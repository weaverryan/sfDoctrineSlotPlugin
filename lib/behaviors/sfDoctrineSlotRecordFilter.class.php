<?php
/**
 * Allows for related sfDoctrineSlot records to be set and get via the
 * normal getters and setters.
 *
 * Image that 'teaser' is not a real column, but has been established
 * as a related sfDoctrineSlot record:
 *
 *     [php]
 *     echo $content->getTeaser();
 *
 * The above is the same as doing:
 *
 *     [php]
 *     echo $content->getSlot('teaser')
 *
 * For setters, this will not create a new relationship if the slot doesn't
 * exist - it will correctly throw an exception.
 *
 * @package sfDoctrineSlotPlugin
 * @author  Jonathan H. Wage <jonwage@gmail.com>
 * @author  Ryan Weaver <ryan.weaver@iostudio.com>
 */
class sfDoctrineSlotRecordFilter extends Doctrine_Record_Filter
{
  /**
   * Filter Doctrine_Record::set() calls and see if we can call the property on
   * an existing sfDoctrineSlot record.
   *
   * @param Doctrine_Record $record The Doctrine_Record instance this is being called on
   * @param string          $name   The name of the property
   * @param string          $value  The value of the property
   * @return                Doctrine_Record
   * @throws Doctrine_Record_UnknownPropertyException If property could not be found
   */
  public function filterSet(Doctrine_Record $record, $name, $value)
  {
    try
    {
      if ($record->hasSlot($name))
      {
        $slot = $record->getSlot($name);
        $slot->set('value', $value);

        return $record;
      }
    }
    catch (Exception $e)
    {
    }

    throw new Doctrine_Record_UnknownPropertyException(sprintf('Unknown record property / related component "%s" on "%s"', $name, get_class($record)));
  }

  /**
   * Filter Doctrine_Record::get() tries to get the property via an
   * sfDoctrineSlot relation.
   *
   * @param Doctrine_Record $record The record instance
   * @param string          $name The name of the property
   * @return mixed          $value The value of the property
   * @throws Doctrine_Record_UnknownPropertyException If property could not be found
   */
  public function filterGet(Doctrine_Record $record, $name)
  {
    try
    {
      if ($record->hasSlot($name))
      {
        return $record->getSlot($name)->get('value');
      }
    }
    catch (Exception $e)
    {
    }

    throw new Doctrine_Record_UnknownPropertyException(sprintf('Unknown record property / related component "%s" on "%s"', $name, get_class($record)));
  }
}