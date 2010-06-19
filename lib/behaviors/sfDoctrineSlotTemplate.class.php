<?php
/**
 * Template file to create a many-to-many table to the slot model as well
 * as add slot related functions
 * 
 * @package     
 * @subpackage  
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
  }
}