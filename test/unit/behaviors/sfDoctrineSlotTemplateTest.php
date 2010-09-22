<?php

require_once dirname(__FILE__).'/../../bootstrap/functional.php';
require_once $_SERVER['SYMFONY'].'/vendor/lime/lime.php';

$t = new lime_test(45);
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

  // remove a slot but don't save
  $blog2->removeSlot('url');
  $t->is($blog2->hasSlot('url'), false, '->hasSlot(url) on blog 2 returns false after removing the slot');
  $blog2->refreshRelated('Slots');
  $blog2->getSlotsByName(true); // refresh the slots
  $t->is($blog2->hasSlot('url'), true, '->hasSlot(url) on blog 2 returns true after removing the slot but not saving');
  $slotRefs = Doctrine_Query::create()->from('BlogSlot')->execute();
  $t->is(count($slotRefs), 2, 'There should still be 2 BlogSlot objects');

  $blog2->removeSlot('url');
  $blog2->save();
  $blog2->refreshRelated('Slots');
  $blog2->getSlotsByName(true); // refresh the slots
  $t->is($blog2->hasSlot('url'), false, '->hasSlot(url) on blog 2 returns true after removing the slot and saving');
  $slotRefs = Doctrine_Query::create()->from('BlogSlot')->execute();
  $t->is(count($slotRefs), 1, 'There should once again be only one entry in BlogSlot');

  $blog2->removeSlot('fake');
  $slotRefs = Doctrine_Query::create()->from('BlogSlot')->execute();
  $t->is(count($slotRefs), 1, 'Removing a non-existent slot does nothing.');

  // add a slot, but don't persist
  $blog2->addSlot($slot);
  $blog2->refreshRelated('Slots');
  $blog2->getSlotsByName(true); // refresh the slots
  $t->is($blog2->hasSlot('url'), false, '->hasSlot(url) on blog 2 returns false. addSlot() was called, but not saved');
  $slotRefs = Doctrine_Query::create()->from('BlogSlot')->execute();
  $t->is(count($slotRefs), 1, 'There should still be one entry in BlogSlot');

  // add a slot, and persist
  $blog2->addSlot($slot);
  $blog2->save();
  $blog2->refreshRelated('Slots');
  $blog2->getSlotsByName(true); // refresh the slots
  $t->is($blog2->hasSlot('url'), true, '->hasSlot(url) on blog 2 returns true after using ->addSlot() and saving');
  $slotRefs = Doctrine_Query::create()->from('BlogSlot')->execute();
  $t->is(count($slotRefs), 2, 'There should once again be two entries in BlogSlot');

$t->info('5 - Test createSlot()');
  $t->info('  5.1 - Test on an existing slot');
    $t->is($blog->createSlot('url')->id, $slot->id, '->createSlot(url) returns the existing slot.');
    count_slots($t, 1);

  $t->info('  5.2 - Test on a new slot');
    $newSlot = $blog->createSlot('new_slot', 'MyType', 'default_val');

    $t->is($newSlot->name, 'new_slot', 'The new slot\'s name was set correctly');
    $t->is($newSlot->type, 'MyType', 'The new slot\s type was set correctly');
    $t->is($newSlot->value, 'default_val', 'The new slot\s value was set correctly');
    count_slots($t, 2);

    $slotRefs = Doctrine_Query::create()->from('BlogSlot')->execute();
    $t->is(count($slotRefs), 2, 'The new BlogSlot reference is not added because $blog has not been saved');

    $blog->save();
    $slotRefs = Doctrine_Query::create()->from('BlogSlot')->execute();
    $t->is(count($slotRefs), 3, 'There are now 3 entries on BlogSlot because the $blog has been saved and the ref persisted');

