<?php

# rb20150807 v0.1
# rb20151219 v0.2  Accept _ in datatype regexp (select_multiple)
# rb27052017 v0.3  Some bug fixes

/** Formbuilder class

This is an abstract class, you can subclass it or use it statically.

*/
abstract class FormBuilder {

  static public $max_text_input_size = 20;
  static public $max_numeric_input_size = 6;
  static public $form_content_class = 'formcontent';
  static public $required_marker = '*';
  static public $HTML5 = true; # !! TODO
  
  // defaults which can be overridden for each field
  static public $defaults = array(
    'wrapper' => '<div>%s</div>',
    'optional'=>true,
    'enum_type'=>'dropdown', # or radio
    'id_prefix' => '',
    'label_after' => false,
    'label_suffix' => ':',
    'label_ucfirst' => true,
    'label_class' => '',
    'label_style' => '');
  

  /** Make a single input control.
  
  This method makes a single HTML input control, which can be an INPUT, a TEXTAREA or a SELECT.
  
  This method requires two parameters, $sqltype and $name. 

  What type of control to make depends on the $sqltype parameter which supports all valid MySQL
  data types, in addition to some HTML types:
  
  - hidden         A hidden input field
  - submit         A submit button
  - reset     TODO
  - image     TODO
  - checkbox       A checkbox control (value=1)
  - radio     TODO
  - select         A select dropdown element
  - multiple  TODO
  
  You can optionally provide the initial value of the control as the third parameter. By default
  input is optional, there are three ways to override this:
  
  - Modify class variable FormBuilder::$defaults['optional'], false means input is required by default
  - Use functions required() or optional() for the value parameter
  - Set attribute *optional
  
  The fourth parameter is the label to use for the control. Use false to prevent a label.
  NULL is the default, it means the field name is used as a label. 
  
  The fifth and last parameter is $attrs, it is a string or an associative array. These are
  attributes which are inserted into the input element. If it is an array it can also contain 
  special overrides for this input. Use the following keys, all prefixed with an asterix:
  
  - *id_prefix      Prepend the name with this string to make the DOM id parameter.
  - *label_after    Set to true to place the label after the input control
  - *label_suffix   Suffix for the label, typical a colon character
  - *label_ucfirst  Set to true to uppercase first character of the label
  - *label_class    Set a class attribute on the label, default ''
  - *label_style    Set a style attribute on the label, default ''
  - *wrapper        Use this pattern to wrap the input control, incuding the label element, %s is placeholder
  - *optional       True or false, this input is optional
  - *enum_type      'dropdown' or 'radio' 
  
  TODO: 
    - mandatory 
      - mandatory_mark = '<span class="FBRequiredInput">*</span>';
      - checkboxes can never be required (or can they?)
      - dropdown must have extra (empty/labeled) option for optional input
    - optional HTML5 support
      - date/time input controls
      - datalist
    - JS validation, numeric inputs should get class and/or change handlers
    - PHP validation helper
    - Bootstrap helper (set classes)
    - Prettify HTML output

    
  @param $sqltype string Type of input, MySQL field type
  @param $name string The name of the conrol
  @param $value The initial value for the control. Default value is ''
  @param $label The label to use for this control. Default value is NULL, which means the name is used
  @param $attrs string/array Extra HTML atributes for the input element and/or special overrides, see above
  */
  static function makeInput($sqltype,$name,$value='',$label=NULL,$attrs='') { 
    if(is_object($value)) { # !experimental
      $optional = $value->optional;
      $value = $value->value;
    } else $optional = NULL;
    if(!is_array($value)) $value = htmlentities($value);
    if(is_null($optional))
      $optional = self::$defaults['optional'];
    $enum_type = self::$defaults['enum_type'];
    $id_prefix = self::$defaults['id_prefix'];
    $label_after = self::$defaults['label_after'];
    $label_suffix = self::$defaults['label_suffix'];
    $label_ucfirst = self::$defaults['label_ucfirst'];
    $label_class = self::$defaults['label_class'];
    $label_style = self::$defaults['label_style'];    
    $wrapper = self::$defaults['wrapper'];
    $DOMid = $id_prefix.$name; // default, may be changed below
    $input_attrs = $attrs;
    if($attrs) {
      if(is_array($attrs)) {
        if(isset($attrs['*id_prefix'])) {
          $id_prefix = $attrs['*id_prefix'];
          unset($attrs['*id_prefix']);
          $DOMid = $id_prefix.$name;
        }        
        if(isset($attrs['id'])) { # if id is set *id_prefix is ignored
          $DOMid = $attrs['id'];
          unset($attrs['id']);
        } 
        if(isset($attrs['*label_after'])) {
          $label_after = $attrs['*label_after'];
          unset($attrs['*label_after']);
        }
        if(isset($attrs['*label_suffix'])) {
          $label_suffix = $attrs['*label_suffix'];
          unset($attrs['*label_suffix']);
        }
        if(isset($attrs['*label_ucfirst'])) {
          $label_ucfirst = $attrs['*label_ucfirst'];
          unset($attrs['*label_ucfirst']);
        }
        if(isset($attrs['*label_class'])) {
          $label_class = $attrs['*label_class'];
          unset($attrs['*label_class']);
        }
        if(isset($attrs['*label_style'])) {
          $label_style = $attrs['*label_style'];
          unset($attrs['*label_style']);
        }
        if(isset($attrs['*wrapper'])) {
          $wrapper = $attrs['*wrapper'];
          unset($attrs['*wrapper']);
        }
        if(isset($attrs['*optional'])) {
          $optional = $attrs['*optional'];
          unset($attrs['*optional']);
        }
        if(isset($attrs['*enum_type'])) {
          $enum_type = $attrs['*enum_type'];
          unset($attrs['*enum_type']);
        }        
        // transform to string
        $attr_pairs = array();
        foreach($attrs as $k=>$v) {
          $attr_pairs[]= $k.'="'.htmlentities($v).'"';
        }
        $attrs = $attr_pairs ? ' '.implode(' ',$attr_pairs) : '';
      }
    }
    if($wrapper && strpos($wrapper,'%s') === false)
      trigger_error('Wrapper must contain %s placeholder');    
    
    $decimals = false; # float/double/decimal
    
    if(is_null($label)) # false/empty string means no label
      $label = str_replace('_',' ',$label_ucfirst ? ucfirst(strtolower($name)) : $name); 
    
    $extra = ''; # unsigned zerofill
    if(strpos($sqltype,'(') !== false) {
      preg_match('/^([a-z_]+)\(([^\)]*)\)(.*)/',$sqltype,$m);
      $fieldtype = $m[1];
      $par = $m[2];
      if(in_array($fieldtype,array('decimal','float','double')) && strpos($par,',') !== false)
        list($par,$decimals) = explode(',',$par);
      $extra = $m[3];
      $unsigned = (strpos($extra,'unsigned') !== false) ? 1 : 0;
    } else {
      $fieldtype = $sqltype;
      if(strpos($fieldtype,' ') !== false) {
        list($fieldtype,$extra) = explode(' ',$fieldtype,2);
      }
      $unsigned = (strpos($extra,'unsigned') !== false) ? 1 : 0;
      if(in_array($fieldtype,array('float','double'))) 
        $par = 10;
      elseif(in_array($fieldtype,array('tinyint')))
        $par = 4-$unsigned;
      elseif(in_array($fieldtype,array('smallint'))) 
        $par = 6-$unsigned;
      elseif(in_array($fieldtype,array('mediumint'))) 
        $par = 9-$unsigned;
      elseif(in_array($fieldtype,array('int','integer')))
        $par = 11-$unsigned;
    }
    # disable label for hidden fields
    #if($fieldtype == 'hidden')
    #  $label = false; 
    switch($fieldtype) {
# TODO: image, file
# TODO: test reset, button
# TODO: number, range, url, tel, color, search (html5)
# TODO: week,month (html5)
      case 'password':  # ! Not SQL
      case 'hidden':    # ! Not SQL
      case 'submit':    # ! Not SQL
      case 'button':  # ! Not SQL
        if($fieldtype!='password') $label = false; 
        $input = '<input type="'.$fieldtype.'" name="'.$name.'" id="'.$DOMid.'" value="'.$value.'"'.$attrs.' />';
        break;
      case 'reset':    # ! Not SQL
        $label = false; 
        $input = '<input type="reset" name="'.$name.'" id="'.$DOMid.'" value="'.$value.'"'.$attrs.' />';
        break;
      case 'checkbox':  # ! Not SQL 
      case 'bit':        
        $input = '<input type="checkbox" name="'.$name.'" id="'.$DOMid.'" value="1"'.($value ? ' checked="checked"':'').$attrs.' />';
        break;
      case 'tinyint': case 'smallint': case 'mediumint': case 'int': case 'bigint':
      case 'decimal': case 'float': case 'double':
          $size = isset($input_attrs['size']) ? '' :
            ' size="'.(
              $par > self::$max_numeric_input_size ? 
              self::$max_numeric_input_size : $par).
            '"';
        $input = '<input'.$size.' maxlength="'.$par.'" type="text" name="'.$name.'" id="'.$DOMid.'" value="'.$value.'"'.$attrs.' />';
        break;
      case 'varchar': case 'char': case 'binary': case 'varbinary':
        $size = isset($input_attrs['size']) ? '' :
            ' size="'.(
              $par > self::$max_text_input_size ? 
              self::$max_text_input_size : $par).
            '"';
        $input = '<input'.$size.' maxlength="'.$par.'" type="text" name="'.$name.'" id="'.$DOMid.'" value="'.$value.'"'.$attrs.' />';
        break;
      case 'tinyblob': case 'blob': case 'mediumblob': case 'longblob':
      case 'tinytext': case 'text': case 'mediumtext': case 'longtext':
        $input = '<textarea name="'.$name.'" id="'.$DOMid.'"'.$attrs.'>'.$value.'</textarea>';
        break;
      case 'time':      # type="time" (html5)
      case 'date':      # type="date" (html5)
      case 'datetime':  # type="datetime" (html5)
      case 'timestamp': # type="datetime" (html5)
      case 'year':
        $input = '<input size="5" type="text" name="'.$name.'" id="'.$DOMid.'" value="'.$value.'"'.$attrs.' />';
        break;
      case 'radio': # ! Not SQL   TODO
      case 'select': # ! Not SQL
      case 'select_multiple': # ! Not SQL  TODO
      case 'enum':
      case 'set':  # datalist ? checkboxes/radiobuttons
        if($fieldtype == 'select_multiple') 
          $fieldtype = 'set';
        if($fieldtype == 'radio') 
          $enum_type = 'radio';
        $options = array_map(function($opt) use($value,$enum_type,$name) {
          static $no=1;
          $sel = false;
          if(is_array($value)) {
            if(in_array($no,$value)) 
              $sel = true;
          } elseif($value == $no)
            $sel = true;
          if($enum_type == 'radio')  # !! work in progress
            return '<input type="radio" name="'.$name.'" id="'.$name.'_'.$no.'" value="'.$no.'"'.
                   ($sel ? ' checked="checked"':'').' />'.
                   '<label for="'.$name.'_'.($no++).'">'.trim($opt,"'").'</label>';
          return '<option'.($sel ? ' selected="selected"':'').' value="'.($no++).'">'.trim($opt,"'").'</option>';
        },explode(",",$par));
        $options = implode("\n  ",$options);
        if($enum_type == 'radio')
          $input = $options;
        else
          $input = '<select name="'.$name.'" id="'.$DOMid.'"'.($fieldtype == 'set' ? ' multiple="multiple"':'').$attrs.'>'."\n  ".$options."\n</select>\n";
        break;
      default:
        $input = 'ERROR, type='.$fieldtype.' not supported';
    }    
    if($label) {
      $Lmarkup = '<label for="'.$DOMid.'"'.
        ($label_class?' class="'.$label_class.'"':'').
        ($label_style?' style="'.$label_style.'"':'').'>'.
        ($optional?'':self::$required_marker).$label.$label_suffix.'</label>';
      $input = $label_after ? $input.$Lmarkup : $Lmarkup.$input;
    }
    if($wrapper)
      $input = sprintf($wrapper,$input);
    return $input;
  }
  static function fillForm(&$form,$data,$filter=false) {
    foreach($form as $k => & $v) { 
      if($k[0] == '*') continue;
      if(isset($data[$k]))
        $v[1] = $filter ? $filter($data[$k]) : $data[$k];
      elseif($v[0] == 'checkbox')
        $v[1] = 0; # false and empty string also works
    }  
  }
  static function selectPart($name,$cfg,$sel=0,$displaytype='block',$DOMprefix=NULL) {  
    if($name) $DOMprefix = $name.'_part';
    elseif(is_null($DOMprefix))
      # guaranteed to make unique id if 
      # a) script finish within 100 seconds
      # b) PHP does not execute 1000000 times faster
      $DOMprefix = 'id'.str_replace('.','',substr(sprintf('%.10f',microtime(true)),9));
    $i = 0;
    $opts = array();
    $sections = array();
    foreach($cfg as $label => $section) {
      $opts[] = '<option value="'.$i.'"'.
        ($sel == $i ? ' seleced="selected"':'').'>'.
        $label.'</option>';
      $sections[] = '<div id="'.$DOMprefix.$i.'"'.($sel == $i ? '':' style="display:none"').'>'.$section.'</div>';
      $i++;
    }
    $script = "var sel=this.attributes['data-sel'];
      document.getElementById(sel.value).style.display='none';
      sel.value = '$DOMprefix'+this.options[this.selectedIndex].value;
      document.getElementById(sel.value).style.display='$displaytype';";
    $html = '<select'.($name?' name="'.$name.'"':'').' data-sel="'.$DOMprefix.$sel.'" onchange="'.$script.'">'.
      "\n".implode("\n  ",$opts)."\n".'</select>'."\n".
      implode("\n",$sections);
    return $html;
  }

