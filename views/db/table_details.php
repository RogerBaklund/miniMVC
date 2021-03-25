<div id="table_details">
  
  <table>
  <caption><?=$caption?></caption>
  <tr><?php 
    $names = array_keys($fields[0]);
    echo '<th>'.implode('</th><th>',$names).'</th>';
  ?>
  </tr>
  <?php 
  foreach($fields as $field) {
    #if($field['Key'] == 'PRI') $primary[] = $field['Field'];
    echo '<tr><td>'.implode('</td><td>',$field).'</td></tr>';
  }
  ?>
  </table>
  
  <div class="table_actions">
    <button onclick="location.href=location.href.split('?')[0]+'?action=add_row';">Add row</button>
    
    <?php 
      echo SimpleInput::ask('add_field','Add field','Name and type:'); 
      echo SimpleInput::ask('copy_table','Copy table','New table name:'); 
    ?>
  </div>
  
  <p>
    This table has <b><?=$rowcount?></b> rows
    
    <?php if($rowcount && !isset($table_rows)) { ?>

      <button onclick="location.href=location.href.split('?')[0]+'?action=browse';">Browse</button>

    <?php } if(!$rowcount) { ?>

      <button onclick="
        if(confirm('Are you sure you want to delete this table?')) 
          location.href=location.href.split('?')[0]+'?action=delete';">
          Delete table
      </button>
      
    <?php } ?>    
  </p>
  
</div>

<?php if(isset($table_rows) && $rowcount) echo $table_rows; ?>
