<?php

class mainActions extends sfActions
{
  // displays the form
  public function executeForm(sfWebRequest $request)
  {
    $this->form = new BlogForm($this->getOrCreateBlog());
    $this->form->addSlotFields();
    $this->form->embedI18n(array('en'));
  }

  // handles the form submit
  public function executeSubmit(sfWebRequest $request)
  {
    $this->form = new BlogForm($this->getOrCreateBlog());
    $this->form->addSlotFields();
    $this->form->embedI18n(array('en'));

    $this->form->bind($request->getParameter($this->form->getName()));

    if ($this->form->isValid())
    {
      $this->form->save();
      
      $this->setTemplate('result');
    }
    else
    {
      $this->setTemplate('form');
    }
  }

  // returns a Blog instance
  protected function getOrCreateBlog()
  {
    $blog = Doctrine_Query::create()->from('Blog')->fetchOne();

    if (!$blog)
    {
      $blog = new Blog();
      $blog->title = 'functional test';
      $blog->save();
    }

    $blog->createSlot('link_url', 'url', 'test');

    return $blog;
  }
}
