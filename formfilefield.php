<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;

class FormfilefieldPlugin extends Plugin
{
  public static function getSubscribedEvents()
  {
    return [
      'onTwigTemplatePaths' => ['onTwigTemplatePaths', 0],
      'onTwigSiteVariables' => ['onTwigSiteVariables', 0],
      'onFormProcessed' => ['onFormProcessed', 0]
    ];
  }

  public function onTwigTemplatePaths()
  {
    $this->grav['twig']->twig_paths[] = __DIR__ . '/templates';
  }

  /*
   * Provide modifications to form template for uploading file.
   */
  public function onTwigSiteVariables()
  {
    if (property_exists($this->grav['page']->header(), 'form')) {
      // TODO: update to 5.5 and the following function will work.
      // if (array_search('file', array_column($this->grav['page']->header()->form['fields'], 'type'))) {

      // check if any form fields are of type 'file'
      if (array_search('file', array_map(function($data) {return $data['type'];}, $this->grav['page']->header()->form['fields']))) {
        // add enctype attribute with the value of multipart/form-data to the form
        $this->grav['twig']->twig_vars['formfilefield']['attributes']['enctype'] = "multipart/form-data";
      }
    }
  }

  /*
   * Process uploaded file.
   */
  public function onFormProcessed()
  {
    if (!empty($_FILES)) {
      // if needed, create dir in data dir for uploaded file
      // TODO: assumes tha there is a text field called 'name' that it can use for a dir name
      //   - make this dynamically find the first text field in the form.
      //   - document that a text field is required on the form
      //     or
      //     use the datestamp for the dir name
      if (!file_exists($_SERVER['DOCUMENT_ROOT'] .'/user/data/'. $this->grav['page']->header()->form['name'] .'/'. date('Ymd-His') .'-'. $_POST['name'])) {
        mkdir($_SERVER['DOCUMENT_ROOT'] .'/user/data/'. $this->grav['page']->header()->form['name'] .'/'. date('Ymd-His') .'-'. $_POST['name'], 0775, true);
      }
      // move uploaded file to data dir
      move_uploaded_file($_FILES['upload']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] .'/user/data/'. $this->grav['page']->header()->form['name'] .'/'. date('Ymd-His') .'-'. $_POST['name'] .'/'. $_FILES['upload']['name']);
    }
  }
}