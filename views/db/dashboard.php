
<h2 style="margin:0"><?=$app_name?><span style="font-size:60%;color:silver">v <?=$app_version?></span></h2>

<div style="padding:.5em;background:#efc">

<h3 style="display:inline-block;margin:0;padding:.3em;vertical-align:top;border:solid 1px black;background:silver;">DB Dashboard</h3>

<p style="display:inline-block;width:25em;margin:0 .5em">Overview and server statistics, select a table from the list or use the config button to select another database.</p>

<div style="display:inline-block;width:10em;margin:0;vertical-align:top;">
<?=$configform?>
</div>

</div>


<div><?=$connect_status?></div>
<div style="display:inline-block;background:#ddd;min-width:40em;border:solid 1px red;"><?=$content?></div>


<table style="display:inline-block;vertical-align:top;border:solid 2px black;">
<caption style="background:black;color:white;">Server statistics</caption>
<?=$status_rows?>
</table>