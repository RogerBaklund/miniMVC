<?php

abstract class BootstrapUI {

  const IGNORE = 0,
        TRIGGER_NOTICE = 1,
        TRIGGER_WARNING = 2,
        TRIGGER_ERROR = 3,
        THROW_EXCEPTION = 4;
  
  /* 3.3.1
  static $IconNames = " asterisk plus minus euro cloud envelope pencil glass music search heart star star-empty user film 
    th-large th th-list ok remove zoom-in zoom-out off signal cog trash home file time road download-alt download upload 
    inbox play-circle repeat refresh list-alt lock flag headphones volume-off volume-down volume-up qrcode barcode tag tags 
    book bookmark print camera font bold italic text-height text-width align-left align-center align-right align-justify 
    list indent-left indent-right facetime-video picture map-marker adjust tint edit share check move step-backward 
    fast-backward backward play pause stop forward fast-forward step-forward eject chevron-left chevron-right plus-sign 
    minus-sign remove-sign ok-sign question-sign info-sign screenshot remove-circle ok-circle ban-circle arrow-left 
    arrow-right arrow-up arrow-down share-alt resize-full resize-small exclamation-sign gift leaf fire eye-open eye-close 
    warning-sign plane calendar random comment magnet chevron-up chevron-down retweet shopping-cart folder-close folder-open  
    resize-vertical resize-horizontal hdd bullhorn bell certificate thumbs-up thumbs-down hand-right hand-left hand-up 
    hand-down circle-arrow-right circle-arrow-left circle-arrow-up circle-arrow-down globe wrench tasks filter briefcase 
    fullscreen dashboard paperclip heart-empty link phone pushpin usd gbp sort sort-by-alphabet sort-by-alphabet-alt 
    sort-by-order sort-by-order-alt sort-by-attributes sort-by-attributes-alt unchecked expand collapse-down collapse-up 
    log-in flash log-out new-window record save open saved import export send floppy-disk floppy-saved floppy-remove 
    floppy-save floppy-open credit-card transfer cutlery header compressed earphone phone-alt tower stats sd-video 
    hd-video subtitles sound-stereo sound-dolby sound-5-1 sound-6-1 sound-7-1 copyright-mark registration-mark 
    cloud-download cloud-upload tree-conifer tree-deciduous ";
    */
  static $IconNames = " asterisk plus euro eur minus cloud envelope pencil glass music search heart star star-empty user film 
  th-large th th-list ok remove zoom-in zoom-out off signal cog trash home file time road download-alt 
  download upload inbox play-circle repeat refresh list-alt lock flag headphones volume-off volume-down
  volume-up qrcode barcode tag tags book bookmark print camera font bold italic text-height text-width 
  align-left align-center align-right align-justify list indent-left indent-right facetime-video 
  picture map-marker adjust tint edit share check move step-backward fast-backward backward play  pause 
  stop forward fast-forward step-forward eject chevron-left chevron-right plus-sign minus-sign remove-sign
  ok-sign question-sign info-sign screenshot remove-circle ok-circle ban-circle arrow-left arrow-right 
  arrow-up arrow-down share-alt resize-full resize-small exclamation-sign gift leaf fire eye-open 
  eye-close warning-sign plane calendar random comment magnet chevron-up chevron-down retweet shopping-cart 
  folder-close folder-open resize-vertical resize-horizontal hdd bullhorn bell certificate thumbs-up 
  thumbs-down hand-right hand-left hand-up hand-down circle-arrow-right circle-arrow-left circle-arrow-up 
  circle-arrow-down globe wrench tasks filter briefcase fullscreen dashboard paperclip heart-empty link 
  phone pushpin usd gbp sort sort-by-alphabet sort-by-alphabet-alt sort-by-order sort-by-order-alt 
  sort-by-attributes sort-by-attributes-alt unchecked expand collapse-down collapse-up log-in flash log-out 
  new-window record save open saved import export send floppy-disk floppy-saved floppy-remove floppy-save 
  floppy-open credit-card transfer cutlery header compressed earphone phone-alt tower stats sd-video 
  hd-video subtitles sound-stereo sound-dolby sound-5-1 sound-6-1 sound-7-1 copyright-mark registration-mark 
  cloud-download cloud-upload tree-conifer tree-deciduous cd save-file open-file level-up copy paste alert 
  equalizer king queen pawn bishop knight baby-formula tent blackboard bed apple erase hourglass lamp 
  duplicate piggy-bank scissors bitcoin btc xbt yen jpy ruble rub scale ice-lolly ice-lolly-tasted education
  option-horizontal option-vertical menu-hamburger modal-window oil grain sunglasses text-size text-color 
  text-background object-align-top object-align-bottom object-align-horizontal object-align-left 
  object-align-vertical object-align-right triangle-right triangle-left triangle-bottom triangle-top console 
  superscript subscript menu-left menu-right menu-down menu-up ";
  