  static function makeFormPart($fields) {
    $form = '';
    foreach($fields as $name=>$def) {
      if($name[0] == '*') {
        $form .= $def;
      } else {
        $sqltype = $def[0];
        $value = isset($def[1]) ? $def[1] : '';
        $label = isset($def[2]) ? $def[2] : NULL; # NULL means $name is used
        $attrs = isset($def[3]) ? $def[3] : '';
        $form .= self::makeInput($sqltype,$name,$value,$label,$attrs);
      }
    }
    return $form;
  }  
  /** Make a HTML form.
  
  This methods takes a specially formatted array of field definitions as input and returns a HTML form.
  $fields format:
      ['fieldname' => [$type,$value,$label,$label_after],
       '*meta1' => '<p>Markup, string</p>' ]
  */
  static function makeForm($fields,$action='#',$method="post",$form_attrs='') {
    $form = '<form method="'.$method.'" action="'.$action.'"'.($form_attrs?" $form_attrs":'').'>'.
            '<div class="'.self::$form_content_class.'">';
    $form.=self::makeFormPart($fields) ;
    $form .= '</div></form>';
    return $form;
  }
  /** Make CSS for a form
  
  */
  # TODO: $config (see SimpleMenu)
  static function makeCSS($config=false,$divclass=false) {
    if(!$divclass) $divclass = self::$form_content_class;
    $conf = array(
      'width' => '20em',
      'background' => 'silver',
      'padding' => '.5em'
    );
    if($config) # modify defaults
      foreach($config as $k=>$v)
        $conf[$k] = $v;
    $prefix = 'form div.'.$divclass.' ';
    $style = <<<EOD
  $prefix {width:{$conf['width']};background:{$conf['background']};padding:{$conf['padding']};}
  $prefix h2 {margin:0;padding:.1em;text-align:center;background:rgba(255,255,255,0.6);border:solid 1px black;}
  $prefix label:first-child {display:inline-block;width:8em;text-align:right;padding-right:.3em}
  $prefix input[type!=submit] {width:14em;}
  $prefix input[type=submit] {display:block;margin:.5em auto;}

EOD;
    return $style;
  }
  
  /** Returns a collection of fields.
  
  This method makes a collection of fields which can be used by the makeForm() method.
  */
  static function makeFieldCollection($fields) {
    $col = array();
    return $col;
  }

}

# For mandatory fields
function required($value) { return (object) array('value'=>$value,'optional'=>false); }
function optional($value) { return (object) array('value'=>$value,'optional'=>true); }

/*
# Fluent interface
class FluentFormBuilder extends FormBuilder {
  function __construct($fields,$action='',$method='post',$attrs='') {
    $this->fields = $fields;
    $this->action = $action;
    $this->method = $method;
    $this->attrs = $attrs;
  }
  function insertBefore($key,$fields) {
    return $this;
  }
  function insertAfter($key,$fields) {
    return $this;
  }
  function append($fields) {
    return $this;
  }
  function input($type,$name,$value,$label,$attrs) {
    return $this;
  }
  function meta($data) {
    return $this;
  }
  function change($key,$data) {
    return $this;
  }
  function __toString() {
    return $this->makeForm($this->fields,$this->action,$this->method,$this->attrs);
  }
}
*/