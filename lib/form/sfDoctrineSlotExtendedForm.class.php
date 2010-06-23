<?php

/**
 * Listens to the form.method_not_found event to "extend" form classes
 * 
 * @package     sfDoctrineSlotPlugin
 * @subpackage  form
 * @author      Ryan Weaver <ryan.weaver@iostudio.com>
 */
class sfDoctrineSlotExtendedForm
{
  /**
   * @var sfDoctrineSlotFieldService
   */
  protected $_fieldService;

  /**
   * @var sfForm The current form that is the subject of the event
   */
  protected $_subject;

  /**
   * Class constructor
   *
   * @param sfDoctrineSlotFieldService $fieldService
   */
  public function __construct(sfDoctrineSlotFieldService $fieldService)
  {
    $this->_fieldService = $fieldService;
  }

  /**
   * Adds any slot columns on this object to the form with their configured
   * widgets and validator
   *
   * @param array $fields An array of slot field names to add (blank to add all)
   * @return void
   */
  public function addSlotFields(array $fields = array())
  {
    $fields = (array) $fields;

    if (!$this->_subject instanceof sfFormDoctrine)
    {
      throw new sfException('->addSlotFields() can only be called on doctrine form.');
    }

    if (!$this->_subject->getObject()->getTable()->hasTemplate('sfDoctrineSlotTemplate'))
    {
      throw new sfException('->addSlotFields() can only be called on a form whose model acts as sfDoctrineSlotTemplate.');
    }

    // setup the widget and validator for the slots
    $slotFields = array();
    $widgetSchema = $this->_subject->getWidgetSchema();
    $validatorSchema = $this->_subject->getValidatorSchema();
    foreach ($this->_subject->getObject()->getSlotsByName() as $name => $slot)
    {
      if (empty($fields) || in_array($name, $fields))
      {
        $slotFields[] = $name;
        $fieldType = $this->_fieldService->getFieldType($slot->type);

        if (!$fieldType)
        {
          $fieldType = $this->_fieldService->getDefaultFieldType();
        }

        if (!$fieldType)
        {
          throw new sfException(sprintf('No field type defined for "%s" and no default field to fall back to.', $slot->type));
        }

        $widgetSchema[$name] = $fieldType->getWidget();
        $validatorSchema[$name] = $fieldType->getValidator();
        $this->_subject->setDefault($name, $this->_subject->getObject()->get($name));
      }
    }

    $this->_subject->setOption('slot_fields', $slotFields);
  }

  /**
   * Returns an array of the field names that are slot fields
   *
   * @return array
   */
  public function getSlotFields()
  {
    return $this->_subject->getOption('slot_fields', array());
  }

  
  /**
   * Listener method for method_not_found events
   *
   * This allows any public other methods in this class to be called as
   * if they were in the actions class.
   */
  public function extend(sfEvent $event)
  {
    $this->_subject = $event->getSubject();
    $method = $event['method'];
    $arguments = $event['arguments'];

    if (method_exists($this, $method))
    {
      $result = call_user_func_array(array($this, $method), $arguments);

      $event->setReturnValue($result);

      return true;
    }
    else
    {
      return false;
    }
  }
}