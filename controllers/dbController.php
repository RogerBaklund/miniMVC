<?php

include 'DefaultController.php';


/*
  KNOWN ISSUES:
  - column names with leading/trailing space fails because PHP trims $_POST keys
*/

/* TODO: prefix column names in forms

Stored procedures/functions:
select db,name,type,param_list,returns,body
from mysql.proc 

*/

/* Use this for compound keys in URL ?
function base64url_encode($data) { 
  return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); 
} 

function base64url_decode($data) { 
  return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT)); 
} 
*/
class dbController extends DefaultController {
  
  // This is a simple database management system
  
  static public $app_name = 'Database manager';
  static public $app_version = '0.1a';
  
  function __construct($path) {
    parent::__construct($path);
    // Set default View path:
    View::setDefaultPath('views/db/');
  }
  
  function _Default($params) {
    if($this->action) 
      #echo __CLASS__.': method '.$this->action.' is not defined';
      parent::_Default($params);
    else   // no action, show dashboard
      $this->dashboard();
  }
  
  function table($p=false) {
    global $db;
    $table_name = $this->params;
    $extra_style = '';
    $content = '';
    
    $content .= $this->dashboard_link();

    if(!$table_name)
      return $this->ShowPage($content.error('Bad URL, table name is missing')); 
      
    $connect_status = $this->connect($config = $this->loadConfig());
    if(!$config->isLoaded() || $db->connect_error) {
      $this->ShowPage($connect_status);
      return;
    }
    
    if(isset($_POST['add_field'])) {
      $content .= $this->table_add_field($db,$table_name);
      # tmp, hackish, tried to add row 
      if(isset($_GET['action']))
        unset($_GET['action']);
    }

    if(isset($_POST['copy_table'])) {
      $content .= $this->table_copy_table($db,$table_name);
      # tmp, hackish, tried to add row 
      if(isset($_GET['action']))
        unset($_GET['action']);
    }

    require_once 'DBModel.php';
    require_once 'SimpleInput.php';
    
    $table_model = new DBTableModel($db,$table_name);
    $chk = $table_model->check();
    if($chk != 'OK') { # show error & return, corrupt or non-existing table
      $this->ShowPage(error($chk));
      return;
    }
    
    # TODO: move to style view
    $table_form_style = '#table_form {width: 22em;background:silver;padding:.5em;border:1px solid black;}'.
        '#table_form h3 {margin:0;padding:0;text-align:center;}'.
        '#table_form form label {display:inline-block;width:10em;text-align:right;padding-right:.3em;}'.
        '#table_form form label:after {content:":";}'.
        '#table_form form textarea {width:100%;height:8em;}';

    #$extra_style .= $table_form_style;
    
    $table_model->getFields('fields');
    $table_model->getRowCount('rowcount');

    $primary_key = $table_model->getPrimaryKey();
    $table_model->caption = "`$config->dbname`.`$table_name` ".
      "<br/>Primary key: ".(is_array($primary_key) ? implode(',',$primary_key) : $primary_key);

    # !! what if table column is named "action" ?!
    #$action = isset($_POST['action']) ? $_POST['action']:'';
    #if(!$action)
    $action = isset($_GET['action']) ? $_GET['action']:'';
    if(!$action && $table_model->rowcount) $action = 'browse';
    
    if($action == 'delete')  // remove table, must be empty. Error or redirect
      $content .= $table_model->rowcount ? 
        error('Table has records, you are not allowed to delete it!') : 
        $this->table_delete($db,$table_name);
    
    if($action == 'edit') { // edit row (& delete)
      if(count($_POST))  // return error or delete msg or redirect
        $content .= $this->table_edit_row_POST($table_model); # $db,$table_name);
      else {
        $test = '<div id="table_form">';        
        $content .= ($res = $this->table_edit_row_GET($table_model)); #$db,$table_name));
        if(substr($res,0,strlen($test)) == $test)
          $extra_style .= $table_form_style;
      }
      if($table_model->rowcount)
        $action = 'browse';
    }
    
    if($action == 'add_row') {
    
      /// !! ISSUE: NOT NULL bit field with default 1 will be set to 1 if checkbox is unchecked
    
      if(count($_POST)) {  // POST request, save new row
        $content .= $this->table_add_row_POST($table_model); # $db,$table_name);
        if($db->insert_id)
          $content .= '<p>The new row got key <b>'.$db->insert_id.'</b></p>';
      }
      if($table_model->rowcount)
        $action = 'browse';
      $content .= '<div id="table_form">'.
        '<h3>Add row to '.$table_name.'</h3>'.
        $table_model->inputFields(array(
            'buttons'=>array(
              'insert'=>'<div style="padding:.5em 8em;">'.
                      '<input type="submit" style="font-size:120%" value="Insert row"/>'.
                      '</div>'))).
                   '</div>';
      $extra_style .= $table_form_style;
    
    }
    
    if($action == 'browse') {
      $extra_style .= $this->browseTable($table_name,$primary_key,$table_model,$config);
    }
    
    ############################
    
    $content .= new View('table_details',$table_model);
    $extra_style .= '#table_details table {margin:1em;border-collapse:collapse;border: solid 2px black;}'.
      '#table_details table caption { border: solid 2px black;border-bottom:none;}'.
      '#table_details table td {border-bottom: solid 1px black;border-left:1px dashed silver;s}'.
      '#table_details table caption {background:silver; font-weight:bold;}';
    
    $this->ShowPage($content,$extra_style);
  }  
  
