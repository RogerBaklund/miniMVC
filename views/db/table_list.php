<div class="table_list">
  <table>
  <caption><?=$caption?></caption>
  <tr><?php if(!$tables) { ?>
    <th>No tables to show</th>
    <?php } else {
    $fix_db_table_row = function ($table) {
      # remove
      unset($table['Version']);
      unset($table['Max_data_length']);
      unset($table['Index_length']);
      unset($table['Data_free']);
      unset($table['Collation']);
      unset($table['Checksum']);
      unset($table['Create_options']);
      unset($table['Comment']);
      unset($table['Update_time']);
      unset($table['Check_time']);
      unset($table['Comment']);
      # shorten datetime columns
      $fixdate = function ($d) {
        if(substr($d,0,10) == date('Y-m-d')) // same day
          return substr($d,0,11); // just time
        if(substr($d,0,4) == date('Y')) // same year
          return substr($d,5,11); // date H:M
        return substr($d,0,10); // just date
      };
      $table['Create_time'] = $fixdate($table['Create_time']);
      #$table['Update_time'] = $fixdate($table['Update_time']);
      #$table['Check_time'] = $fixdate($table['Check_time']);
      # set link on name
      $table['Name'] = '<a href="'.APP_PATH.'db/table/'.urlencode($table['Name']).'">'.htmlentities($table['Name']).'</a>';
      # red zero Rows
      $table['Rows'] = $table['Rows'] == 0 ? '<span style="color:red">0</span>': $table['Rows'];
      return $table;
    };
    $table = $fix_db_table_row($tables[0]);
    $names = array_keys($table);
    $names[array_search('Avg_row_length',$names)] = 'Avg_len';
    $names[array_search('Auto_increment',$names)] = 'Auto_inc';
    echo '<th>'.implode('</th><th>',$names).'</th>';
    }
  ?>
  </tr>
  <?php 
  foreach($tables as $table) {
    $table = $fix_db_table_row($table); 
    echo '<tr><td>'.implode('</td><td>',$table).'</td></tr>';
  }
  ?>
  </table>
  <?php echo SimpleInput::ask('add_table','Add table',
    '<label style="margin-right:.2em;">Key: <input type="text" size="2" style="width:2em;" name="key_name" value="id"></label>'.
    '<select name="data_type">'.
    '<option value="bigint">bigint</option>'.
    '<option value="int" selected="selected">integer</option>'.
    '<option value="mediumint">mediumint</option>'.
    '<option value="smallint">smallint</option>'.
    '<option value="tinyint">tinyint</option>'.
    '</select>'.
    '<label style="margin:0 1em;">Unsigned:<input type="checkbox" value="1" name="unsigned" checked="checked" /></label>'.
    '<label >Auto increment:<input type="checkbox" value="1" name="auto_increment" checked="checked" /></label><br />'.
    'Table name: <input type="hidden" name="action" value="add_table" />'); ?>
</div>