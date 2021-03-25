<?php

include 'miniMVC.php';

define('APP_NAME','miniMVC');
define('APP_VERSION','0.1a');
define('APP_AUTHOR','Roger Baklund');
define('APP_PATH','/miniMVC/');
define('CONTROLLER_PATH','controllers/');

function error($msg) {
  return '<p style="color:red;"><b>Error:</b> '.nl2br(htmlentities($msg)).'</p>';
}

function example($str) { 
  return $str ? '<pre>'.preg_replace('@&lt;\?php<br />@','',highlight_string("<?php\n".$str,true)).'</pre>' : '';
} 


// Catch all requests and fire up controller:
Route::request(Route::DefaultPattern(APP_PATH),Route::DefaultRoute);

die('Configuration error, a route was not found');

?>