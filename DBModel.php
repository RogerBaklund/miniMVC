<?php

require_once 'miniMVC.php';

class DBModel extends Model {
  protected $_db;
  
  function __construct($db,$data=false) {
    if(!is_object($db))
      return self::error('You must provide a database instance');
    if(!method_exists($db,'query'))
      return self::error('The database instance must have a query method');
    if(!method_exists($db,'escape_string'))
      return self::error('The database instance must have an escape_string method');
    parent::__construct($data);
    $this->_db = $db;
  }
  
  // work in progress
  function getError() { return $this->_db->error; }
  function query($sql) { return $this->_db->query($sql); }
  function prepare($sql) { return $this->_db->prepare($sql); }
  
  function getTables($verbose=true) {
    $res = $this->_db->query('show table status');
    $tables = array();
    while($row = $res->fetch_assoc()) {
      $tables[] = $verbose ? $row : $row['Name'];
    }
    return $tables;
  }
  
  function getTableNames() {
    return $this->getTables(false);
  }
  
  function fetchTables($target) {
    $this->_data[$target] = $this->getTables();
  }
  
  function fetchTableNames($target) {
    $this->_data[$target] = $this->getTableNames();
  }
}

class DBStatusModel extends DBModel {
  private function fetchServerInfo($type,$pattern=false,$where=false) {
    # Note: only for queries which returns columns Variable_name and Value
    # SHOW [GLOBAL] VARIABLES [ LIKE ...| WHERE ...]
    # SHOW [GLOBAL] STATUS [ LIKE ...| WHERE ...]
    if($pattern && $where) { // !! only one of them is allowed
      $where = "Varible_name like '$pattern' and ".$where;
      $pattern = false;
    }
    $res = $this->_db->query('show '.$type.
      ($pattern?' like "'.$pattern.'"':'').
      ($where?' where '.$where:''));
    $this->_data = array(); // reset internal data
    if(!$res) return $this->error($this->_db->error);
    while($row = $res->fetch_assoc()) {
      $this->_data[ $row['Variable_name'] ] = $row['Value'];
    }
    return true;
  }
  function fetchGlobalStatus($pattern=false,$where=false) {
    return $this->fetchServerInfo('global status',$pattern,$where);
  }
  function fetchSessionStatus($pattern=false,$where=false) {
    return $this->fetchServerInfo('status',$pattern,$where);
  }
  function fetchGlobalVariables($pattern=false,$where=false) {
    return $this->fetchServerInfo('global variables',$pattern,$where);
  }
  function fetchSessionVariables($pattern=false,$where=false) {
    return $this->fetchServerInfo('variables',$pattern,$where);
  }
}

class DBTableModel extends DBModel {
  protected $_table;
  protected $_primary_key;
  function __construct($db,$table,$data=false) {
    parent::__construct($db,$data) ;
    $this->_table = $table;
    $this->_primary_key = NULL; // unknown
  }
  function getTableName() { return $this->_table; }
  
