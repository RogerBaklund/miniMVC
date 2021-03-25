<?php
# mini MVC
# rb01082015

error_reporting(E_ALL);  // !! dev mode
ini_set('display_errors',1);

set_error_handler(function($level, $message, $file, $line, $context)  {
  if($level == E_USER_ERROR) {
    echo "$message\n";
    return true;
  }
  return false;
});

class Model {
  const 
    ERROR_TRIGGER = 1,
    ERROR_THROW = 2,
    ERROR_DIE = 3,
    ERROR_IGNORE = 4;
  static $_error_cancel=false;
  protected $_cancelled;
  protected $_dirty;
  protected $_data;
  protected $_loaded;
  static protected $_error_mode = self::ERROR_TRIGGER;
  
  function __construct($data=false) {
    $this->_cancelled = false;
    $this->_dirty = false;
    $this->_loaded = false;
    if(is_a($data,'Model')) 
      $data = $data->data();
    elseif(is_object($data))
      $data = (array) $data;
    $this->_data = $data ? $data : array();
  }
  static function errormode($mode) {
    self::$_error_mode = $mode;
  }
  static function error($msg) {
    self::$_error_cancel = true;
    $bt = debug_backtrace();
    $i = 1; # count($bt) - 1;
    $caller = $bt[$i];
    /*
    while(!isset($caller['file']) && $i<count($bt)) {    
      $i++;
      echo "i=$i ";
      var_dump($caller);
      echo "\n";
      $caller = $bt[count($bt) - $i];
    }
    */
    $prefix = 'ERROR in '.$caller['file'].' line '.$caller['line'].', '.
      (isset($caller['class'])?$caller['class'].'::':'').$caller['function'].'(): ';
    switch(self::$_error_mode) {
      case self::ERROR_TRIGGER:
        trigger_error($prefix.$msg,E_USER_ERROR);
        #die(1);
        break;
      case self::ERROR_THROW:
        throw new Exception($msg);
        break;
      case self::ERROR_DIE:
        die($prefix.$msg);
        break;
      case self::ERROR_IGNORE: 
        break;
    }
    return false;
  }
  
  function __set($name,$value) {
    if((isset($this->_data[$name]) && $this->_data[$name] != $value) || 
       (!isset($this->_data[$name]))) $this->_dirty = true;
    $this->_data[$name] = $value;
  }
  
  function __get($name) {
    if(!isset($this->_data[$name])) return $this->error('Undefined property: "'.$name.'"');
    return $this->_data[$name];
  }
  
  function __isset($name) {
    return isset($this->_data[$name]);
  }

  function __unset($name) {
    unset($this->_data[$name]);
  }
  
  function update($changes,$settings=false) {
    if(!$settings) $settings = array();
    $accept_new = (isset($settings['accept_new']) && !$settings['accept_new']) ? false : true;
    $return_changes = (isset($settings['return_changes']) && $settings['return_changes']) ? true : false;
    $return_new = (isset($settings['return_new']) && $settings['return_new']) ? true : false;
    $chg = array();
    $ins = array();
    if(is_a($changes,'Model'))
      $changes = $changes->data();
    elseif(is_object($changes)) 
      $changes = (array) $changes;
    foreach($changes as $k=>$v) {
      if(array_key_exists($k,$this->_data) && $this->_data[$k] != $v) {
        $chg[$k] = $this->_data[$k]; // old value 
        $this->_data[$k] = $v;
      }
      if($accept_new && !array_key_exists($k,$this->_data)) {
        $ins[] = $k; // key only
        $this->_data[$k] = $v;
     }
    }
    if($chg || $ins) $this->_dirty = true;
    if($return_changes && $return_new) return array($chg,$ins);
    elseif($return_changes) return $chg;
    elseif($return_new) return $ins;
  }

  function data() {
    return $this->_data;
  }

  function names() {
    return array_keys($this->_data);
  }

  function values() {
    return array_values($this->_data);
  }

  function load() {}
  function save() {}
  
  function cancel() {
    $this->_cancelled = true;
  }
  
  function isCancelled() {
    return $this->_cancelled;
  }
  
  function isDirty() {
    return $this->_dirty;
  }
  
  function isLoaded() {
    return $this->_loaded;
  }

  function __destruct() {
    if(!$this->_cancelled && !self::$_error_cancel && $this->_dirty) 
      $this->save();
  }
}

class JSONModel extends Model {
  protected $_fn;

  function __construct($fn=false,$data=false) {
    $this->_fn = $fn;
    parent::__construct($data);
    if($fn && file_exists($fn)) {
      $h = fopen($fn,'rb');
      flock($h,LOCK_SH);
      $this->_data = json_decode(file_get_contents($fn),true);
      flock($h,LOCK_UN);
      fclose($h);    
      $this->_loaded = true;
    } 
  }

  function saveAs($fn) {
    $this->_fn = $fn;
    return $this->save();
  }

  function save() {
    if(!$this->_fn) return false;
    file_put_contents($this->_fn,json_encode($this->_data),LOCK_EX);
    $this->_dirty = false;
    return true;
  }
}

