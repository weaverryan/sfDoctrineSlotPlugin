<?php

require_once dirname(__FILE__).'/../../bootstrap/functional.php';
require_once $_SERVER['SYMFONY'].'/vendor/lime/lime.php';

$t = new lime_test(3);

$t->info('1 - Test to see that the m2m relationship is setup correctly by sfDoctrineSlotTemplate');
  $blog = new Blog();
  $blog->title = 'Unit test';
  $blog->body= 'Lorem ipsum';
  $blog->save();

  $t->is(count($blog->Slots), 0, 'The ->Slots relationship correctly returns 0 results initially.');

  $slot = new sfDoctrineSlot();
  $slot->name = 'url';
  $slot->value = 'http://www.sympalphp.org';
  $slot->save();

  $blog->link('Slots', array($slot->id));
  $blog->save();

  $blog->refreshRelated('Slots');
  $t->is(count($blog->Slots), 1, 'The ->Slots relationship now returns 1 result.');
  $t->is(count($slot->Blog), 1, '$slot->Blog returns one item as well.');