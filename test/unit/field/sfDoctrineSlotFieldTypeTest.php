<?php

require_once dirname(__FILE__).'/../../bootstrap/functional.php';
require_once $_SERVER['SYMFONY'].'/vendor/lime/lime.php';

$t = new lime_test(5);

$widget = new sfWidgetFormInputText();
$validator = new sfValidatorString();

$fieldType = new sfDoctrineSlotFieldType($widget, $validator, array('key' => 'val'));

$t->is($fieldType->getWidget(), $widget);
$t->is($fieldType->getValidator(), $validator);
$t->is($fieldType->getOptions(), array('key' => 'val'));

$fieldType->setWidget(new sfWidgetFormInputCheckbox());
$fieldType->setValidator(new sfValidatorBoolean());
$t->is(get_class($fieldType->getWidget()), 'sfWidgetFormInputCheckbox');
$t->is(get_class($fieldType->getValidator()), 'sfValidatorBoolean');