<?php
/**
 * Template file to create a many-to-many table to the slot model as well
 * as add slot related functions
 * 
 * @package     sfDoctrineSlotPlugin
 * @subpackage  behaviors
 * @author  Jonathan H. Wage <jonwage@gmail.com>
 * @author      Ryan Weaver <ryan.weaver@iostudio.com>
 */

class sfDoctrineSlotTemplate extends Doctrine_Template
{
  protected $_options = array(
    'generateFiles'     => false,
  );

  /**
   * Class constructor - constructs the sfDoctrineSlotRelation class.
   *
   * @param array $options
   * @return void
   */
  public function __construct(array $options = array())
  {
    parent::__construct($options);

    $this->_plugin = new sfDoctrineSlotRelation($this->_options);
  }

  /**
   * Adds the slot record filter to the filters so that existing slots can
   * be set and retrieved via the normal column interface.
   *
   * @return void
   */
  public function setTableDefinition()
  {
    $this->getTable()->unshiftFilter(new sfDoctrineSlotRecordFilter());
  }

  /**
   * Setup the slot relation table
   *
   * @return void
   */
  public function setUp()
  {
    $this->_plugin->initialize($this->_table);

    // setup the m2m relationship from sfDoctrineSlot to this model
    Doctrine_Core::getTable('sfDoctrineSlot')->hasMany($this->getTable()->getComponentName(), array(
      'local'     => 'id',
      'foreign'   => $this->_plugin->getLocalColumnName(),
      'refClass'  => $this->getRefClass(),
      'onDelete'  => 'cascade',
    ));

    // setup the m2m relationshp from this model to sfDoctrineSlot
    $this->getTable()->hasMany('sfDoctrineSlot as Slots', array(
      'local'     => $this->_plugin->getLocalColumnName(),
      'foreign'   => 'id',
      'refClass'  => $this->getRefClass(),
      'onDelete'  => 'cascade',
    ));
  }

  /**
   * Retrieves an array of the slots where the key is the slot name.
   *
   * This caches the result as a property
   *
   * @param boolean $force To force a refresh of the slots or not
   * @return array
   */
  public function getSlotsByName($force = false)
  {
    $invoker = $this->getInvoker();
    if (!isset($invoker->_slotsByName) || $invoker->_slotsByName === null || $force)
    {
      $slotsByName = array();
      foreach ($this->getInvoker()->Slots as $slot)
      {
        $slotsByName[$slot->name] = $slot;
        
      }
      $invoker->mapValue('_slotsByName', $slotsByName);
    }

    return $invoker->_slotsByName;
  }

  /**
   * Returns whether or not the given slot exists for this record
   *
   * @param  string $name The name of the slot
   * @return bool
   */
  public function hasSlot($name)
  {
    $slotsByName = $this->getSlotsByName();

    return isset($slotsByName[$name]) ? true : false;
  }

  /**
   * Returns whether or not this record has any slots
   *
   * @return bool
   */
  public function hasSlots()
  {
    return count($this->getSlotsByName()) > 0 ? true : false;
  }

  /**
   * Returns the given slot or null of the slot does not exist.
   *
   * @param  string $name The name of the slot to retrieve
   * @return sfDoctrineSlot
   */
  public function getSlot($name)
  {
    if ($this->hasSlot($name))
    {
      $slotsByName = $this->getSlotsByName();

      return $slotsByName[$name];
    }

    return null;
  }

  /**
   * Removes the given slot from this record
   *
   * @param string|sfDoctrineSlot $slot The slot to remove - the object or just the name
   * @return Doctrine_Collection
   */
  public function removeSlot($slot)
  {
    if (!($slot instanceof sfDoctrineSlot))
    {
      $slot = $this->getSlot($slot);
    }

    if (!$slot)
    {
      return;
    }

    return Doctrine_Core::getTable($this->getRefClass())
      ->createQuery()
      ->delete()
      ->where('id = ?', $slot->id)
      ->andWhere($this->_plugin->getLocalColumnName().' = ?', $this->getInvoker()->id)
      ->execute();
  }

  /**
   * Adds the given slot to this record
   *
   * @param sfDoctrineSlot $slot
   * @return Doctrine_Record
   */
  public function addSlot(sfDoctrineSlot $slot)
  {
    $this->removeSlot($slot);

    $class = $this->getRefClass();
    $ref = new $class();
    $ref->set('id', $slot->id);
    $ref->set($this->_plugin->getLocalColumnName(), $this->getInvoker()->id);
    $ref->save();

    // make sure the slots are initialized
    $slotsByName = $this->getSlotsByName();
    $slotsByName[$slot->name] = $slot;
    $this->getInvoker()->mapValue('_slotsByName', $slotsByName);

    return $ref;
  }

  /**
   * Retrieves or creates a link to a sfDoctrineSlot object
   *
   * Options include:
   *   * type
   *   * default_value
   *
   * @param   string  $name     The name of the slot to get or create
   * @param   array   $options  The options array (see above)
   * @return  sfDoctrineSlot
   */
  public function getOrCreateSlot($name, $options = array())
  {
    if (!$hasSlot = $this->hasSlot($name))
    {
      if ($this->hasField($name))
      {
        throw new sfException(sprintf('Slot cannot be created for field "%s" - a field of that name already exists.', $name));
      }

      // @todo don't hardcode the default type
      $type = isset($options['type']) ? $options['type'] : 'Text';

      $slot = new sfDoctrineSlot();
      $slot->name = $name;
      $slot->type = $type;
      
      if (isset($options['default_value']))
      {
        $slot->value = $options['default_value'];
      }
      $slot->save();

      $this->addSlot($slot);
    }
    else
    {
      $slot = $this->getSlot($name);
    }

    return $slot;
  }

  /**
   * Returns whether or not this "field" already exists on this model.
   *
   * This allows for virtual fields (other record filters) and i18n.
   *
   * @param  string $name The name of the field to check
   * @return bool
   */
  public function hasField($name)
  {
    $result = $this->getTable()->hasField($name);
    if (!$result)
    {
      $className = $this->getTable()->getComponentName();

      if (Doctrine_Core::isValidModelClass($className.'Translation'))
      {
        $result = Doctrine_Core::getTable($className.'Translation')->hasField($name);
      }
    }

    if (!$result)
    {
      try
      {
        $this->getInvoker()->get($name);
        $result = true;
      }
      catch (Doctrine_Record_UnknownPropertyException $e)
      {
      }

      // we're not counting slots as "fields" in this method
      if ($this->hasSlot($name))
      {
        $result = false;
      }
    }

    return $result;
  }

  /**
   * Returns the name of the m2m table between this model and sfDoctrineSlot
   *
   * @return string
   */
  public function getRefClass()
  {
    return $this->_plugin->getTable()->getComponentName();
  }
}