  ### protected methods below

  protected function dashboard() {  # front page for database manager
  
    global $db;  // store mysqli connection in global space (for now)
    
    $extra_style = '';
    $content = '';
    
    $dashboard = new View('dashboard',$m = new Model());
    $m->app_name = self::$app_name;
    $m->app_version = self::$app_version;
      
    $config = $this->loadConfig();
    
    if(isset($_POST['configsave']))   # Either redirect or return error
      $content .= $this->saveConfig($config);
   
    $m->connect_status = $this->connect($config);
    $m->status_rows = ''; #'<tr><td>Unknown</td></tr>';
    
    $m->configform = new View('configform',$config);
    $extra_style .= '#configform {width:16em;background-color:silver;padding:.3em;} 
      #configform label {width:30%;font-size:80%;display:inline-block;} 
      #configform input[type=text] {width:60%;} 
      #configform form {margin:0;} ';

    if($config->isLoaded() && !$db->connect_error) {

      if(isset($_POST['add_table']))   # Either redirect or return error
        $content .= $this->add_table($db);

      require_once 'DBModel.php';
      require_once 'SimpleInput.php';

      $dbmodel = new DBModel($db);  
      $dbmodel->fetchTables('tables');
      $dbmodel->caption = "Tables in `$config->dbname`";

      $content .= new View('table_list',$dbmodel);
      
      $m->status_rows = $this->getServerStatus($db);
    }

    
    $m->content = $content;  // puts content into dashboard
    $content = $dashboard;   // ..and dashboard into page content

    $this->ShowPage($content,$extra_style);
    
  }

  protected function dashboard_link() {
    return '<div style="
      display:inline-block;
      padding:.3em;
      margin:.2em;
      background-color:#6f6;
      border:solid 1px black;
      border-radius:10%"><a style="text-decoration:none;color:black" href="'.APP_PATH.'db/">DB Dashboard</a></div>';
  }
  
  protected function table_copy_table($db,$table_name) {
    $target = isset($_POST['copy_table_input']) ? trim($_POST['copy_table_input']) : '';
    if(!$target) 
      return error('New table name must be provided');
    $res = $db->query("create table `$target` like `$table_name`");
    if($db->error) 
      return error($db->error);      
    return '<p style="color:green">A new table was created</p>'.
      '<p><a href="'.APP_PATH.$this->controller.'/table/'.urlencode($target).'">Go to</a> new table</p>';
  }
  
  protected function table_add_field($db,$table_name) {
    $field_def = isset($_POST['add_field_input']) ? trim($_POST['add_field_input']) : '';
    $tmp = explode(' ',$field_def);
    if(!$field_def) 
      return error('Field definition not provided');
    elseif(count($tmp) < 2) 
      return error('Invalid field definition, data type required');
    else {
      $res = $db->query("alter table `$table_name` add $field_def");
      if($db->error) 
        return error($db->error);      
      return '<p style="color:green">A new field was added</p>';
      #header('Location: '.APP_PATH.'db/table'.urlencode($table_name));
      #exit;     
    }
  }
  
  protected function table_delete($db,$table_name) {
    $res = $db->query("drop table `$table_name`");
    if($db->error)
      return error($db->error);
    header('Location: '.APP_PATH.'db/');  # msg?
    exit;
  }
    
