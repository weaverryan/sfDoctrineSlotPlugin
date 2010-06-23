<?php

require_once dirname(__FILE__).'/../../bootstrap/functional.php';
require_once $_SERVER['SYMFONY'].'/vendor/lime/lime.php';

$t = new lime_test(32);
$tbl = Doctrine_Core::getTable('Blog');

$blog = new Blog();
$blog->title = 'Unit test';
$blog->body= 'Lorem ipsum';
$blog->save();

$slot = new sfDoctrineSlot();
$slot->name = 'url';
$slot->value = 'http://www.sympalphp.org';
$slot->save();

$blog2 = new Blog();
$blog2->title = 'Other blog';

$t->info('1 - Test to see that the m2m relationship is setup correctly by sfDoctrineSlotTemplate');

  $t->is(count($blog->Slots), 0, 'The ->Slots relationship correctly returns 0 results initially.');

  $blog->link('Slots', array($slot->id));
  $blog->save();
  $blog->refreshRelated('Slots');

  $t->is(count($blog->Slots), 1, 'The ->Slots relationship now returns 1 result.');
  $t->is(count($slot->Blog), 1, '$slot->Blog returns one item as well.');

$t->info('2 - Test getSlotsByName()');
  test_slots_by_name($t, $blog, array(
    'url' => 'http://www.sympalphp.org',
  ));

$t->info('3 - Test hasField()');
  $t->is($blog->hasField('title'), true, '->hasField(title) returns true');
  $t->is($blog->hasField('fake'), false, '->hasField(fake) returns true');
  $t->is($blog->hasField('filtered_field'), true, '->hasField(filtered_field) returns true, it is handled by a record filter.');
  $t->is($blog->hasField('url'), false, '->hasField(url) return false - its a slot field, but we\'re choosing to not include slot fields in our hasField method.');
  $t->is($blog->hasField('body'), true, '->hasField(body) return true - its an i18n field.');

$t->info('4 - Test hasSlot(), hasSlots(), getSlot(), removeSlot(), addSlot()');
  $t->is($blog->hasSlot('url'), true, '->hasSlot(url) return true');
  $t->is($blog->hasSlot('fake'), false, '->hasSlot(fake) return false');

  $t->is($blog->hasSlots(), true, '->hasSlots() returns true');
  $t->is($blog2->hasSlots(), false, '->hasSlots() returns false for the other blog');

  $t->is($blog->getSlot('url')->id, $slot->id, '->getSlot(url) returns the correct sfDoctrineSlot object');
  $t->is($blog->getSlot('fake'), null, '->getSlot(fake) returns null');

  $blog2->link('Slots', array($slot->id));
  $blog2->save();
  $blog2->refreshRelated('Slots');
  $blog2->getSlotsByName(true); // refresh the slots
  $t->is($blog2->hasSlot('url'), true, '->hasSlot(url) on blog 2 returns true now, we just added the slot');

  $blog2->removeSlot('url');
  $blog2->refreshRelated('Slots');
  $blog2->getSlotsByName(true); // refresh the slots
  $t->is($blog2->hasSlot('url'), false, '->hasSlot(url) on blog 2 returns false after removing the slot');
  $slotRefs = Doctrine_Query::create()->from('BlogSlot')->execute();
  $t->is(count($slotRefs), 1, 'There should once again be only one entry in BlogSlot');

  $blog2->removeSlot('fake');
  $slotRefs = Doctrine_Query::create()->from('BlogSlot')->execute();
  $t->is(count($slotRefs), 1, 'Removing a non-existent slot does nothing.');

  $blog2->addSlot($slot);
  $blog2->refreshRelated('Slots');
  $blog2->getSlotsByName(true); // refresh the slots
  $t->is($blog2->hasSlot('url'), true, '->hasSlot(url) on blog 2 returns true after using ->addSlot()');
  $slotRefs = Doctrine_Query::create()->from('BlogSlot')->execute();
  $t->is(count($slotRefs), 2, 'There should once again be two entries in BlogSlot');

$t->info('4 - Test createSlot()');
  $t->info('  4.1 - Test on an existing slot');
    $t->is($blog->createSlot('url')->id, $slot->id, '->createSlot(url) returns the existing slot.');
    count_slots($t, 1);

  $t->info('  4.2 - Test on a new slot');
    $newSlot = $blog->createSlot('new_slot', 'MyType', 'default_val');

    $t->is($newSlot->name, 'new_slot', 'The new slot\'s name was set correctly');
    $t->is($newSlot->type, 'MyType', 'The new slot\s type was set correctly');
    $t->is($newSlot->value, 'default_val', 'The new slot\s value was set correctly');
    count_slots($t, 2);
    $slotRefs = Doctrine_Query::create()->from('BlogSlot')->execute();
    $t->is(count($slotRefs), 3, 'There are now 3 entries on BlogSlot');

$t->info('5 - Test the record filter.');
  $t->info('  5.1 - Test the getter and setter with a real slot.');
    $t->is($blog->url, 'http://www.sympalphp.org', '->url correctly returns the slot value for this existing slot.');

    $blog->url = 'http://www.symfony-project.org';
    $slots = $blog->getSlotsByName(true);
    $t->is($slots['url']->getValue(), 'http://www.symfony-project.org', '->url as a setter correctly sets the existing slot.');

    $blog->save();
    $slots['url']->refresh();
    $blog->refresh(true);
    $t->is($slots['url']->getValue(), 'http://www.symfony-project.org', 'The slot value actually persisted to the db.');

  $t->info('  5.2 - Test the getter and setter with a field that is not a slot - exceptions are thrown.');
    try
    {
      $blog->fake;
      $t->fail('Exception now thrown on getter');
    }
    catch (Doctrine_Record_UnknownPropertyException $e)
    {
      $t->pass('Exception thrown on getter');
    }

    try
    {
      $blog->fake = 'cool';
      $t->fail('Exception now thrown on setter');
    }
    catch (Doctrine_Record_UnknownPropertyException $e)
    {
      $t->pass('Exception thrown on setter');
    }

$t->info('6 - Test the addSlotQueryTableProxy() method on the template');
  $tbl = Doctrine_Core::getTable('Blog');
  $q = $tbl->createQuery('b')->where('id = ?', $blog->id);
  $q = $tbl->addSlotQuery($q, 's');
  $q->andWhere('b.id = ?', $slot->id);
  $dql = 'SELECT b.id AS b__id, b.title AS b__title, s.id AS s__id, s.name AS s__name, s.type AS s__type, s.value AS s__value, s.created_at AS s__created_at, s.updated_at AS s__updated_at FROM blog b LEFT JOIN blog_slot b2 ON (b.id = b2.blog_id) LEFT JOIN sf_doctrine_slot s ON s.id = b2.id WHERE (b.id = ? AND b.id = ?)';
  $t->is($q->getSqlQuery(), $dql, 'The addSlotQuery table method joins correctly.');


// tests getSlotsByName() to a given array of slot names and values
function test_slots_by_name(lime_test $t, Blog $blog, $slots)
{
  $slotsByName = $blog->getSlotsByName(true);
  $t->is(count($slots), count($slotsByName), sprintf('->getSlotsByName() returns %s results', count($slots)));
}

function count_slots(lime_test $t, $numSlots)
{
  return (Doctrine_Query::create()->from('sfDoctrineSlot')->count() == $numSlots);
}
