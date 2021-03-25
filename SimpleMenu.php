<?php

class SimpleMenu {
  
  static $APP_PATH = '';
  
  function __construct($app_path='') {
    self::$APP_PATH = $app_path;
  }
  
  static function menu($items,$extra_class=false) {
    return '<ul class="menu'.($extra_class?' '.$extra_class:'').'">'."\n  <li>".
      implode("</li>\n  <li>",$items).
      "</li>\n</ul>".
      '<br style="clear:left"/>';
  }

  static function item($label,$url='') {
    #if($url == false) $url = $label;
    #if(is_array($url))
    #  return self::menu($url,'sub');
    $current = (substr($_SERVER['REQUEST_URI'],0,strlen(self::$APP_PATH.$url))==self::$APP_PATH.$url && 
      ($url || $_SERVER['REQUEST_URI'] == self::$APP_PATH)) ? 
      ' class="current"':'';
    return '<a href="'.self::$APP_PATH.$url.'"'.$current.'>'.htmlentities($label).'</a>';
  }

  static function CSS($config=NULL) {
    $conf = array(
      'font-size' => '90%',
      'item-width' => '8em',
      'item-padding' => '4px',
      'sub-indent' => '1em',
      'sub-class' => 'submenu',
      # colors
      'normal'=>'white/black',
      'hover'=>'black/silver',
      'current'=>'black/darkgray',
      's_normal'=>'white/darkgreen',
      's_hover'=>'black/limegreen',
      's_current'=>'yellow/green',
      );
    if($config) # modify defaults
      foreach($config as $k=>$v)
        $conf[$k] = $v;
    list($normal_fg,$normal_bg) = explode('/',$conf['normal'],2);
    list($hover_fg,$hover_bg) = explode('/',$conf['hover'],2);
    list($current_fg,$current_bg) = explode('/',$conf['current'],2);
    list($s_normal_fg,$s_normal_bg) = explode('/',$conf['s_normal'],2);
    list($s_hover_fg,$s_hover_bg) = explode('/',$conf['s_hover'],2);
    list($s_current_fg,$s_current_bg) = explode('/',$conf['s_current'],2);
    $sub = $conf['sub-class'];
    $menu_style = <<<EOD
    ul.menu {list-style-type:none;margin:0;padding:0;}
    ul.menu a:link, ul.menu a:visited  {
      display: block;
      text-align: center;
      text-decoration: none;
      font-size: {$conf['font-size']};
      width: {$conf['item-width']};
      padding: {$conf['item-padding']};
      color: $normal_fg;
      background-color: $normal_bg;
    }
    ul.menu a:hover,ul.menu a:active {
      color: $hover_fg;
      background-color: $hover_bg;
    }
    ul.menu a.current {
      color: $current_fg;
      background-color: $current_bg;
    }
    ul.menu li {float:left;}
    
    ul.$sub {margin-left:{$conf['sub-indent']};}
    ul.$sub a:link, ul.$sub a:visited  {
      color:$s_normal_fg;
      background-color:$s_normal_bg;
    }
    ul.$sub a:hover,ul.$sub a:active {
      color: $s_hover_fg;;
      background-color: $s_hover_bg;;
    }
    ul.$sub a.current {
      color: $s_current_fg;
      background-color: $s_current_bg;
    }

EOD;
    return $menu_style;
  }
}