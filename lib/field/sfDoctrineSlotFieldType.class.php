<?php
/**
 * Represents a field/slot type - helps to define what a slot is
 * 
 * @package     sfDoctrineSlotPlugin
 * @subpackage  field
 * @author      Ryan Weaver <ryan.weaver@iostudio.com>
 */
class sfDoctrineSlotFieldType
{

  /**
   * @var sfWidgetForm
   * @var sfValidatorBase
   * @var array
   */
  protected
    $_widget,
    $_validator,
    $_options;

  /**
   * @param sfWidgetForm    $widget     The widget to use for this field
   * @param sfValidatorBase $validator  The validator to use for this field type
   * @param array           $options    An options array
   */
  public function __construct(sfWidgetForm $widget, sfValidatorBase $validator, $options = array())
  {
    $this->_widget = $widget;
    $this->_validator = $validator;
    $this->_options = $options;
  }

  /**
   * @return sfWidgetForm
   */
  public function getWidget()
  {
    return $this->_widget;
  }

  /**
   * @param  sfWidgetForm $widget
   */
  public function setWidget($widget)
  {
    $this->_widget = $widget;
  }

  /**
   * @return sfValidatorBase
   */
  public function getValidator()
  {
    return $this->_validator;
  }

  /**
   * @param  sfValidatorBase $validator
   */
  public function setValidator(sfValidatorBase $validator)
  {
    $this->_validator = $validator;
  }

  /**
   * @return array
   */
  public function getOptions()
  {
    return $this->_options;
  }
}