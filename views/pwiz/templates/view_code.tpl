<?php if($views == 3) { ?>
    $page_view = new View('page_template','views/');
    $head_view = new View('head_template','views/');
    $body_view = new View('body_template','views/');

    $body = new Model();
    $body->top = '<h1>'.APP_NAME.'</h1>';
    $body->menu = $main_menu;
    $body->content = $content;
    $body->footer = '&copy; <?=date('Y')?> '.APP_AUTHOR;

    $page = new Model();
    $page->title = APP_NAME;
    $page->head = $head_view->render(array('extra_style'=>$extra_style));
    $page->body = $body_view->render($body);
<?php } elseif($views == 2) { ?>
    $page_view = new View('page_template','views/');
    $page = new Model();
    $page->title = APP_NAME;
    $page->head = '<style>'.$extra_style.'</style>';
    $page->body = $main_menu.$content;    
<?php } ?>
    
<?php if(in_array($views,array(2,3))) { ?>
    $page->external_files = '<?=$external_files?>'; 
    echo $page_view->render($page);
<?php } else { ?>
    $head = <<<EOD
<head><title><?=$project_name?></title>
<style type="text/css">
$extra_style
</style>
<?=$external_files?>
</head>
EOD;

    $content .= <<<EOD
<?=$frontpage_content?>
EOD;

echo '<html>'.$head.'<body>'.
  '<h1>'.APP_NAME.'</h1>'.
  '<div>'.$main_menu.'</div>'.
  '<div>'.$content.'</div>'.
  '</body></html>';

<?php } ?>
