<?php

require_once dirname(__FILE__).'/../../bootstrap/functional.php';
require_once $_SERVER['SYMFONY'].'/vendor/lime/lime.php';

$t = new lime_test(0);

$widget = new sfWidgetFormInputText();
$validator = new sfValidatorString();
$textType = new sfDoctrineSlotFieldType($widget, $validator);

$service = new sfDoctrineSlotFieldService('text');

$t->info('1 - Test basic getters and setters.');
  $t->is($service->getDefaultFieldType(), null, '->getDefaultFieldType() returns default if type does not exist.');
  $service['text'] = $textType;
  $t->is($service->getDefaultFieldType(), $textType, '->getDefaultFieldType() returns the defined default type.');

  $t->is($service->getFieldType('fake'), null, '->getFieldType(fake) returns null.');
  $t->is($service->getFieldType('text'), $textType, '->getFieldType(text) returns the correct sfDoctrineSlotFieldType object.');

  $widget = new sfWidgetFormInputCheckbox();
  $validator = new sfValidatorBoolean();
  $booleanType = new sfDoctrineSlotFieldType($widget, $validator);
  $service->addFieldType('boolean', $booleanType);
  $t->is($service->getfieldType('boolean'), $booleanType, '->addFieldType() correctly adds a feild.');

$t->info('2 - Test ArrayAccess');
  $t->is($service['boolean'], $booleanType, 'offsetGet works.');

  $service['new'] = $textType;
  $t->is($service['new'], $textType, 'offsetSet works.');

  unset($service['new']);
  $t->is($service['new'], null, 'offsetUnset works.');

  $t->is(isset($service['new']), false, 'offsetExists works.');
  $t->is(isset($service['text']), true, 'offsetExists works.');