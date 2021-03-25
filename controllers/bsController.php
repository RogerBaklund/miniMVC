<?php

require_once 'BootstrapUI.php';
require_once 'DefaultController.php';
require_once 'infoController.php';

class UI extends BootstrapUI {}

# This is done only so that we can use a method 
# that is protected in the infoController
class JSONReader extends infoController {
  static function getJSON($fn) {
    return self::getJSONArticle($fn);
  }
}

class bsController extends DefaultController {
  const APP_SUB_SECTION = 'Bootstrap test';
  
  function _Default($params) {
    $this->initPage();
    if($this->action)
      $this->content .= '<p>'.$this->action.' is not implemented</p>';
    else 
      $this->content .= UI::Container('<p><a href="'.APP_PATH.'">exit demo</a></p>'.new View('frontpage_info'));
    $this->showPage($this->content,$this->extra_style);
  }
  
  ### Public methods, accessible from URL
  
  function Views() {    
    $this->infoPage('views');
  }
  function Models() {
    $this->infoPage('models');
  }
  function Controllers() {
    $this->infoPage('controllers');
  }
  function Route() {
    $this->infoPage('route');
  }
  function Utils() {
    $this->infoPage('utils');
  }
  
  ########
  
  protected function makeMenuItem($label,$path) { # $path includes action
    if(in_array($label,array('Views','Models','Utils'))) {
      $section = strtolower($label);
      $items = array_map(function($f){
        return basename($f,'.json');
      },glob($_SERVER['DOCUMENT_ROOT'].APP_PATH.'json/info/'.$section.'/*.json'));
      # Remove suffix when sorting
      $L = strlen($label)-1;  # Views -> View, Models -> Model
      usort($items,function($a,$b) use ($L) {
        $a1 = substr($a,0,strlen($a)-$L);
        $b1 = substr($b,0,strlen($b)-$L);
        return $a1 > $b1 ? 1 : -1;
      });
      $sectionpath = APP_PATH.$this->controller.'/'.$section.'/';
      $items = array_map(function($n) use ($sectionpath) {      
        return UI::MenuItem($n,$sectionpath.$n);
      },$items);
      array_unshift($items,UI::Divider());
      array_unshift($items,UI::MenuItem('Overview',APP_PATH.$this->controller.'/'.$section));
      return UI::NavBarDropdown($label,$items);
    } else 
      return UI::MenuItem($label,APP_PATH.$this->controller.'/'.$path);
  }

  protected function infoPage($section) {
    $this->initPage();
    $fn = 'json/info/'.$section.($this->params ? '/'.$this->params:'').'.json';
    if(file_exists($fn)) {
      $this->content .= UI::Container(JSONReader::getJSON($fn));
      $this->extra_style .= '.article p.intro {font-weight:bold;}'; 
    } else 
      $this->content .= UI::Container(error("Did not find file $fn"));
    $this->showPage($this->content,$this->extra_style);
  }
  
  ######## move to generic BS controller ?

  protected function initPage() {
    $this->head = '<title>'.APP_NAME.': '.$this::APP_SUB_SECTION.'</title>';
    $this->head .= UI::LoadAll_CDN();
    $this->extra_style = '';
    $this->content = $this->topMenu();
    $this->extra_style .= 'body{margin-top:50px;}'; # required by topMenu
  }
  
  protected function showPage($content,$extra_style = '') {
    echo '<html><head>'.$this->head.
      ($extra_style ? '<style type="text/css">'.$extra_style.'</style>':'').
      '</head><body>'.$content.'</body></html>';
  }
  protected function getPublicMethods() {
    $R = new ReflectionClass($this);
    return $R->getMethods(ReflectionMethod::IS_PUBLIC);
  }
  
  /* protected function makeMenuItem($label,$path) { # $path includes action
    return UI::MenuItem($label,APP_PATH.$this->controller.'/'.$path);
  } */
  protected function topMenu() {
    $menu = array();
    foreach($this->getPublicMethods() as $m) {
      if($m->name[0] == '_') continue;  # _Default & __construct
      $name = str_replace('_',' ',$m->name);
      $menu[] = $this->makeMenuItem($name,$m->name);
    }
    return UI::NavBar(array(
              UI::NavBarHeader(APP_NAME,APP_PATH.$this->controller),
              '<div class="collapse navbar-collapse" id="navbarmenu">'.
              UI::NavBarMenu($menu).
              UI::NavBarText($this::APP_SUB_SECTION.'&nbsp;',true).  
              '</div>'),true,true);# inverse=true, fixed=true
  }

}