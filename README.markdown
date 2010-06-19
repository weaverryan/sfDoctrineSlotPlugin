sfDoctrineSlotPlugin
====================

Allows for fake "columns" to be added to a model and data saved to that column
on a record-by-record basis.

    $blog = Doctrine_Core::getTable('Blog')->find(1);

    // "title" is a real column on the Blog model
    echo $blog->title;

    // "event_date" is not a real column on the Blog model
    echo $blog->event_date;