  protected function browseTable($table_name,$primary_key,$table_model,$config) {
    $extra_style = '';
    # pagination
    $per_page = 20;
    $offset = isset($_GET['offset']) ? $_GET['offset'] : 0;
    $limit = "$offset,$per_page"; 
    $pages = ceil($table_model->rowcount / $per_page);
    $pagination_item = new FormatView('<a href="?action=browse&amp;offset=%d"%s>%d</a> ');
    $pagination = ($pages > 1) ? $pagination_item->loop(array_map(
      function($p) use($per_page,$offset) { 
        return array(($p-1)*$per_page,($offset==($p-1)*$per_page)?' class="current"':'',$p);
      },range(1,$pages))) : '';
    if($pagination)
      $extra_style .= 
        '.pagination a {style:inline-block;padding:2px;text-decoration:none;background:silver;color:black;}'.
        '.pagination a.current {background:black;color:white;}';
    # table rows
    $table_model->table_rows = new View('table_rows',array(
      'rows_caption'=>"Rows in `$config->dbname`.`$table_name`",
      'rows' => $table_model->getRows('','',$limit),
      'primary_key' => $primary_key,
      'pagination' => $pagination));
    $extra_style .= '#table_rows td {border-bottom:solid 1px silver;}';
    return $extra_style;
  }
  
  protected function loadConfig() {
    # this is just defaults, actual config is stored in json/config.json
    $default_config = new Model();
    $default_config->host = 'localhost';
    $default_config->user = 'root';
    $default_config->pass = '';
    $default_config->dbname = 'test';
    $default_config->installed = false;

    $config = new JSONModel('json/config.json',$default_config);
      #$config = new JSONModel($_SERVER['DOCUMENT_ROOT'].APP_PATH.'json/config.json',$default_config);
    return $config;
  }
  
  protected function saveConfig($config) {
    global $db;
    $data = $_POST;
    unset($data['configsave']);
    echo new View('connect',$data); // no output, just connects to DB
    if($db->connect_error)
      return '<p style="color:red">'.$db->connect_error.'</p>';
    else { // save config only if connect success
      $data['installed'] = true;
      $config->update($data); #['accept_new'=>false]
      $config->save();   # !! hm... why is this required? no constructor save?
      // redirect
      header('Location: '.APP_PATH.'db/?msg=confsaved');
      exit;
    }
  }
  
  protected function connect($config) {
    global $db;
    if($config->isLoaded()) {    
      echo new View('connect',$config); // no output, just connects to DB
      if($db->connect_error)
        $connect_status_msg = '<p style="color:red">'.$db->connect_error.'</p>';
      else
        $connect_status_msg = '<p style="color:green">Connected to database <b>'.$config->dbname.'</b> on <b>'.$config->host.'</b> with <b>'.$config->user.'</b> account</p>';
    } else {
      $connect_status_msg = '<p style="color:red">Configure database settings to access database manager</p>';
    }
    return $connect_status_msg;
  }
  
