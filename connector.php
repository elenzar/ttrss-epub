<?php
//php script made for a cron job to generate the .epub file periodically
$dir = dirname(dirname(dirname(__FILE__)));

chdir($dir);

$requete = $argv[1];
$_REQUEST['op'] = $requete; //typically: pluginhandler&plugin=epub&method=exportrun&offset=0

include 'prefs.php';
include 'backend.php';

?>
