<?php

require_once dirname(__FILE__).'/../../bootstrap/functional.php';
require_once $_SERVER['SYMFONY'].'/vendor/lime/lime.php';

$t = new lime_test(5);
Doctrine_Query::create()->from('Blog')->delete()->execute();
$blog = new Blog();
$blog->title = 'Unit test blog';
$blog->save();

$form = new BlogForm($blog);

$t->is(count($form->getSlotFields()), 0, '->getSlotFields() on a form begins at 0');
$form->addSlotFields();
$t->is(count($form->getSlotFields()), 0, '->getSlotFields() returns 0 after addSlotFields() when there are no slots.');

$blog->getOrCreateSlot('url', array('type' => 'textarea', 'default_value' => 'unit test'));
$form->addSlotFields();
$t->is(count($form->getSlotFields()), 1, '->getSlotFields() returns 1 after addSlotFields() when there is one slot.');

$form = new BlogForm($blog);
$form->addSlotFieldS(array('fake'));
$t->is(count($form->getSlotFields()), 0, '->getSlotFields() returns 0 after addSlotFields() with an array not including the actual slot name.');

$form = new BlogForm($blog);
$form->addSlotFieldS(array('url'));
$t->is(count($form->getSlotFields()), 1, '->getSlotFields() returns 1 after addSlotFields() with an array containing the true slot.');