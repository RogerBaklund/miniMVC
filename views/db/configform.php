
<button id="config_open" onclick="
  document.getElementById('configform').style.display='block';
  this.style.display='none';
  return false;">Config</button>

<div id="configform" style="display:none;position:absolute;">
<form method="POST">
  <fieldset><legend>Database configuration</legend>
    <label for="host">Hostname:</label>
    <input type="text" name="host" id="host" value="<?=$host?>" />
    <label for="user">User:</label>
    <input type="text" name="user" id="user" value="<?=$user?>" />
    <label for="pass">Password:</label>
    <input type="text" name="pass" id="pass" value="<?=$pass?>" />
    <label for="dbname">Database:</label>
    <input type="text" name="dbname" id="dbname" value="<?=$dbname?>" />
  </fieldset>
  <fieldset>
    <input type="hidden" name="configsave" value="1" />
    <input type="submit" value="Save" />
    <button onclick="
      document.getElementById('configform').style.display='none';
      document.getElementById('config_open').style.display='inline';
      return false;">Cancel</button>
  </fieldset>
</form>
</div>