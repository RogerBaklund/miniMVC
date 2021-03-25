<?php

$bootstrap = function() {
  return 2+2;
};

// set $noclass to true if this file does NOT contain a class definition
#$noclass = true;

class TestController extends Controller {

  function _Default($p) {
    echo '<p>This is '.__CLASS__.', 2+2='.$p.'</p>';
    if($this->path[2])
      echo '<p style="color:red">Method '.$this->path[2].' not found</p>';
  }
  function x($p) {
    echo '<p>This is x() in the '.__CLASS__.' class, 2+2='.$p.'</p>';
  }
}
