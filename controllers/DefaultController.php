<?php

#$bootstrap = function() { echo '<p>bootstrap in DefaultController.php was called</p>'; };

// set $noclass to true if this file does NOT contain a class definition
#$noclass = true;

class DefaultController extends Controller {

  function __construct($path) {
    parent::__construct($path);
    $this->request_method = $_SERVER['REQUEST_METHOD'];
    $this->controller= urldecode($path[1]);
    $this->action = urldecode($path[2]);
    $this->params = urldecode($path[3]);
  }

  function _Default($params) {
    if($this->action)
      $content = get_class($this) == $this->controller.'Controller' ? 
        error($this->controller.'Controller has no '.$this->action.' method!') : 
        error($this->controller.'Controller was not found!');
    elseif($this->controller)
      $content = get_class($this) == $this->controller.'Controller' ? 
        error($this->controller.'Controller has no _Default() method! ') :
        error($this->controller.'Controller was not found!');
    else
      $content = new View('frontpage_info','views/');
    $this->ShowPage($content);
  }

  protected function ShowPage($content,$extra_style='') {  # protected to avoid URI call
    
    require_once 'SimpleMenu.php';

    $mm = new SimpleMenu(APP_PATH);

    $extra_style .= $mm->CSS(); // conf. possible, using defaults

    $page_view = new View('page_template','views/');
    $head_view = new View('head_template','views/');
    $body_view = new View('body_template','views/');
    
    $main_menu = $mm->menu(array(
      $mm->item('Home',''),
      $mm->item('Views','info/Views'),
      $mm->item('Models','info/Models'),
      $mm->item('Controllers','info/Controllers'),
      $mm->item('Route','info/Route'), # !! RouteS ?
      $mm->item('Utils','info/Utils')));
    
    $sub_menu = '';
    
    if($this->controller == 'info') {
      if($this->action == 'Views')
        $sub_menu = $mm->menu(array(
          $mm->item('View','info/Views/View'),
          $mm->item('InlineView','info/Views/InlineView'),
          $mm->item('StringView','info/Views/StringView'),
          $mm->item('FormatView','info/Views/FormatView')
          ),'submenu');
      elseif($this->action == 'Models')
        $sub_menu = $mm->menu(array(
          $mm->item('Model','info/Models/Model'),
          $mm->item('JSONModel','info/Models/JSONModel'),
          $mm->item('DBModel','info/Models/DBModel'),
          $mm->item('DBStatusModel','info/Models/DBStatusModel'),
          $mm->item('DBTableModel','info/Models/DBTableModel'),
          $mm->item('DBRecordModel','info/Models/DBRecordModel')
          ),'submenu');    
      elseif($this->action == 'Utils')
        $sub_menu = $mm->menu(array(
          $mm->item('FormBuilder','info/Utils/FormBuilder'),
          $mm->item('SimpleMenu','info/Utils/SimpleMenu'),
          $mm->item('SimpleInput','info/Utils/SimpleInput'),
          $mm->item('BootstrapUI','info/Utils/BootstrapUI')
          ),'submenu');    
    }
    
    $body = new Model();
    $body->top = '<h1>'.APP_NAME.'<span style="font-size:60%;color:silver"> v'.APP_VERSION.'</span>'.'</h1>';
    $body->menu = $main_menu.$sub_menu;
    $body->content = $content;
    $body->footer = '&copy; 2015 '.APP_AUTHOR;

    $page = new Model();
    $page->title = APP_NAME;
    $page->head = $head_view->render(array('extra_style'=>$extra_style));
    $page->body = $body_view->render($body);

    echo $page_view->render($page);
    
  }
  
}
