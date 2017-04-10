<?php
/**
 * <pre>
 *	Filename: listener.php
 *	Description: Handles incoming paypal requests.
 *	Last Modified: 4 April 2014
 *  Author: AskAmn
 * </pre>
 *
 * @author AskAmn
 * @link 		
 *
 */

define('THIS_SCRIPT', 'paypal_listener.php');
define('IN_PRE_PROCESSOR', 1);

$dir = dirname(dirname(__FILE__));
require_once $dir."/init.php"; 

if(!isset($ins->['txn_id']) && !isset($ins->['txn_type']))
{
	die("Error: Access Denied.");
}
/* Well, maybe we have a payment, but we cannot parse this users request as his browser denied our Cookie Request. */  
if(!isset($ins->cookies['item_id']))
{
	die("Looks like you paid, but the server cannot accept your request because you have cookies disabled. <br />This is not really a big issue. Please contact the administrator & provide him your transaction ID and your request will be carried forward!");
}
 
/* All cool. Lets start */
$ipn_post_data = $ins->input; 

require_once ROOT."/modules/paypal/ipn.class.php";
$ipn = new IPN($ipn_post_data, $ins->cookies['item_id']); 

if($ipn->status == 1)
{
	if(isset($ins->cookies['return_url']))
	{
		$message = "Payment successful. <a href=\"{$ins->cookies['return_url']}\">Click here to get your product.</a>";
	}
	else
	{
		$message = "We received your payment. Please wait till one of our staff member contacts you.";
	}
	
	echo "Payment made.";
	
	eval("\$content = \"".$templates->get("paypal_payment_success")."\";"); 
}
else
{
	eval("\$content = \"".$templates->get("paypal_payment_failed")."\";"); 
}

$template->output($content);
?>