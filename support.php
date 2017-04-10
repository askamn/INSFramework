<?php
define('THIS_SCRIPT', 'support.php');
define('IN_INS', 1);

require_once "./init.php";
require_once INS_ROOT.'/core/global.php';

eval("\$content = \"".$templates->get("support")."\";"); 
$templates->output($content); 
?>
