<?php if(isset($simplemenu)) { ?>
    require_once 'SimpleMenu.php';

    $mm = new SimpleMenu(APP_PATH);

    $extra_style .= $mm->CSS(); // conf. possible, using defaults

    $main_menu = $mm->menu(array(
      $mm->item('Home',''),
      $mm->item('Dummy','dummy'),
    ));
<?php } else { ?>
    $main_menu = '(menu here)';
<?php } ?>
