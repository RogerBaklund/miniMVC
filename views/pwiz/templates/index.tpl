
### File generated <?=$timestamp?> by <?=$appname?> v<?=$appversion?> ###

include 'miniMVC.php';
<?=$index_includes?>

define('APP_NAME','<?=$project_name?>');
define('APP_PATH','/<?=$short_name?>/');
define('APP_AUTHOR','<?=$author?>');

<?php if($controllers == 1) { ?>define('CONTROLLER_PATH','controllers/');

// You can put custom routes here

// Catch all requests and run controller:
Route::request(Route::DefaultPattern(APP_PATH),Route::DefaultRoute);

die('Configuration error, a route was not found');
<?php } else { ?>
// Put your routing rules or write your application here

    $content = <?php if($views > 1) { ?>new View('frontpage')<?php } else { ?>''<?php } ?>;
    $extra_style = '';
<?=$menu_code?>

<?=$view_code?>

<?php } ?>

