<div id="table_rows">
  <table>
  <caption><?=$rows_caption?></caption>
  <tr><?php 
    $names = $rows ? array_keys($rows[0]) : array();
    echo (($primary_key && !is_array($primary_key)) ? '<th>&nbsp;</th>':'').
      '<th>'.implode('</th><th>',$names).'</th>';
  ?>
  </tr>
  <?php 
  $prepare = function($d) {
    if(is_null($d)) return '<i style="color:silver;">NULL</i>';
    if(strlen($d) > 200) $d = substr($d,0,200).'...';
    $d = htmlentities($d);
    if(strpos($d,"\n")) $d = "<pre>$d</pre>";
    return $d;
  };
  foreach($rows as $row) {
    if($primary_key) {
      if(is_array($primary_key)) {$edit = '';} // tmp
      else $edit = '<td><a href="?action=edit&amp;key='.urlencode($row[$primary_key]).'">Edit</a></td>';
    } else $edit = '';
    echo '<tr>'.$edit.'<td>'.implode('</td><td>',array_map($prepare,$row)).'</td></tr>';
  }
  ?>
  </table>
  <div class="pagination"><?=$pagination?></div>
</div>