  function check() {
    $res = $this->_db->query("check table `{$this->_table}`");
    $row = $res->fetch_assoc();
    if($row['Msg_type'] == 'status' && $row['Msg_text'] == 'OK')
      return 'OK';
    if($row['Msg_type'] == 'Error')
      return $row['Msg_text'];
    # TODO: Msg_type warning, info, note
  }
  private function parsePrimaryKey($create_statement) {
    preg_match('/PRIMARY KEY \(([^)]+)/',$create_statement,$m);
    if(!isset($m[1])) return false;
    $tmp = array_map(function($p) { return trim($p,'`');},explode(',',$m[1]));
    if(count($tmp) == 1) return $tmp[0];
    else return $tmp;
  }
  function getCreateStatement() {
    $res = $this->_db->query("show create table `{$this->_table}`");
    if(!$res || $this->_db->error) return $this->error('Table '.$this->_table.' was not found');
    $row = $res->fetch_assoc();
    if(isset($row['Create Table'])) {
      $def = $row['Create Table'];
      $this->_primary_key = $this->parsePrimaryKey($def);
    }
    elseif(isset($row['Create View']))
      $def = $row['Create View'];
    return $def;
  }
  function getFields($target=false) { # !! fetchFields() ?
    # TODO: Store min/max values, +Max_len ?
    # return as assoc ?
    $numeric_types = array('bit','tinyint','smallint','int','mediumint','bigint',
        'float','double','decimal');
    $decimal_types = array('float','double','decimal');
    $fields = array();
    $res = $this->_db->query("desc `{$this->_table}`");
    if(!$res->num_rows) return $this->error('Table '.$this->_table.' was not found');
    while($row = $res->fetch_assoc()) {
      preg_match('/^([^( ]+)/',$row['Type'],$m);
      $row['Base_type'] = $m[1];
      $row['Numeric'] = in_array($m[1],$numeric_types) ? 'YES' : 'NO';
      $row['Decimal'] = in_array($m[1],$decimal_types) ? 'YES' : 'NO';
      $fields[] = $row;
    }
    if($target)
      $this->_data[$target] = $fields;
    return $fields;
  }
  function getPrimaryKey() {
    if(is_null($this->_primary_key)) {
      $this->getCreateStatement();
    }
    return $this->_primary_key;
  }
  function getRowCount($target=false,$where=false) {
    return $this->getAggregate("count(*)",$where,$target);
  }
  /* This is for total aggregates, not group by queries.
     It returns a single number
     !! make this private ?.
  */
  function getAggregate($aggr,$where=false,$target=false) {
    $sql = "select $aggr as aggr from `{$this->_table}`".($where?" where $where":'');
    $res = $this->_db->query($sql);
    if(!$res) return $this->error($this->getError());
    $row = $res->fetch_assoc();
    if($target)
      $this->_data[$target] = $row['aggr'];
    return $row['aggr'];
  }
  /* Returns number of rows without null for a specified field */
  function getCount($field='*',$where=false,$target=false) {
    if($field != '*') {
      if(strtolower(substr($field,9))=='distinct ')
        $field = "distinct `".substr($field,9)."`";
      else 
        $field = "`$field`";
    }
    return $this->getAggregate("count($field)",$where,$target);
  }
  function getMin($field,$where=false,$target=false) {
    return $this->getAggregate("min(`$field`)",$where,$target);
  }
  function getMax($field,$where=false,$target=false) {
    return $this->getAggregate("max(`$field`)",$where,$target);
  }
  function getSum($field,$where=false,$target=false) {
    return $this->getAggregate("sum(`$field`)",$where,$target);
  }
  function getAvg($field,$where=false,$target=false) {
    return $this->getAggregate("avg(`$field`)",$where,$target);
  }
  /* build a query, supporting joins, return query as string */
  function buildQuery($fields='*',$tables='',$where='',$order='',$limit='100') {
    if(is_array($fields)) {
      $field_spec = array();
      foreach($fields as $k=>$v) {
        if(is_numeric($k)) $field_spec[] = $v;
        else $field_spec[] = "$v AS '$k'";
      }
      $fields = implode(',',$field_spec);
    }
    if(!$tables) $tables = "`{$this->_table}`";
    elseif(is_array($tables)) {
      foreach($tables as $k=>$v) {
        if(is_numeric($k)) $table_spec[] = $v;
        else $table_spec[] = "$v AS $k";
      }
      $tables = implode(' ',$table_spec);
    }
    return "select $fields from $tables".
      ($where?" where $where":'').
      ($order?" order by $order":'').
      ($limit?" limit $limit":'');
  }
  /* Fetching data, supporting joins */
  function getData($fields='*',$tables='',$where='',$order='',$limit='100') {
    $sql = $this->buildQuery($fields,$tables,$where,$order,$limit);
    $res = $this->_db->query($sql);
    if(!$res) return $this->error($this->getError());
    $rows = array();
    while($row = $res->fetch_assoc()) 
      $rows[]=$row;
    return $rows;
  }
  /* Fetching entire rows */
  function getRows($where='',$order='',$limit='100') {
    return $this->getData('*','',$where,$order,$limit);
  }
  /* Fetch a single value 
     $field Either a column name as a string or an associative array with 
            the name as key and an expression as value.
     $where Required, condition as a string. 
            If multiple rows are found only first value is returned.
  */
  function getValue($field,$where,$order='') {
    $row = $this->getData($field,'',$where,$order,1);
    if(is_array($field))
      $fieldname = key($field);
    else $fieldname = $field;
    return $row[$fieldname];
  }
  function getRecordModel($keyVal,$keyCol='id') {
    return new DBRecordModel($this->_db,$this->_table,$keyVal,$keyCol);
  }
  /* form builder  TODO: move to separate class */  
  # get HTML input based on MySQL field type
  static function fieldInput($sqltype,$name,$value='',$label=NULL,$label_after=false,$id_prefix='') {
    $value = htmlentities($value);
    $id = $id_prefix.$name;
    $max_text_input_size = 20;
    $decimals = false; # float/double/decimal
    if(is_null($label)) $label = $name; # false/empty space means no label
    $label_before = ($label && !$label_after) ? true : false;
    $label_after = ($label && !$label_before) ? true : false; 
    $extra = ''; # unsigned zerofill
    if(strpos($sqltype,'(') !== false) {
      preg_match('/^([a-z]+)\(([^\)]*)\)(.*)/',$sqltype,$m);
      $fieldtype = $m[1];
      $par = $m[2];
      if(in_array($fieldtype,array('decimal','float','double')) && strpos($par,',') !== false)
        list($par,$decimals) = explode(',',$par);
      $extra = $m[3];
    } else {
      $fieldtype = $sqltype;
      if(strpos($fieldtype,' ') !== false) {
        list($fieldtype,$extra) = explode(' ',$fieldtype,2);
        $par = 6;
      }
      if(in_array($fieldtype,array('float','double'))) 
        $par = 10;
    }
    switch($fieldtype) {
      case 'bit':  # checkbox ?
      case 'tinyint':
      case 'smallint':
      case 'mediumint':
      case 'int':
      case 'bigint':
      case 'decimal':
      case 'float':
      case 'double':
        $size = $par > 6 ? 6 : $par;
        $input = '<input size="'.$size.'" maxlength="'.$par.'" type="text" name="'.$name.'" id="'.$id.'" value="'.$value.'"/>';
        break;
      case 'varchar':
      case 'char':
      case 'binary':
      case 'varbinary':
        $size = $par > $max_text_input_size ? $max_text_input_size : $par;
        $input = '<input size="'.$size.'" maxlength="'.$par.'" type="text" name="'.$name.'" id="'.$id.'" value="'.$value.'"/>';
        break;
      case 'tinyblob':
      case 'blob':
      case 'mediumblob':
      case 'longblob':
      case 'tinytext':
      case 'text':
      case 'mediumtext':
      case 'longtext':
        $input = '<textarea name="'.$name.'" id="'.$id.'">'.$value.'</textarea>';
        break;
      case 'time':      # type="time" (html5)
      case 'date':      # type="date" (html5)
      case 'datetime':  # type="datetime" (html5)
      case 'timestamp': # type="datetime" (html5)
      case 'year':
        $input = '<input size="5" type="text" name="'.$name.'" id="'.$id.'" value="'.$value.'" />';
        break;
      case 'enum':
      case 'set':  # datalist ?
        $options = array_map(function($opt) use($value) {
          static $no=1;
          $sel = false;
          if(is_array($value)) {
            if(in_array($no,$selected)) 
              $sel = true;
          } elseif($value == $no || $value == trim($opt,"'"))
            $sel = true;
          return '<option'.($sel ? ' selected="selected"':'').' value="'.($no++).'">'.trim($opt,"'").'</option>';
        },explode(",",$par));
        $options = implode("\n",$options);
        $input = '<select name="'.$name.'" id="'.$id.'"'.($fieldtype == 'set' ? ' multiple="multiple"':'').'>'.$options.'</select>';
        break;
      default:
        $input = 'ERROR, type='.$fieldtype;    
    }
    $Lmarkup = '<label for="'.$id.'">'.$label.'</label>';
    return ($label_before ? $Lmarkup : '').$input.($label_after ? $Lmarkup : '');
  }
  static function buildForm($fields,$action='',$method="post") {
    /* $fields format:
      ['fieldname' => [$type,$value,$label,$label_after],
       '*meta1' => '<p>Markup, string</p>' ]
    */
    $form = '<form method="'.$method.'" action="'.$action.'"><div class="formcontent">';
    foreach($fields as $key=>$def) {
      if($key[0] == '*') {
        $form .= $def;
      } else {
        $name = $key;
        $sqltype = $def[0];
        $value = isset($def[1]) ? $def[1] : '';
        $label = isset($def[2]) ? $def[2] : NULL; # NULL means $name is used
        $label_after = isset($def[3]) ? $def[3] : false;
        $form .= self::fieldInput($sqltype,$name,$value,$label,$label_after);
      }
    }
    $form .= '</div></form>';
    return $form;
  }
  function inputFields($config=false) {
    /* $config format:
      ['fields' => [
          'fieldname1' => [
             'value' => '',
             'label' => '',
             'label_after' => true,
             'before' => '',
             'after' => ''] ,
          'fieldname2' => [] ],
       'buttons' => ['btnname' => 'Button markup'] ]
    */
    $fields = array();
    if(!$config) $config = array();
    foreach($this->getFields() as $fld) {
      $sqltype = self::fieldInput($fld['Type'],$fld['Field']);
      $cfg = array();
      if(isset($config['fields']) && isset($config['fields'][ $fld['Field'] ])) 
        $cfg = $config['fields'][ $fld['Field'] ];
      $params = array($fld['Type']);
      if(isset($cfg['value'])) $params[] = $cfg['value'];
      if(isset($cfg['label'])) $params[] = $cfg['label'];
      if(isset($cfg['label_after'])) $params[] = $cfg['label_after'];
      if(isset($cfg['before']))  
        $fields[ '*'.$fld['Field'].'_before' ] = $cfg['before'];
      $fields[ $fld['Field'] ] = $params;
      if(isset($cfg['after']))  
        $fields[ '*'.$fld['Field'].'_after' ] = $cfg['after'];
    }
    if(isset($config['buttons'])) {
      foreach($config['buttons'] as $btn_name => $btn) {
        $fields['*_'.$btn_name] = $btn;
      }
    } else $fields['*_submit'] = '<input type="submit"  />';
    return self::buildForm($fields);
  }
}

