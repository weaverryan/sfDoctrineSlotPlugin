<?php

require_once dirname(__FILE__).'/../../bootstrap/functional.php';
require_once $_SERVER['SYMFONY'].'/vendor/lime/lime.php';

$t = new lime_test(6);
Doctrine_Query::create()->from('Blog')->delete()->execute();
$blog = new Blog();
$blog->title = 'Unit test blog';
$blog->save();

$form = new BlogForm($blog);

$t->is(count($form->getSlotFields()), 0, '->getSlotFields() on a form begins at 0');
$form->addSlotFields();
$t->is(count($form->getSlotFields()), 0, '->getSlotFields() returns 0 after addSlotFields() when there are no slots.');

$blog->createSlot('url', 'textarea', 'unit test');
$form->addSlotFields();
$t->is(count($form->getSlotFields()), 1, '->getSlotFields() returns 1 after addSlotFields() when there is one slot.');
$t->is($form['url']->getValue(), 'unit test', 'The value of the slot was set as the default value.');

$form = new BlogForm($blog);
$form->addSlotFieldS(array('fake'));
$t->is(count($form->getSlotFields()), 0, '->getSlotFields() returns 0 after addSlotFields() with an array not including the actual slot name.');

$form = new BlogForm($blog);
$form->addSlotFields(array('url'));
$t->is(count($form->getSlotFields()), 1, '->getSlotFields() returns 1 after addSlotFields() with an array containing the true slot.');