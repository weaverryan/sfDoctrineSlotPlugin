<?php

require_once dirname(__FILE__).'/../bootstrap/functional.php';

$browser = new sfTestFunctional(new sfBrowser());
$browser->setTester('doctrine', 'sfTesterDoctrine');
Doctrine_Query::create()->from('Blog')->delete()->execute();

// prime to this action, it creates our Blog object
$browser->get('/form');
Doctrine_Core::getTable('Blog')->getConnection()->clear();
$blog = Doctrine_Query::create()->from('Blog')->fetchOne();
$form = new BlogForm($blog);
$form->addSlotFields();

$browser->info('1 - Submit a form with slot fields and check the results')
  ->get('/form')
  
  ->with('request')->begin()
    ->isParameter('module', 'main')
    ->isParameter('action', 'form')
  ->end()
  
  ->with('response')->begin()
    ->isStatusCode(200)
    ->checkForm($form)
    ->checkElement('#blog_link_url', true)
  ->end()
  
  ->info('  1.1 - Submit a form with validation errors')
  ->click('Submit', array('blog' => array(
    'link_url' => 'not a url',
  )))

  ->with('form')->begin()
    ->hasErrors(1)
    ->isError('link_url', 'invalid')
  ->end()
  
  ->info('  1.2 - Submit a valid form - check the values')
  
  ->click('Submit', array('blog' => array(
    'title'     => 'functional test title',
    'en'        => array('body' => 'a short post'),
    'link_url'  => 'http://www.sympalphp.org',
  )))
  
  ->with('form')->begin()
    ->hasErrors(0)
  ->end()
  
  ->with('doctrine')->begin()
    ->check('Blog', array(
      'title'     => 'functional test title',
    ))
    ->check('BlogTranslation', array(
      'body'      => 'a short post',
      'lang'      => 'en',
    ))
    ->check('sfDoctrineSlot', array(
      'name'  => 'link_url',
      'value' => 'http://www.sympalphp.org',
      'type'  => 'url',
    ))
  ->end()
;