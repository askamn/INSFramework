<?php

define('THIS_MODULE', "paypal_ipn");
define('THIS_MODULE_NAME_STRING', "PayPal IPN Payment Processor");

if(!defined('IN_PRE_PROCESSOR'))
{
	die("File read error.");
}

function ipn_details()
{
	$info = array(
					"name" => "PayPal IPN Payment Processor",  
					"version" => "1.0",
					"custom" => -1,
					"id" => ""
				 );	
	return $info;
}

function ipn_install_status()
{
	global $db;
	
	$db->smart_query("SELECT * FROM `modules` WHERE `name_string` = ".THIS_MODULE);
	
	if($db->num_rows())
	{
		return true;
	}
	else
		return false;
}

?>