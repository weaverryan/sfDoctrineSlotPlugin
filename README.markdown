sfDoctrineSlotPlugin
====================

Allows for non-existent columns of data on a model to be persisted and
retrieved as if those columns existed in the schema.

>These nonexistent columns of data are called `slots`. So, a `slot` is a
>field on your model that doesn't really exist in the schema.

Suppose the following model exists:

    Blog:
      columns:
        title:    string(255)
        body:     clob
      actAs:
        sfDoctrineSlotTemplate

A "fake" column `link_url` could be added to any `Blog` record:

    $blog = new Blog();

    // register the pseudo field "link_url"s a "url" type
    $blog->createSlot('link_url', 'url');

    // now use the field as if it were a real field
    $blog->link_url = 'http://www.sympalphp.org';
    echo $blog->link_url;

The second argument to `createSlot()` is a `type`, which defines its
widget and validator for when used in a form.

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

### Setup

In your `config/ProjectConfiguration.class.php` file, make sure you have
the plugin enabled.

    $this->enablePlugins('sfDoctrineSlotPlugin');

Configuration
-------------

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

Creating, getting & setting slots
---------------------------------

The purpose behind a slot is to allow you to easily add data to a non-existent
field on your model. This in itself is a bit magical - but the magic is
minimized as much as possible.

Before adding and retrieving data from a slot (non-existent field), you'll
need to create it via the `createSlot()` method.

The following code will throw an exception because the field doesn't exist
on the schema for the `Blog` model and the `link_url` hasn't yet been created
as a slot:

    echo $blog->link_url;

Once you create the slot, however, it can be accessed and mutated like
any other field:

    $blog->createSlot('link_url');

    $blog->link_url = 'http://www.sympalphp.org';
    echo $blog->link_url;

The `createSlot()` method also has an optional string second argument
`$type` (see next section) and an optional third argument `$defaul_value`:

    $blog->createSlot('link_url', 'url', 'http://www.sympalphp.org');

>**NOTE**
>Like a real field, the `link_url` value won't be persisted into the
>database until you call `$blog->save()`. The same is true for the
>`removeSlot()` method.

Field types
-----------

As we'll cover in the next section, it's very easy to add any slot fields
to a form object so that they can be saved to the database.

Unfortunately, since each slot field isn't defined in `schema.yml`, a widget
and validator can't be setup for it automatically. Furthermore, setting up
a slot field in your form defeats the purpose of a slot. A slot should be
a fringe-case - if it's so well-known that you setup its widget and validator
in a form class, then that field should be a part of your model's schema
in the first place.

To address this problem, each slot field is assigned a string "type", which is
saved in the database for that slot. Each type consists of a widget and
a validator. These slot types are defined entirely in `app.yml` and can
be fully configured:

all:
  doctrine_slot:
    default_type:    text
    types:
      textarea:
        widget:    sfWidgetFormTextarea
        validator: sfValidatorString

The `type` for a slot is set as the second argument of `createSlot()`. If
left blank, it will default to the type defined by `url` in `app.yml`:

    $blog->createSlot('link_url', 'text', 'http://www.sympalphp.org');

In the above example, the `url` field defines an `sfWidgetFormInputText`
widget and an `sfValidatorUrl` validator.

Adding slots to a form
----------------------

With the definition of a field type for each slot, adding slots to a form
is easy. To add any slot fields to your form, call `addSlotFields()` on
your form object:

    class BlogForm extends BaseBlogForm
    {
      public function configure()
      {
        $this->addSlotFields();
      }
    }

Any slot fields on the form's `Blog` object will be added to the form with
the widget and validator defined by that slot's field type. You can also
pass an array to `addSlotFields()` with the name of the fields to add (all
other slot fields won't be added to the form).

Since you won't know the name of your slot fields, iterate through the slot
fields and render them:

    <?php foreach($form->getSlotFields() as $name): ?>
      <?php echo $form[$name]->renderRow() ?>
    <?php endforeach; ?>

Efficient querying
------------------

Without the proper query, the use of slots can increase the number of
queries that your application makes. To avoid this, simply joing over
to the `Slots` relationship when querying for your object:

    $blog = Doctrine_Query::create()
      ->from('Blog b')
      ->leftJoin('b.Slots s');

Or even easier, use the helper method on your table class:

    class BlogTable extends Doctrine_Table
    {
      public function find($id)
      {
        $q = $this->createQuery('b')
          ->where('b.id = ?', $id);

        $q =$this->addSlotQuery($q);

        return $q->execute();
      }
    }

The `addSlotQuery` adds the join portion of the query for you. The first
argument is optional. The second argument specifies the alias to use for
`Slots` and defaults to `a`.

Reference
---------

The `sfDoctrineSlotTemplate` behavior adds a few other methods to your
model.

 * `createSlot($name)` Gets or creates a slot of the given name and returns
   the `sfDoctrineSlot` object. If the slot doesn't already exist, this will
   create a new `sfDoctrineSlot` object and link it to your model via the
   many-to-many relationship behind the scenes. While the `sfDoctrineSlot`
   object is created immediately, you'll need to call `save()` on your
   record before the link is completed in the database. This means that
   the slot will act just like setting any normal field.

 * `getSlot($name)` Equivalent to `createSlot($name)` except that it returns
   null of the slot doesn't exist.

 * `hasSlot($name)` Returns true if the slot exists

 * `removeSlot($name)` Removes the slot reference to this object (if it
   exists). The `sfDoctrineSlot` object itself will remain. You must call
   `save()` on your record before the reference is removed in the database.

Several other methods are available. See
[sfDoctrineSlotTemplate](http://github.com/weaverryan/sfDoctrineSlotPlugin/blob/master/lib/behaviors/sfDoctrineSlotTemplate.class.php)
for more methods and their full details.

Known Issues
------------

Be careful with how you name your slots. This functionality uses a lot of
Doctrine magic, and each column (e.g. `my_field`) is translated internally
to the lower camel-case getter/setter (e.g. `getMyField()`) and then translated
back to the field name (e.g. `my_field`). Some field names will not make
this translate well. For example:

    my_slot_1 => getMySlot1() => my_slot1

In other words, by the time this plugin is notified of the field, it is
improperly named.

Care to Contribute?
-------------------

Please clone and improve this plugin! This plugin is by the community and
for the community and I hope it can be final solution for handling menus.

If you have any ideas, notice any bugs, or have any ideas, you can reach
me at ryan.weaver [at] iostudio.com.

A bug tracker is available at
[http://redmine.sympalphp.org/projects/sfdoctrineslotplugin](http://redmine.sympalphp.org/projects/sfdoctrineslotplugin)

This plugin was taken from [sympal CMF](http://www.sympalphp.org) and was
developed by both Ryan Weaver and Jon Wage.