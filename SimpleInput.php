<?php

class SimpleInput {

  static function ask($name,$label=NULL,$extra='') {
    if(is_null($label)) $label = ucfirst(strtolower(str_replace('_',' ',$name)));
    $html = <<<EOD
    <button id="{$name}_btn" onclick="
      var f = document.getElementById('{$name}_form');
      f.style.display='block';
      document.getElementById('{$name}_input').select();
      return false;">{$label}</button>
    <div id="{$name}_form" style="display:none;position:absolute;padding:.5em;border:solid 1px black;background:silver;">
    <form method="post">
      <div>
        $extra
        <input type="text" name="{$name}_input" id="{$name}_input" value="" />
        <input type="submit" name="{$name}" value="{$label}" />
        <button onclick="
          document.getElementById('{$name}_btn').style.display='inline';
          this.parentNode.parentNode.parentNode.style.display='none';
          return false;">Cancel</button>
      </div>
    </form>
    </div>
EOD;
    return $html;
  }
  
  # !! TODO: test
  static function selectPage($name,$label,$options,$extra='') {
    if($extra) $extra = " $extra";
    $opts = array(0=>"<option>$label</option>");
    foreach($options as $k=>$v) {
      $opts[] = '<option value="'.($k?htmlentities($k):'').'">'.htmlentities($v).'</option>';
    }
    return '<select onchange="'.
      "location.href='?$name='+this.value;".'"$extra>'.implode("\n  ",$opts).'</select>';
    
  }
  
  // ! copied to FormBuilder, slightly different
  static function selectPart($cfg,$sel=0,$displaytype='block',$DOMprefix=NULL) {  
    if(is_null($DOMprefix))
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
    $html = '<select data-sel="'.$DOMprefix.$sel.'" onchange="'.$script.'">'.
      "\n".implode("\n  ",$opts)."\n".'</select>'."\n".
      implode("\n",$sections);
    return $html;
  }

}