class DBRecordModel extends DBTableModel {
  protected $_keyCol;
  protected $_keyVal;

  # !! supports only single column numeric primary key
  function __construct($db,$table,$keyVal=false,$keyCol='id',$data=false) {
    $this->_keyCol = $keyCol;
    $this->_keyVal = $keyVal;
    parent::__construct($db,$table,$data);
    if($keyVal) {
      $this->$keyCol = $keyVal;
      $this->load();
    }
  }

  function setKey($id) {
    $this->_keyVal = $id;
  }

  # Validation/NULL handling
  function validate($data,$definitions=false) {
    if(!$definitions)
      $definitions = $this->getFields();
    $errors = array();
    foreach($definitions as $fld_def) {
      $fld_name = $fld_def['Field'];
      if(!isset($data[$fld_name])) 
        continue;
      if($fld_def['Numeric'] == 'YES') {
        $data[$fld_name] = trim($data[$fld_name]); // remove spaces
        if($data[$fld_name] === '') {
          if($fld_def['Null'] == 'YES')
            $data[$fld_name] = NULL; // silent change '' => NULL
          elseif($fld_def['Extra'] == 'auto_increment')
            unset($data[$fld_name]); // silent change: remove empty auto_increment !! Only for insert, not update!?
          else
            $errors[] = $fld_name.' can not be NULL';
        } else {
          if(!is_numeric($data[$fld_name]))
            $errors[] = $fld_name.' must be numeric, got "'.$data[$fld_name].'"';
          elseif(strpos($data[$fld_name],'.') !== false) {
            if($fld_def['Decimal'] == 'NO')
              $errors[] = $fld_name.' must be integer, got "'.$data[$fld_name].'"';
          }
        }
      } # else string
    }
    return array($data,$errors);
  }
  
