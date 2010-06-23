<?php
/**
 * Utility service class for the plugin.
 *
 * This handles the configuration of fields - widgets, validators, etc
 * 
 * @package     sfDoctrineSlotToolkit
 * @subpackage  util
 * @author      Ryan Weaver <ryan.weaver@iostudio.com>
 */
class sfDoctrineSlotFieldService implements ArrayAccess
{

  /**
   * @var array
   * @var string
   */
  protected
    $_fieldTypes = array(),
    $_defaultField;

  /**
   * Class constructor
   *
   * @param  string $defaultField The string field type that should be default
   */
  public function __construct($defaultField = null)
  {
    $this->_defaultField = $defaultField;
  }

  /**
   * @param  string $name The name of the field type to return
   * @return sfDoctrineSlotFieldType
   */
  public function getFieldType($name, $returnDefault = false)
  {
    return isset($this->_fieldTypes[$name]) ? $this->_fieldTypes[$name] : null;
  }

  /**
   * Returns the default slot field type if one was set
   *
   * @return sfDoctrineSlotFieldType
   */
  public function getDefaultFieldType()
  {
    if ($this->_defaultField)
    {
      return isset($this->_fieldTypes[$this->_defaultField]) ? $this->getFieldType($this->_defaultField) : null;
    }

    return null;
  }

  /**
   * Add a valid field type
   *
   * @param  string $name The name of the field type
   * @param sfDoctrineSlotFieldType $type The field type object
   * @return void
   */
  public function addFieldType($name, sfDoctrineSlotFieldType $type)
  {
    $this->_fieldTypes[$name] = $type;
  }

  /**
   * @see ArrayAccess
   *
   * @param string $key The name of the field type
   * @param sfDoctrineSlotFieldType $val The field type object
   */
  public function offsetSet($key, $val)
  {
    $this->addFieldType($key, $val);
  }

  /**
   * @see ArrayAccess
   */
  public function offsetGet($key)
  {
    return $this->getFieldType($key);
  }

  /**
   * @see ArrayAccess
   */
  public function offsetUnset($key)
  {
    unset($this->_fieldTypes[$key]);
  }

  /**
   * @see ArrayAccess
   */
  public function offsetExists($key)
  {
    return isset($this->_fieldTypes[$key]);
  }
}