// move to View.classes.php  ?
class View {
  static private $default_path = 'views/';
  static private $default_ext = '.php';
  function __construct($template,$path=NULL,$ext=NULL) {
    $this->template = $template;
    if(is_string($path))
      $this->path = $path;
    else
      $this->path = self::$default_path;
    if(is_array($path))
      $this->data = $path;
    elseif(is_object($path))
      $this->data = $path;
    if(is_null($ext))
      $this->ext = self::$default_ext;
    else
      $this->ext = $ext;
  }
  
  static function setDefaultPath($path) { self::$default_path = $path; }
  static function setDefaultExt($ext) { self::$default_ext = $ext; }
  static function getDefaultPath($path) { return self::$default_path; }
  static function getDefaultExt($ext) { return self::$default_ext; }
  
  function getData($data) {
    if(!$data && isset($this->data))
      $data = $this->data;
    if($data) {
      if(is_a($data,'Model')) 
        $data = $data->data();
      elseif(is_object($data))
        $data = (array) $data;
    }
    return is_array($data) ? $data : array($data);
  }
  
  function render($data=false) {
    ob_start();  // warning/notice/error from include goes into the return value
    extract($this->getData($data));
    include $this->path.$this->template.$this->ext;
    return ob_get_clean();
  }
  function loop($items,$keys=false,$sep='') {
    if($keys) 
      $items = array_map(function($rec) use($keys) { 
        return array_combine($keys,$rec);
      },$items);
    return implode($sep,array_map(array($this,'render'),$items));
  }
  function __toString() { return $this->render(); }
}

class InlineView extends View {
  function __construct($template) {
    parent::__construct('','data://text/plain,','');
    $this->template = urlencode($template);
  }
}

class StringView extends View { 
  function __construct($template,$data=false) {
    parent::__construct($template,$data,'');
  }
  function render($data=false) {
    extract($this->getData($data));
    return eval('return "'.addcslashes($this->template,'"').'";');
  }
}

class FormatView extends View {
  # See PHP docs for format specifiers
  # http://php.net/manual/en/function.sprintf.php
  function __construct($template,$data='') {
    parent::__construct($template,$data,'');
  }
  function render($data=false) {
    $data = $this->getData($data);
    return vsprintf($this->template,$data); // array_values()
  }
}

class Controller {

  function __construct($path) {
    $this->path = $path;
  }

  function _Default($params) {
    // NOTE: this depends on the pattern in the Route
    @list($path,$controller,$action,$params) = $this->path;
    echo '<p>This is the '.__FUNCTION__.' method in the '.__CLASS__.' class<br />File: '.__FILE__.'</p>';
    echo '<p>Requested path: <b>'.$path.'</b><br />';
    echo 'Requested controller: <b>'.$controller.'</b><br />';
    echo 'Requested action: <b>'.$action.'</b><br />';
    echo 'Parameters: <b>'.$params.'</b></p>';
    echo '<p>You see this message because a _Default() method was not defined in '.get_class($this).'.</p>';
    die('<p style="color:red">Implement '.$controller.'Controller class'.($action?' and '.$action.' method':'').'!</p>');
  }

}

// move to Route.class.php ?
/*

Pattern:

/{$controller}/{$action}/{$params}
/{$class}/{$method}/{$params}
/{$file}/{$function}/{$params}

preg_match_all('/{\$([a-z][a-z0-9_]*)}/i',$pattern,$m);
call_user_func_array($callback,$m[1]);

*/
abstract class Route {
  const DefaultRoute = 'Route::DefaultRoute';

  static function DefaultPattern($install_path='/') {
    return '@^'.$install_path.'([^/?]*)/?([^/?]*)/?([^?]*)@';
  }

  static function handle_methods($accepted_methods,$path_regexp,$control) {
    if(!in_array($_SERVER['REQUEST_METHOD'],$accepted_methods)) return;
    $found = preg_match($path_regexp,$_SERVER['REQUEST_URI'],$m);
    if($found === false) die('Invalid route: '.htmlentities($path_regexp));
    if($found && $m) {
      #$control($m);
      call_user_func_array($control,array($m));
    }
  }
  
  static function get($path_regexp,$control) {
    self::handle_methods(array('GET'),$path_regexp,$control);
  }
  
  static function post($path_regexp,$control) {
    self::handle_methods(array('POST'),$path_regexp,$control);
  }
  
  static function request($path_regexp,$control) {
    self::handle_methods(array('GET','POST'),$path_regexp,$control);
  }

  static function DefaultRoute($path) {
    $controller_class = $path[1] ? $path[1] : 'Default';
    $action = $path[2] ? $path[2] : '_Default';
    #$params = $path[3]; # ? array_filter(explode('/',$path[3])) : array('_Default');
    $fn = CONTROLLER_PATH.$controller_class.'Controller.php';
    if(!file_exists($fn)) {
      $controller_class = 'Default';
      $action = '_Default';
    }
    $fn = CONTROLLER_PATH.$controller_class.'Controller.php';
    if(file_exists($fn)) {
      include($fn);
      $bootstrap_return = isset($bootstrap) ? $bootstrap($path) : NULL;
      if(isset($noclass) && $noclass) {}
      else {
        $controller = $controller_class.'Controller';
        $loaded = new $controller($path);
        if(!method_exists($loaded,$action))
          $action = '_Default';
        $loaded->$action($bootstrap_return); // call action method
      }
    } else die('Configuration error, '.($loaded ? 'can not reload '.$controller_class : $fn.' was not found'));
    exit;
  }
  
}

?>