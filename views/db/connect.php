<?php
# connect to db

global $db;
$db = @new mysqli($host,$user,$pass,$dbname);