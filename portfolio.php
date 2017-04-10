<?php

define('THIS_SCRIPT', 'portfolio.php');
define('IN_PRE_PROCESSOR', 1);
 
// The main file where all the magic starts!
require_once "./init.php";

// Build the title for this page
$title = build_title(THIS_SCRIPT);
		
// So what does the user wants to see?
$action = sanitize($_GET['action']);

/* Do we have force login enabled? */
if($settings['users']['force_login'])
{
	if(!$user->session() && $action != "login")
	{
		redirect($_url."/members.php");
	}
}

// Get through each Action!
if($action == "")
{   
    eval("\$content = \"".$templates->get("portfolio")."\";"); 
}
 
// Lets Output the HTML! 
$templates->output($content); 
?>
