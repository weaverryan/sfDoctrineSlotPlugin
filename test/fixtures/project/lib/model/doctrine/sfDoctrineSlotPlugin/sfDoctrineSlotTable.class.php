<?php


class sfDoctrineSlotTable extends PluginsfDoctrineSlotTable
{
    
    public static function getInstance()
    {
        return Doctrine_Core::getTable('sfDoctrineSlot');
    }
}