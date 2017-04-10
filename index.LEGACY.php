<?php
define('THIS_SCRIPT', 'index.php');
define('IN_INS', 1);
 
/** 
 * Include our main required files
 */
require_once "init.php";

$addons->runHooks('index_start');
require_once INS_ROOT."/core/global.php";

$module = $ins->input['module'];

/**
 * Force login check
 */
if($ins->settings['users']['force_login'])
{
	if(!$user->INSsession())
	{
		redirect($ins->settings['site']['url']."/members.php");
	}
}

/**
 * Evaluate, as per action
 */
if($module == "")
{   
	/**** Custom ****/
	eval("\$sidebar = \"".$templates->get("sidebar")."\";"); 
	/**** Custom:Ends ****/	 
    eval("\$index = \"".$templates->get("index")."\";"); 
}
elseif($module == "aboutus")
{
	/**** Custom ****/
	eval("\$sidebar = \"".$templates->get("aboutussidebar")."\";"); 
	/**** Custom:Ends ****/	 
	eval("\$index = \"".$templates->get("aboutus")."\";"); 
}
elseif($module = "schedule")
{
	$title = "";
	$rows = $db->fetchAll('*', 'drschedule');
	foreach($rows as $row)
	{
		if($row['presenter'])
		{
			$row['presenter'] = getUsernameByUID($row['presenter']);
		}	
		/* 1. Monday */
		if($row['day'] == 'Monday')
		{	
			eval("\$monday .= \"".$templates->get("indexschedule_list")."\";"); 
		}
		elseif($row['day'] == 'Tuesday')
		{
			eval("\$tuesday .= \"".$templates->get("indexschedule_list")."\";"); 
		}
		elseif($row['day'] == 'Wednesday')
		{
			eval("\$wednesday .= \"".$templates->get("indexschedule_list")."\";"); 
		}
		elseif($row['day'] == 'Thursday')
		{
			eval("\$thursday .= \"".$templates->get("indexschedule_list")."\";"); 
		}
		elseif($row['day'] == 'Friday')
		{
			eval("\$friday .= \"".$templates->get("indexschedule_list")."\";"); 
		}
		elseif($row['day'] == 'Saturday')
		{
			eval("\$saturday .= \"".$templates->get("indexschedule_list")."\";"); 
		}
		elseif($row['day'] == 'Sunday')
		{
			eval("\$sunday .= \"".$templates->get("indexschedule_list")."\";"); 
		}
	}
	
	if(strlen($monday) < 1)
	{
		$monday = '<table style="width: 100%"><tbody><tr><td>Nothing scheduled...</td></tr></tbody></table>';
	}
	if(strlen($tuesday) < 1)
	{
		$tuesday = '<table style="width: 100%"><tbody><tr><td>Nothing scheduled...</td></tr></tbody></table>';
	}
	if(strlen($wednesday) < 1)
	{
		$wednesday = '<table style="width: 100%"><tbody><tr><td>Nothing scheduled...</td></tr></tbody></table>';
	}
	if(strlen($thursday) < 1)
	{
		$thursday = '<table style="width: 100%"><tbody><tr><td>Nothing scheduled...</td></tr></tbody></table>';
	}
	if(strlen($friday) < 1)
	{
		$friday = '<table style="width: 100%"><tbody><tr><td>Nothing scheduled...</td></tr></tbody></table>';
	}
	if(strlen($saturday) < 1)
	{
		$saturday = '<table style="width: 100%"><tbody><tr><td>Nothing scheduled...</td></tr></tbody></table>';
	}
	if(strlen($sunday) < 1)
	{
		$sunday = '<table style="width: 100%"><tbody><tr><td>Nothing scheduled...</td></tr></tbody></table>';
	}
	$dayname = '<h3>Monday</h3>';
	$schedulelist = $dayname.$monday;
	$dayname = '<h3>Tuesday</h3>';
	$schedulelist .= $dayname.$tuesday;
	$dayname = '<h3>Wednesday</h3>';
	$schedulelist .= $dayname.$wednesday;
	$dayname = '<h3>Thursday</h3>';
	$schedulelist .= $dayname.$thursday;
	$dayname = '<h3>Friday</h3>';
	$schedulelist .= $dayname.$friday;
	$dayname = '<h3>Saturday</h3>';
	$schedulelist .= $dayname.$saturday;
	$dayname = '<h3>Sunday</h3>';
	$schedulelist .= $dayname.$sunday;
	
	/**** Custom ****/
	eval("\$sidebar = \"".$templates->get("sidebar")."\";"); 
	/**** Custom:Ends ****/	 
    eval("\$index = \"".$templates->get("indexschedule")."\";"); 
}
 
/* Lets Output the HTML */
$templates->output($index); 
?>