  protected function getServerStatus($db) {

    #require_once 'DBModel.php';
    
    $dbstatus = new DBStatusModel($db);      
    $dbstatus->fetchGlobalStatus(false,
      "Variable_name in ('Uptime','Bytes_received','Bytes_sent') or 
      (Variable_name like 'Com_%' and Value>0)");

    $rows = array_map(function ($k) use($dbstatus) { 
      $name = $k;
      if(substr($name,0,4) == 'Com_')
        $name = str_replace('_',' ',substr($name,4));
      $value = $dbstatus->$k;
      if($k == 'Uptime') {
        $unit = ' sec';
        if($value > 120) { $unit = ' min';   $value = round($value / 60); 
        if($value > 120) { $unit = ' hours'; $value = round($value / 60); 
        if($value > 48)  { $unit = ' days';  $value = round($value / 24); 
        if($value > 14)  { $unit = ' weeks'; $value = round($value / 7); }}}}
        $value .= $unit;
      } else {
        $unit = '';
        if($value > 2048) { $unit = 'K';   $value = round($value / 1024); 
        if($value > 1024) { $unit = 'M';   $value = round($value / 1024,1); 
        if($value > 1024) { $unit = 'G';   $value = round($value / 1024,1); }}}
        $value .= $unit;
      }
      if($value != $dbstatus->$k)
        $value = '<span title="'.$dbstatus->$k.'">'.$value.'</span>';
      return sprintf('<tr><td>%s</td><td>%s</td></tr>',$name,$value);},$dbstatus->names());
    return implode("\n",$rows);
  }
  
  protected function add_table($db) {
    $new_table_name = isset($_POST['add_table_input']) ? trim($_POST['add_table_input']) : '';
    if(!$new_table_name)
      return error('Table name must be provided');
    $key_name = isset($_POST['key_name']) ? trim($_POST['key_name']) : false;
    if(!$key_name)
      return error('Key name must be provided');
    $def = $_POST['data_type'];
    if(isset($_POST['unsigned']))
      $def .= ' unsigned';
    $def .= ' not null primary key';  
    if(isset($_POST['auto_increment']))
      $def .= ' auto_increment';
    $res = $db->query("create table `$new_table_name` (`$key_name` $def);");
    if($db->error)
      return error($db->error);
    header('Location: '.APP_PATH.'db/table/'.urlencode($new_table_name));
    exit;
  }

  protected function table_add_row_POST($table_model) { # $db,$table_name) {
    #$tab = new DBRecordModel($db,$table_name);
    $tab = $table_model->getRecordModel(false);
    Model::errormode(Model::ERROR_IGNORE);
    list($data,$errors) = $tab->validate($_POST);
    if($errors) {
      if(count($errors)>1)
        $msg = "\n".implode("\n",$errors);
      else
        $msg = $errors[0];
      return error($msg);
    } 
    $tab->update($data);
    if(!$tab->save()) 
      return error('Save failed: '.$tab->getError());
    $table_model->rowcount += 1;
    return '<p style="color:green">A new row was added</p>';
    #header('Location: '.$_SERVER['REQUEST_URI']);  # add more
    #exit;
      
  }
  
  protected function table_edit_row_GET($table_model) { #$db,$table_name) {
    $key = isset($_GET['key']) ? $_GET['key'] : false;
    if($key === false) 
      return error('Key is missing for edit');
      
    $table_name = $table_model->getTableName();
    Model::errormode(Model::ERROR_IGNORE);
    $keyName = $table_model->getPrimarykey();
    if(!$keyName)
      return error('Did not find key');
    if(is_array($keyName))
      return error('Compound primary key not supported');
    
    $record = $table_model->getRecordModel($key,$keyName);
    if(!$record->isLoaded()) 
      return error('Record with `$keyName`='.$key.' not found');

    $form_config = array(
      'fields'=>array(),
      'buttons'=>array(
        'update'=>'<div style="padding:.5em;width:80%;margin:0 auto;">'.
                  '<input type="submit" style="float:right;font-size:120%" value="Update"/> '.
                  '<input type="submit" style="font-size:120%" value="Delete" name="_do_delete" />'.
                  '</div>'));

    foreach($record->names() as $name) 
      $form_config['fields'][$name] = array('value'=>$record->$name);

    return '<div id="table_form">'. # !! this div start is tested upon above
      '<h3>Edit row in '.$table_name.'</h3>'.
      $record->inputFields($form_config).
      '</div>';
  }
  
  protected function table_edit_row_POST($table_model) { #$db,$table_name) {
    if(!isset($_GET['key'])) 
      return error('Missing required key for update');
    $key = $_GET['key'];
    
    $table_name = $table_model->getTableName();
    Model::errormode(Model::ERROR_IGNORE);
    $keyName = $table_model->getPrimarykey();
    $tab = $table_model->getRecordModel($key,$keyName); 

    if(!$tab->isLoaded()) 
      return error('Did not find row with key '.$key);

    if(isset($_POST['_do_delete'])) { # !! what if field is named _do_delete !!
      if(!$tab->delete()) {
        return error('Delete failed for row with key '.$key);
      } else {
        $table_model->rowcount -= 1;
        return '<p style="color:green">Deleted row with key '.htmlentities($key).'</p>';
        #header('Location: '.APP_PATH.'db/table/'.urlencode($table_name).'?action=browse');
        #exit;
      }
    }

    list($data,$errors) = $tab->validate($_POST);
    if($errors) {
      if(count($errors)>1)
        $msg = "\n".implode("\n",$errors);
      else
        $msg = $errors[0];
      return error($msg);
    }

    # Non-existing fields will result in illegal SQL
    $settings = array('accept_new'=>false,'return_changes'=>true);
    $chg = $tab->update($data,$settings); 

    if(!$chg) 
      return error('No changes');
      
    // $chg contains values before change, could be used for logging

    // remove unchanged fields:
    foreach($tab->names() as $name)
      if(!in_array($name,array_keys($chg)))
        unset($tab->$name);

    if(!$tab->save()) 
      return error($tab->getError());  // !tmp
    # !! TODO: success message
    #header('Location: '.$_SERVER['REQUEST_URI']);
    header('Location: '.APP_PATH.'db/table/'.urlencode($table_name).'?action=browse');
    exit;

  }
  
}