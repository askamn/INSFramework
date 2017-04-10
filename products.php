<?php

define('THIS_SCRIPT', 'support.php');
define('IN_INS', 1);

require_once "./init.php";
eval("\$headerinclude = \"".$templates->get("headerinclude")."\";");
eval("\$header = \"".$templates->get("header")."\";");

eval("\$footer = \"".$templates->get("footer")."\";");
eval("\$content = \"".$templates->get("support")."\";"); 
$templates->output($content); 
?>
