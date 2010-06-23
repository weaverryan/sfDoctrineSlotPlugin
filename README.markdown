sfDoctrineSlotPlugin
====================

Allows for non-existent columns of data on a model to be persisted and
retrieved as if those columns existed in the schema.

Suppose the following model exists:

    Blog:
      columns:
        title:    string(255)
        body:     clob

A "fake" column `link_url` could be added to any `Blog` record:

    $blog = Doctrine_Core::getTable('Blog')->find(1);

    // register the pseudo field "link_url"
    $blog->createSlot('link_url', 'url');

    // now use the field as if it were a real field
    $blog->link_url = 'http://www.sympalphp.org';
    echo $blog->link_url;

The second argument to `createSlot` is a `type`, which defines its
widget and validator.

The data is actually stored in a separate table, `sfDoctrineSlot`, and
related to the `Blog` model via a many-to-many table (`BlogSlot`) that
is created automatically.

Installation
------------

### With git

    git submodule add git://github.com/weaverryan/sfDoctrineSlotPlugin.git plugins/sfDoctrineSlotPlugin
    git submodule init
    git submodule update

### With subversion

    svn propedit svn:externals plugins

In the editor that's displayed, add the following entry and then save

    sfDoctrineSlotPlugin https://svn.github.com/weaverryan/sfDoctrineSlotPlugin.git

Finally, update:

    svn up

# Setup

In your `config/ProjectConfiguration.class.php` file, make sure you have
the plugin enabled.

    $this->enablePlugins('sfDoctrineSlotPlugin');

Configuration
-------------

### Enabling the slot functionality for your model

To enable the slot functionality, simply add the `sfDoctrineSlotTemplate`
behavior to your model via the `schema.yml` file:

    Blog:
      columns:
        title:    string(255)
        body:     clob
      actAs:
        sfDoctrineSlotTemplate:

A many-to-many model called `BlogSlot`, or more generally `YourModelSlot`
will be generated automatically.