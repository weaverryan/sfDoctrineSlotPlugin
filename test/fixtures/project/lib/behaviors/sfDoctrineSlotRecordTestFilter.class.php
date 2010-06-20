<?php

class sfDoctrineSlotRecordTestFilter extends Doctrine_Record_Filter
{
  public function filterSet(Doctrine_Record $record, $name, $value)
  {
    if ($name == 'filtered_field')
    {
      // do nothing, but pretend field exists
      return;
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
    if ($name == 'filtered_field')
    {
      return 'filtered_result';
    }

    throw new Doctrine_Record_UnknownPropertyException(sprintf('Unknown record property / related component "%s" on "%s"', $name, get_class($record)));
  }
}