$t->info('6 - Test the record filter.');
  $t->info('  6.1 - Test the getter and setter with a real slot.');
    $t->is($blog->url, 'http://www.sympalphp.org', '->url correctly returns the slot value for this existing slot.');

    $blog->url = 'http://www.symfony-project.org';
    $slots = $blog->getSlotsByName(true);
    $t->is($slots['url']->getValue(), 'http://www.symfony-project.org', '->url as a setter correctly sets the existing slot.');

    $blog->save();
    $slots['url']->refresh();
    $blog->refresh(true);
    $t->is($slots['url']->getValue(), 'http://www.symfony-project.org', 'The slot value actually persisted to the db.');

    $blogId = $blog->id;
    Doctrine_Core::getTable('Blog')->getConnection()->clear();
    $blog = Doctrine_Core::getTable('Blog')->find($blogId);

    $blog->fromArray(array('url' => 'http://www.doctrine-project.org'));
    $blog->save();

    $slots['url']->refresh();
    $t->is($slots['url']->getValue(), 'http://www.doctrine-project.org', 'The slot value sets correctly again after retrieving it fresh.');

  $t->info('  6.2 - Test the getter and setter with a field that is not a slot - exceptions are thrown.');
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

$t->info('7 - Test the addSlotQueryTableProxy() method on the template');
  $tbl = Doctrine_Core::getTable('Blog');
  $q = $tbl->createQuery('b')->where('id = ?', $blog->id);
  $q = $tbl->addSlotQuery($q, 's');
  $q->andWhere('b.id = ?', $slot->id);
  $dql = 'SELECT b.id AS b__id, b.title AS b__title, s.id AS s__id, s.name AS s__name, s.type AS s__type, s.value AS s__value, s.created_at AS s__created_at, s.updated_at AS s__updated_at FROM blog b LEFT JOIN blog_slot b2 ON (b.id = b2.blog_id) LEFT JOIN sf_doctrine_slot s ON s.id = b2.id WHERE (b.id = ? AND b.id = ?)';
  $t->is($q->getSqlQuery(), $dql, 'The addSlotQuery table method joins correctly.');

$t->info('8 - Test the toArray() - _slotsByName should map correctly');
  $slot1 = Doctrine_Core::getTable('sfDoctrineSlot')->findOneByName('url');
  $slot2 = Doctrine_Core::getTable('sfDoctrineSlot')->findOneByName('new_slot');

  $expected = array(
    'id'    => $blog->id,
    'title' => 'Unit test',
    '_slotsByName' => array(
      'url'       => $slot1->toArray(),
      'new_slot'  => $slot2->toArray(),
    ),
  );

  $blogArray = $blog->toArray();
  unset($blogArray['Translation'], $blogArray['Slots']);

  $t->is($blogArray, $expected, 'The slotted model can toArray() without bad Doctrine_Record references on _slotsByName');

$t->info('7 - Test an example where slots are naturally added to the record');
  Doctrine_Query::create()->from('Blog')->delete()->execute();
  Doctrine_Query::create()->from('sfDoctrineSlot')->delete()->execute();

  $blog = new Blog();
  $blog->title = 'whatever';
  $blog->save();

  $blog->createSlot('a_slot');
  $blog->a_slot = 'something';

  $t->is($blog->a_slot, 'something', '$blog->a_slot returns the correct value before being persisted');

  // save the blog and supposedly its relations
  $blog->save();
  // clear the Blog identity map
  Doctrine_Core::getTable('Blog')->clear();

  $fromDb = Doctrine::getTable('Blog')->find($blog->id); // load it afresh
  $t->is($fromDb->a_slot, 'something', 'After persisting and refreshing, ->a_slot is still correct');

  // clear the Blog identity map
  Doctrine_Core::getTable('Blog')->clear();
  $q = Doctrine_Query::create()
  ->from('Blog b')
  ->leftJoin('b.Slots s');
  $fromDb2 = $q->fetchOne();

  $t->is($fromDb2->a_slot, 'something', 'After persisting and refreshing with a join, ->a_slot is still correct');

// tests getSlotsByName() to a given array of slot names and values
function test_slots_by_name(lime_test $t, Blog $blog, $slots)
{
  $slotsByName = $blog->getSlotsByName(true);
  $t->is(count($slots), count($slotsByName), sprintf('->getSlotsByName() returns %s results', count($slots)));
}

function count_slots(lime_test $t, $numSlots)
{
  $t->is(Doctrine_Query::create()->from('sfDoctrineSlot')->count(), $numSlots, sprintf('The total number of slots equals "%s"', $numSlots));
}
