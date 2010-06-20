<?php
/**
 * Template file to create a many-to-many table to the slot model as well
 * as add slot related functions
 * 
 * @package     sfDoctrineSlotPlugin
 * @subpackage  behaviors
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
   * Setup the slot relation table
   *
   * @return void
   */
  public function setUp()
  {
    $this->_plugin->initialize($this->_table);

    $refClass = $this->_plugin->getTable()->getComponentName();

    // setup the m2m relationship from sfDoctrineSlot to this model
    Doctrine_Core::getTable('sfDoctrineSlot')->hasMany($this->getTable()->getComponentName(), array(
      'local'     => 'id',
      'foreign'   => $this->_plugin->getLocalColumnName(),
      'refClass'  => $refClass,
      'onDelete'  => 'cascade',
    ));

    // setup the m2m relationshp from this model to sfDoctrineSlot
    $this->getTable()->hasMany('sfDoctrineSlot as Slots', array(
      'local'     => $this->_plugin->getLocalColumnName(),
      'foreign'   => 'id',
      'refClass'  => $refClass,
      'onDelete'  => 'cascade',
    ));
  }
}