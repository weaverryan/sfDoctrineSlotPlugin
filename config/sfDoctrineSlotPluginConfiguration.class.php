<?php
/**
 * Plugin configuration file for sfDoctrineSlotPlugin
 * 
 * @package     sfDoctrineSlotPlugin
 * @subpackage  config
 * @author      Ryan Weaver <ryan.weaver@iostudio.com>
 */
class sfDoctrineSlotPluginConfiguration extends sfPluginConfiguration
{
  /**
   * @var sfDoctrineSlotFieldService
   */
  protected $_fieldService;

  public function initialize()
  {
    $this->dispatcher->connect('context.load_factories', array($this, 'bootstrap'));
  }

  /**
   * Hooks up with the form.method_not_found event and initializes the
   * doctrine field slot service.
   *
   * @param sfEvent $event The context.load_factories event
   */
  public function bootstrap(sfEvent $event)
  {
    $this->_fieldService = $this->createDoctrineSlotFieldService();

    $form = new sfDoctrineSlotExtendedForm($this->getDoctrineSlotFieldService());
    $this->dispatcher->connect('form.method_not_found', array($form, 'extend'));
  }

  /**
   * Returns the current doctrine slot service for this configuration
   *
   * @return sfDoctrineSlotFieldService
   */
  public function getDoctrineSlotFieldService()
  {
    return $this->_fieldService;
  }

  /**
   * Creates a new sfDodctrineSlotFieldService from config
   *
   * @throws sfException
   * @return sfDoctrineSlotFieldService
   */
  protected function createDoctrineSlotFieldService()
  {
    $class = sfConfig::get('app_doctrine_slot_field_service_class', 'sfDoctrineSlotFieldService');
    $defaultField = sfConfig::get('app_doctrine_slot_default_type', 'text');

    $service = new $class($defaultField);

    // gather all of the sfDoctrineSlotFieldType objects from config
    $fieldConfigs = sfConfig::get('app_doctrine_slot_types', array());
    $fields = array();
    foreach ($fieldConfigs as $name => $fieldConfig)
    {
      if (!isset($fieldConfig['widget']))
      {
        throw new sfException(sprintf('Missing key "widget" for doctrine slot type "%s"', $name));
      }
      if (!isset($fieldConfig['validator']))
      {
        throw new sfException(sprintf('Missing key "validator" for doctrine slot type "%s"', $name));
      }

      if (is_string($fieldConfig['widget']))
      {
        $class = $fieldConfig['widget'];
        $widget = new $class();
      }
      else
      {
        $class = $fieldConfig['widget']['class'];
        $options = isset($fieldConfig['widget']['options']) ? $fieldConfig['widget']['options'] : array();
        $attributes = isset($fieldConfig['widget']['attributes']) ? $fieldConfig['widget']['attributes'] : array();

        $widget = new $class($options, $attributes);
      }

      if (is_string($fieldConfig['validator']))
      {
        $class = $fieldConfig['validator'];
        $validator = new $class();
      }
      else
      {
        $class = $fieldConfig['validator']['class'];
        $options = isset($fieldConfig['validator']['options']) ? $fieldConfig['validator']['options'] : array();
        $messages = isset($fieldConfig['validator']['messages']) ? $fieldConfig['validator']['messages'] : array();

        $validator = new $class($options, $messages);
      }

      $service[$name] = new sfDoctrineSlotFieldType($widget, $validator);
    }

    return $service;
  }
}