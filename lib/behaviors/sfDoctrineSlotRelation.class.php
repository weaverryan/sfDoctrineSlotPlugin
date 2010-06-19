<?php

/**
 * Represents the many-to-many table between sfDoctrineSlot and the class
 * that implements the sfDoctrineSlotTemplate template.
 * 
 * @package     
 * @subpackage  
 * @author      Ryan Weaver <ryan.weaver@iostudio.com>
 */
class sfDoctrineSlotRelation extends Doctrine_Record_Generator
{
  protected $_options = array(
    'className'       => '%CLASS%Slot',

    // standard options
    'generateFiles'  => false,
    'generatePath'   => false,
    'builderOptions' => array(),
    'identifier'     => false,
    'table'          => false,
    'pluginTable'    => false,
    'children'       => array(),
    'cascadeDelete'  => true,
    'appLevelDelete' => false
  );

  /**
   * Class constructor
   */
  public function __construct(array $options = array())
  {
    $this->_options = Doctrine_Lib::arrayDeepMerge($this->_options, $options);
  }

  public function buildRelation()
  {
    $this->buildForeignRelation('Slots');
    $this->buildLocalRelation();
  }

  /**
   * Builds a many-to-many table between the model implementing this behavior
   * and the sfDoctrineSlot model.
   *
   * @return void
   */
  public function setTableDefinition()
  {
    if (is_array($this->_options['table']->getIdentifier()))
    {
      throw new Doctrine_Record_Exception('The sfDoctrineSlotTemplate does not support models with multiple primary keys.');
    }

    $idDefinition = $this->_options['table']->getColumnDefinition($this->_options['table']->getIdentifier());
    $length = isset($idDefinition['length']) ? $idDefinition['length'] : null; 

    $this->hasColumn(
      $this->_options['table']->getTableName().'_id',
      'integer',
      $length,
      array(
        'type'      => 'integer',
        'primary'   => true,
      )
    );
  }
}