  function insert() {
    # !! auto_increment
    $auto_increment = false;
    if(!isset($this->_data[$this->_keyCol]))
      $auto_increment = true;
      #return self::error('A key must be provided, property '.$this->_keyCol.' is missing');
    #$this->setKey($this->_data[$this->_keyCol]); # !! setting below
    $data = $this->assign($this->_data);
    /*
    $data = $this->_data;   
    $cols = array_map(function($col) {
      return "`$col`";
    },array_keys($data));
    $cols = implode(',',$cols);
    $db = & $this->_db;
    $values = array_map(function($d) use ($db) {
      return is_numeric($d) ? $d:"'".$db->escape_string($d)."'";
    },array_values($this->_data));
    $values = implode(',',$values);
    $sql = "INSERT INTO `{$this->_table}` ($cols) VALUES ($values)";        
    */
    $sql = "INSERT INTO `{$this->_table}` SET $data";
    #echo "Insert: $sql\n"; //  !! tmp
    #die();
    $res = $this->_db->query($sql);
    if(!$res) return $this->error('Insert error: '.$this->_db->error);    
    if($auto_increment) 
      $this->_data[$this->_keyCol] = $this->_db->insert_id;
    $this->_keyVal = $this->_data[$this->_keyCol];  
    $this->_dirty = false;
    return true;
  }
  function load() {
    if(!isset($this->_data[$this->_keyCol])) 
      return self::error('Key property '.$this->_keyCol.' is missing');
    $id = $this->_data[$this->_keyCol];
    if(!is_numeric($id))
      $id = "'".$this->_db->escape_string($id)."'";
    $sql = "SELECT * FROM `{$this->_table}` WHERE `{$this->_keyCol}` = $id";
    #echo '['.$sql.']';
    $res = $this->_db->query($sql);
    if(!$res) { # SQL error, corrupt table?
      $this->cancel();
      return $this->error($this->_db->error);
    } elseif(!is_object($res)) {
      $this->cancel();
      return $this->error("Incompatible database driver, query() method did not return an object");
    } elseif(!isset($res->num_rows)) {
      $this->cancel();
      return $this->error("Incompatible database driver, object returned by query() method has no num_rows property");
    } elseif($res->num_rows > 1) {
      $this->cancel();
      return $this->error("{$this->_table} has {$res->num_rows} rows with {$this->_keyCol}=$id ");      
    } elseif($res->num_rows == 0) {
      $this->cancel();
      return $this->error("{$this->_table} with {$this->_keyCol}=$id was not found");
    }
    if(!method_exists($res,'fetch_assoc')) {
      $this->cancel();
      return $this->error('Incompatible database driver, object returned by query() method has no fetch_assoc() method');
    }
    $row = $res->fetch_assoc();
    $this->update($row);
    $this->_dirty = false;
    $this->_loaded = true;
  }
  private function assign($data,$sep=', ') {
    $db = & $this->_db;
    return implode($sep,array_map(function ($key) use($db,$data) {
      if(is_null($data[$key])) return "`$key`=NULL";
      return "`$key`=".(is_numeric($data[$key]) ? $data[$key] : "'".$db->escape_string($data[$key])."'");
    },array_keys($data)));
  }
  function delete() {  # !! TODO: support for soft delete
    $sql = "DELETE FROM `{$this->_table}` WHERE `{$this->_keyCol}` = {$this->_keyVal}";
    $res = $this->_db->query($sql);
    if(!$res || $this->_db->error) return $this->error('Could not delete: '.$this->_db->error);
    $this->_data = array();
    $this->_dirty = false;
    $this->_loaded = false;
    return true;
  }
  function save() {
    if(!$this->_keyVal)  
      return $this->insert();
    $data = $this->_data;
    $id_change = false;
    if(isset($data[$this->_keyCol])) {
      if($data[$this->_keyCol] != $this->_keyVal) 
        $id_change = true; // id is changed
      else
        unset($data[$this->_keyCol]);
    }
    $assign = $this->assign($data);
    $keys = array($this->_keyCol => $this->_keyVal); # tmp
    $cond = $this->assign($keys);
    $sql = "UPDATE `{$this->_table}` SET $assign WHERE `{$this->_keyCol}` = {$this->_keyVal}";    
    $res = $this->_db->query($sql);
    if(!$res) return $this->error('Error saving changes: '.$this->_db->error);
    if($id_change)
      $this->_keyVal = $data[$this->_keyCol];
    $this->_dirty = false;
    return true;
  }
}
