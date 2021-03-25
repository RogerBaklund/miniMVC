<?php

require_once 'DefaultController.php';

class infoController extends DefaultController {

  function _Default($params) {
  
    // This class does not use action methods, instead $this->action holds 
    // the name of the JSON file to load or the dictionary for the file if
    // $this->params is also provided.
    
    if($this->action) {
      if($this->params)
        $fn = 'json/info/'.$this->action.'/'.$this->params.'.json';
      else
        $fn = 'json/info/'.$this->action.'.json';
      if(file_exists($fn)) 
        $content = self::getJSONArticle($fn);
      else 
        $content = error("Did not find file $fn");
    } else 
      $content = error("No info page was requested");
    
    $this->ShowPage($content,'pre{margin:0}');    
  }
  
  static protected function getJSONArticle($fn) {
    $data = new JSONModel($fn);
    $data->example = example($data->example);
    $data->cancel(); // don't save
    $article_view = new View('article');
    return $article_view->render($data);
  }
  
}