  static $Bootstrap_version = '3.3.4';
  static $Bootstrap_CDN_root = 'https://maxcdn.bootstrapcdn.com/bootstrap/';
  
  static $OnError = self::TRIGGER_ERROR;
  
  static $contexttypes = array('success','info','warning','danger'); 
  static $buttonsizes = array('xs','sm','lg');
  static $screensizes = array('xs','sm','md','lg');
  static $imageclasses = array('responsive','rounded','thumbnail','circle');  
  
  # Loading methods
  
  static function LoadAll_CDN($meta=true,$JS=true,$CSS=true) {
    return ($meta ? self::Meta():''). 
           ($JS ? self::JS_CDN():'').
           ($CSS ? self::CSS_CDN():'');
  } 

  static function Meta($encoding=true) {
    return ($encoding ? '
    <meta charset="utf-8">':'').'
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">';
  }
  
  static function JS_CDN($include_jquery=true,$IE8_support=true) {
    return ($include_jquery ? '
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>' : '').'
    <script src="'.self::$Bootstrap_CDN_root.self::$Bootstrap_version.'/js/bootstrap.min.js"></script>'.
    ($IE8_support ? '
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->' : '');
  }
  
  static function CSS_CDN($include_theme=true) {
    #return '<link rel="stylesheet" href="http://bootswatch.com/superhero/bootstrap.css">'; # !! tmp, test
    return '
    <link rel="stylesheet" href="'.self::$Bootstrap_CDN_root.self::$Bootstrap_version.'/css/bootstrap.min.css">'.
    ($include_theme ? '
    <link rel="stylesheet" href="'.self::$Bootstrap_CDN_root.self::$Bootstrap_version.'/css/bootstrap-theme.min.css">':'');
  }
  
  # Error handling
  
  static function OnError($error_action) {
    if(is_callable($error_action) ||
       in_array($error_action,range(self::IGNORE,self::THROW_EXCEPTION)))
      self::$OnError = $error_action;
    else self::Error('Invalid error action for '.__CLASS__.'::OnError()');
  }
  
  static function Error($msg) {
    if(is_callable(self::$OnError)) {
      return call_user_func(self::$OnError,$msg);      
    }
    switch(self::$OnError) {
      case self::TRIGGER_ERROR: trigger_error($msg,E_USER_ERROR); break;
      case self::TRIGGER_WARNING: trigger_error($msg,E_USER_WARNING); break;
      case self::TRIGGER_NOTICE: trigger_error($msg,E_USER_NOTICE); break;
      case self::THROW_EXCEPTION: throw new InvalidArgumentException($msg); break;
      default:break; # IGNORE
    }
  }
  
  # Visibility
  
  private static function _visible_hidden($vh,$type,$content,$display='block') {
    if(!in_array($vh,array('visible','hidden')))
      return self::Error("Expected 'visible' or 'hidden' for ".__METHOD__.'(), got '.$vh);
    if(is_array($content)) $content = implode("\n",$content);
    if(!in_array($display,array('block','inline','inline-block')))
      return self::Error('Invalid display for '.__CLASS__.'::'.ucfirst($vh).'(): '.$display);
    $valid = self::$screensizes;
    $valid[] = 'print';
    if(strpos($type,',')!==false)
      $type=array_map('trim',explode(',',$type));
    if(is_array($type)) {
      $classes = array();
      foreach($type as $t) {
        if(!in_array($t,$valid))
          return self::Error('Invalid type for '.__CLASS__.'::'.ucfirst($vh).'(): '.$type);
        $classes[] = $vh.'-'.$t.($vh=='visible'?'-'.$display:'');
      }
      $classes = implode(' ',$classes);
    } else {
      if(!in_array($type,$valid))
        return self::Error('Invalid type for '.__CLASS__.'::'.ucfirst($vh).'(): '.$type);
      $classes = $vh.'-'.$type.($vh=='visible'?'-'.$display:'');
    }
    return '<div class="'.$classes.'">'.$content.'</div>';
  }

  function Visible($type,$content,$display='block') {
    return self::_visible_hidden('visible',$type,$content,$display);
  }

  function Hidden($type,$content) {
    return self::_visible_hidden('hidden',$type,$content);
  }
  
  # Content handling methods
  
  static function Container($content,$fluid=false) {
    if(is_array($content)) $content = implode("\n",$content);
    return '<div class="container'.($fluid?'-fluid':'').'">'.$content.'</div>';
  }
  
  static function Heading($text) {
    return '<div class="page-header"><h1>'.$text.'</h1></div>';
  }

  static function Jumbotron($content) {
    if(is_array($content)) $content = implode("\n",$content);
    return '<div class="jumbotron">'.$content.'</div>';
  }
  
  static function Label($text,$type='default') {
    if(!in_array($type,array('default','primary') + self::$contexttypes))
      return self::Error('Invalid type for '.__METHOD__.'(): '.$type);
    return '<span class="label label-'.$type.'">'.$text.'</span>';
  }
  
  static function Alert($content,$type='info') {
    if(is_array($content)) $content = implode("\n",$content);
    if(!in_array($type,self::$contexttypes))
      return self::Error('Invalid type for '.__METHOD__.'(): '.$type);
    return '<div class="alert alert-'.$type.'">'.$content.'</div>';
  }
  
  static function Badge($text) {
    return '<span class="badge">'.$text.'</span>';
  }

  # Grid

  static function Row($content) {
    if(is_array($content)) $content = implode("\n",$content);
    return '<div class="row">'.$content.'</div>';
  }
  static function Col($classes,$content) {
    if(!is_array($classes)) 
      $classes = explode(' ',$classes);
    $cl = array_filter($classes,array(__CLASS__,'IsValidColClass'));  
    if($cl != $classes) {
      $bad = array_diff($classes,$cl);
      return self::Error('Illegal class'.(count($bad)>1?'es':'').' for '.__METHOD__.'(): '.implode(' ',$bad));
    }
    $classes = implode(' ',$cl);
    if(is_array($content)) $content = implode("\n",$content);
    return '<div class="'.$classes.'">'.$content.'</div>';
  }
  
  static function IsValidColClass($colclass) {
    $sizes = implode('|',self::$screensizes);
    preg_match("/^col-($sizes)-[1-9][0-2]?$/",$colclass,$m);
    return $m ? true : false;
  }
  
  # Navigation related methods
  
  static function ButtonToolbar($content) {
    if(is_array($content)) $content = implode("\n",$content);
    return '<div class="btn-toolbar" role="toolbar">'.$content.'</div>';
  }
  
  static function ButtonGroup($content,$size=false,$grouptype='horizontal') {
    if($size && !in_array($size,self::$buttonsizes))
      return self::Error('Invalid size for '.__METHOD__.'(): '.$size);
    $default_size = $size;
    if(!in_array($grouptype,array('horizontal','vertical','justified')))
      return self::Error('Invalid group type for '.__METHOD__.'(): '.$grouptype);
    if($grouptype == 'horizontal')
      $grouptype = ''; # default
    elseif($grouptype == 'vertical')
      $grouptype = '-vertical'; 
    else $grouptype = ' btn-group-justified'; 
    if(is_array($content) && 
       array_unique(array_map("is_string", array_keys($content))) === array(true)) { # assoc
      $res = array();
      foreach($content as $href=>$btn) { # !! unique href, $data is not used!!
        #echo 'dbg: '.$href.' -> '.$btn;
        $type = 'default';
        $size = $default_size;
        $disabled = false;
        $classes = 'btn';
        $data = NULL;  # !! 
        #$toggle = false;
        #$target = false;
        if(is_array($btn)) {
          $text = $btn[0];
          if(count($btn) > 1) $type = $btn[1];
          if(count($btn) > 2) $size = $btn[2];
          if(count($btn) > 3) $disabled = $btn[3];
          if(count($btn) > 4) $classes = $btn[4];
          if(count($btn) > 5) $data = $btn[5];
        } else $text = $btn;
        #$data = array();
        #if($toggle) $data['toggle'] = $toggle;
        #if($target) $data['target'] = $target;
        $res[] = self::Button($text,$href,$type,$size,$disabled,$classes,$data);
      }
      $content = implode("\n",$res);
    }
    return '<div class="btn-group'.$grouptype.
           ($default_size?' btn-group-'.$default_size:'').
           '" role="group">'.$content.
           '</div>';
  }
  
  static function Button($text,$href=false,$type='default',$size='',$disabled=false,$classes='btn',$data=NULL) {
    # !! $data is only used for <BUTTON> (when $href is false)
    if($size && !in_array($size,self::$buttonsizes))
      return self::Error('Invalid size for '.__METHOD__.'(): '.$size);
    if($type && !in_array($type,array_merge(array('default','primary'),self::$contexttypes)))
      return self::Error('Invalid type for '.__METHOD__.'(): '.$type);
    if($classes) {
      if(!is_array($classes)) 
        $classes = array_filter(array_map('trim',explode(' ',$classes)));
    }
    if($size && !in_array('btn-'.$size,$classes)) 
      $classes[] = 'btn-'.$size;
    if($type && !in_array('btn-'.$type,$classes)) 
      $classes[] = 'btn-'.$type;
    $classes = implode(' ',$classes);
    $data_str = '';
    if($data) {
      if(!is_array($data))
        return self::Error('The data parameter for '.__METHOD__.'() must be an associative array');
      foreach($data as $k=>$v)
        $data_str .= ' data-'.$k.'="'.$v.'"';
    }
    return $href ? 
        '<a href="'.$href.'" class="'.$classes.($disabled?' disabled':'').'" role="button">'.$text.'</a>' : 
        '<button type="button" class="'.$classes.'"'.($disabled?' disabled="disabled"':'').
        $data_str.'>'.$text.'</button>';
  }

  static function Img($src,$alt='',$class='responsive') {
    if(!in_array($class,self::$imageclasses)) 
      return self::Error('Illegal class for '.__METHOD__.'(): '.$class.
        ', expected one of '.implode(', ',self::$imageclasses));
    return '<img src="'.$src.'" alt="'.$alt.'"'.($class?' class="img-'.$class:'').'" />';
  }
  
  static function Caret() {
    return '<span class="caret"></span>';
  }
  
  static function Icon($name,$color=false) {
    if(strpos(self::$IconNames," $name ")===false)
      return self::Error('Invalid icon name: '.$name);
    return '<span class="glyphicon glyphicon-'.$name.'"'.($color?' style="color:'.$color.'"':'').'></span>';
  }

  static function Dropdown($btnId,$label,$content,$type='default',$rightAligned=false) { 
    if(!in_array($type,array_merge(array('default','primary'),self::$contexttypes)))
      return self::Error('Invalid type for '.__METHOD__.'(): '.$type);
    return '<div class="dropdown"'.($rightAligned?' align="right"':'').'>'.
           '<button type="button" class="btn btn-'.$type.' dropdown-toggle"'.
           ' id="'.$btnId.'" data-toggle="dropdown">'. # aria-expanded ? aria-haspopup="true" ?
           $label.' '.self::Caret().'</button>'.
           self::DropdownMenu($content,$btnId,$rightAligned).
           '</div>';
  }
  
  static function DropdownMenu($content,$labelledby=false,$rightAligned=false) {
    if(is_array($content)) $content = implode("\n",$content);
    return '<ul class="dropdown-menu'.($rightAligned ? ' dropdown-menu-right' : '').'" role="menu"'.
           ($labelledby?' aria-labelledby="'.$labelledby.'"':'').'>'.
           $content.'</ul>';
  }
  
  static function MenuItem($text,$href='#',$class='') {
    if($class && !in_array($class,array('active','disabled')))
      return self::Error('Invalid class for '.__METHOD__.'(): '.$class.' (active or disabled only)');
    return '<li'.($class?' class="'.$class.'"':'').' role="presentation">'.
           '<a href="'.$href.'" role="menuitem" tabindex="-1">'.$text.'</a></li>';
  }
  
  static function MenuHeader($text) {
    return '<li role="presentation" class="dropdown-header">'.$text.'</li>';
  }
  
  static function Divider() {
    return '<li role="presentation" class="divider"></li>';
  }
  
  // Tab related methods
  static function TabList($content,$pills=false) {
    if(is_array($content)) $content = implode("\n",$content);
    return '<ul class="nav nav-'.($pills?'pills':'tabs').'" role="tablist">'.$content.'</ul>';
  }
  
  static function TabLabel($text,$href='#',$active=false) {  # !! use MenuItem instead?
    return '<li'.($active?' class="active"':'').' role="presentation">'.
           '<a href="'.$href.'" role="tab" data-toggle="tab">'.$text.'</a></li>';
  }
  
  static function TabPanel($id,$content,$active=false) {
    if(is_array($content)) $content = implode("\n",$content);
    return '<div role="tabpanel" class="tab-pane'.
                  ($active ? ' active':'').'" id="'.$id.'">'.
                  $content.'</div>';
  }

  static function TabSet($content,$active=1,$id_prefix='tab',$pills=false) {
    $panels = array();
    $menu = array();
    $i = 1;
    foreach($content as $label=>$panel) {
      #if(is_array($panel)) $panel = implode("\n",$panel);
      $id = $id_prefix.$i;
      $href = '#'.$id;
      $menu[] = UI::TabLabel($label,$href,($i==$active));
      $panels[] = UI::TabPanel($id,$panel,($i==$active));
      $i++;
    }
    return '<div class="tabpanel">'.
           UI::TabList($menu,$pills).
           '<div class="tab-content">'.
             implode("\n",$panels).
           '</div>'.
           '</div>';
  }

  static function NavBar($content,$inverse=false,$fixed=false,$placement='top') { # !! work in progress
    if(is_array($content)) $content = implode("\n",$content);
    if(!in_array($placement,array('top','bottom')))
      return self::Error('Bad placement for '.__METHOD__.'(), expected "top" or "bottom", got "'.$placement.'"');
    return '<nav class="navbar navbar-'.($inverse?'inverse':'default').($fixed?' navbar-fixed-'.$placement:'').'">'.
           self::Container($content,true).'</nav>';
  }
  
  static function NavBarHeader($brand,$link='#',$DOMid='navbarmenu') {
    return '<div class="navbar-header">'.
           self::Button('<span class="sr-only">Toggle navigation</span>'.str_repeat(self::IconBar(),3),
                        false,'','',false,'navbar-toggle collapsed',
                        array('toggle'=>'collapse','target'=>'#'.$DOMid)).
           '<a class="navbar-brand" href="'.$link.'">'.$brand.'</a>'.           
           '</div>';
  }
  
  static function NavBarDropdown($label,$content,$right=false) {
    if(is_array($content)) $content = implode("\n",$content);
    return '<li class="dropdown">'.
           '<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">'.
             $label.' '.self::Caret().'</a>'.
           self::DropdownMenu($content).
           '</li>';
  }
  
  static function NavBarMenu($content,$right=false) {
    if(is_array($content)) $content = implode("\n",$content);
    return '<ul class="nav navbar-nav'.($right?' navbar-right':'').'">'.
           $content.'</ul>';
  }
  
  static function NavBarText($text,$right=false) {
    return '<p class="navbar-text'.($right?' navbar-right':'').'">'.$text.'</p>';
  }
  
  static function IconBar() {
    return '<span class="icon-bar"></span>';
  }
  
  static function Progress($val,$type='',$striped=false,$active=false) {  # !! work in progress
    # $val = integer (0-100)
    # $val = array of arrays
    # $val = assoc type => value  TODO
    if(is_array($val)) {
      $content = array();
      foreach($val as $idx=>$p) {
        if(!is_array($p)) $p = array($p);
        if(count($p)>1 && !in_array($p[1],self::$contexttypes))
          return self::Error('Invalid type for bar '.($idx+1).' in '.__METHOD__.'(): '.$p[0]);
        $content[] = call_user_func_array(array(__CLASS__,'ProgressBar'),$p);
      }
      $content = implode("\n",$content);
    } else $content = self::ProgressBar($val,$type,$striped,$active);
    return '<div class="progress">'.$content.'</div>';
  }
  
  static function ProgressBar($val,$type='',$striped=false,$active=false,$min=0,$max=false) { # TODO: $striped, $active (only if striped)
    if($type && !in_array($type,self::$contexttypes))
      return self::Error('Invalid type for '.__METHOD__.'(): '.$type);  
    if($active && !$striped)
      return self::Error(__METHOD__.'(): Only striped progressbar can be active');
    if($max) $aria=true; # !! work in progress
    else $aria=false;
    $pct = $val; # !! calculate if min/max != 0/100 
    return '<div class="progress-bar'.
           ($type?' progress-bar-'.$type:'').
           ($striped?' progress-bar-striped':'').
           ($active?' active':'').
           '" role="progressbar" style="width:'.$pct.'%;"'.
           ($aria?'aria-valuenow="'.$val.'" aria-valuemin="'.$min.'" aria-valuemax="'.$max.'"':'').
           '><span class="sr-only">'.$pct.'%</span>'.
           '</div>';
  }
  
  static function ListGroupUL($content,$style=false) {
    if(is_array($content)) $content = implode("\n",$content);
    return '<ul class="list-group"'.($style?' style="'.$style.'"':'').'>'.$content.'</ul>';
  }
  
  static function ListGroupLI($text,$type='',$active=false) {
    if($type && !in_array($type,self::$contexttypes))
      return self::Error('Invalid type for '.__METHOD__.'(): '.$type);
    return '<li class="list-group-item'.($type?' list-group-item-'.$type:'').($active?' active':'').'">'.$text.'</li>';
  }
  
  static function ListGroupDIV($content) {
    if(is_array($content)) $content = implode("\n",$content);
    return '<div class="list-group">'.$content.'</div>';
  }
  
  static function ListGroupItem($text,$active=false,$heading='',$hlevel=3) {
    if($heading) 
      $text = '<h'.$hlevel.' class="list-group-item-heading">'.$heading.'</h'.$hlevel.'>'.
              '<p class="list-group-item-text">'.$text.'</p>';
    return '<a href="#" class="list-group-item'.($active?' active':'').'">'.$text.'</a>';
  }
  
  static function Panel($title,$content,$type='default',$footer='') {
    if(!in_array($type,array_merge(array('default','primary') + self::$contexttypes)))
      return self::Error('Invalid type for '.__METHOD__.'(): '.$type);
    return '<div class="panel panel-'.$type.'">'.
           ($title?'<div class="panel-heading"><h3 class="panel-title">'.$title.'</h3></div>':'').
           '<div class="panel-body">'.$content.'</div>'.
           ($footer?'<div class="panel-footer">'.$footer.'</div>':'').
           '</div>';
  }
  
  static function Well($content) {
    if(is_array($content)) $content = implode("\n",$content);
    return '<div class="well">'.$content.'</div>';    
  }
  
  static function Breadcrumbs(array $items) {
    $last = array_pop($items);
    return '<ol class="breadcrumb">'.
             ($items?'<li>'.implode('</li><li>',$items).'</li>':'').
             '<li class="active">'.$last.'</li>'.
           '</ol>';
  }
  
  static function TooltipInitJS($selection='[data-toggle="tooltip"]') {
    return '$(\''.$selection.'\').tooltip();'."\n";
  }
  
  static function Tooltip($title,$content,$placement='top') {
    if(!in_array($placement,array('top','bottom','left','right')))
      return self::Error('Invalid placement for '.__METHOD__.'(): '.$placement);
    return '<span data-toggle="tooltip"'.
           ($placement!='top'?' data-placement="'.$placement.'"':'').
           ' title="'.htmlentities($title).'">'.$content.'</span>';
  }
  
  static function Modal($DOMid,$title,$content,$buttons) {
    if(is_array($content)) $content = implode("\n",$content);
    return 
      '<div class="modal fade" id="'.$DOMid.'" tabindex="-1" role="dialog" aria-labelledby="'.$DOMid.'Label" aria-hidden="true">'.
        '<div class="modal-dialog">'.
          '<div class="modal-content">'.
            '<div class="modal-header">'.
              '<button type="button" class="close" data-dismiss="modal">'.
                '<span aria-hidden="true">&times;</span><span class="sr-only">Close</span>'.
              '</button>'.
              '<h4 class="modal-title" id="'.$DOMid.'Label">'.$title.'</h4>'.
            '</div>'.
            '<div class="modal-body">'.$content.'</div>'.
            '<div class="modal-footer">'.$buttons.'</div>'.
          '</div>'.  # modal-content
        '</div>'. # modal-dialog
      '</div>';
  }

#  static function text() { # htmlentities(), encoding etc
#  }
  
  #static function Carousel() {
  #  return '';
  #}
  
  # TODO:
  # table table-striped/table-bordered/table-condensed(?)
  # scrollspy
  # carousel
  # affix
  # accordion/collapse
  # Popover
  # 
}
