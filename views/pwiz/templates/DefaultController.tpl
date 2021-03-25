
### File generated <?=$timestamp?> by <?=$appname?> v<?=$appversion?> ###

<?=$controller_includes?>

// Enable this if you need initialization, return value is sent to methods
#$bootstrap = function() { return ...; };

// Set $noclass to true if this file does NOT contain a class definition
#$noclass = true;

function error($msg) {
  return '<p style="color:red;"><b>Error:</b> '.nl2br(htmlentities($msg)).'</p>';
}

class DefaultController extends Controller {

  function __construct($path) {
    parent::__construct($path);
    $this->request_method = $_SERVER['REQUEST_METHOD'];
    $this->controller= urldecode($path[1]);
    $this->action = urldecode($path[2]);
    $this->params = urldecode($path[3]);
  }

  function _Default() {
    if($this->action)
      $content = get_class($this) == $this->controller.'Controller' ? 
        error($this->controller.'Controller has no '.$this->action.' method!') : 
        error($this->controller.'Controller was not found!');
    elseif($this->controller)
      $content = get_class($this) == $this->controller.'Controller' ? 
        error($this->controller.'Controller has no _Default() method! ') :
        error($this->controller.'Controller was not found!');
    else
      $content = <?php if($views > 1) { ?>new View('frontpage')<?php } else { ?>''<?php } ?>;
    $this->ShowPage($content);
  }

  protected function ShowPage($content,$extra_style='') {  # protected to avoid URI call
    
<?=$menu_code?>

<?=$view_code?>

  }
  
}
