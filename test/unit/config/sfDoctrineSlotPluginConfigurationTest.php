<?php

require_once dirname(__FILE__).'/../../bootstrap/functional.php';
require_once $_SERVER['SYMFONY'].'/vendor/lime/lime.php';

$t = new lime_test(7);

$pluginConfig = $configuration->getPluginConfiguration('sfDoctrineSlotPlugin');

$service = $pluginConfig->getDoctrineSlotFieldService();
$t->is(get_class($service), 'sfDoctrineSlotFieldTestService', 'The field service class was read from app.yml');
$t->is(get_class($service->getDefaultFieldType()->getWidget()), 'sfWidgetFormInputCheckbox', 'The default field was read from app.yml');
$t->is(isset($service['testing']), true, 'The "testing" type was loaded from app.yml');
$t->is($service['testing']->getValidator()->getOption('required'), true, 'The "testing" validator is required, per app.yml');

$t->is(isset($service['textarea']), true, 'The "textarea" type was loaded from app.yml');
$t->is(get_class($service['textarea']->getWidget()), 'sfWidgetFormTextarea', 'The "textarea" type was loaded correctly app.yml');
$t->is($service['textarea']->getValidator()->getOption('required'), false, 'The "textarea" validator is not required - the default behavior.');