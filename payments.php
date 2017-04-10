<?php
/**
 * <pre>
 *	Filename: payments.php
 *	Description: Handles incoming payment requests.
 *	Last Modified: 4 April 2014
 *  Author: AskAmn
 * </pre>
 *
 * @author AskAmn
 * @link 		
 * @examples:
 		<form name="_xclick" action="{$settings['site']['url']}/payments.php" method="post" target='_blank'>
 		<input type="hidden" name="cmd" value="_xclick">
 		<input type="hidden" name="no_shipping" value="1">
 		<input type="hidden" name="business" value="{$settings['paypal']['email']}">
		<input type="hidden" name="currency_code" value="{$product['currency']}">
		<input type="hidden" name="item_name" value="{$product['name']}">
		<input type="hidden" name="item_number" value="{$product['number']}">
		<input type="hidden" name="amount" value="{$product['cost']}">
		<input type="hidden" name="notify_url" value="{$settings['site']['url']}/listeners/paypal.listener.php">
		<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_buynow_SM.gif" border="0" name="submit" alt="Buy Now">
		</form>
 */

define('THIS_SCRIPT', 'payments.php');
define('IN_PRE_PROCESSOR', 1);

require_once "./init.php"; 
require_once ROOT."/classes/payments.class.php";

$product = new Product($core->input['id']);

if(!isset($core->input['name']))
	die();

if($core->input['do'] == "forward" && $core->request_method == "post")	
{
	/* If you don't set cookie, PayPal Listener will deny your request. */
	setcookie("return_url", $core->input['return_url'], time()+60*20); /* 20 Min. Expiry */
	setcookie("item_id", $core->input['id'], time()+60*20); /* 20 Min. Expiry */
	
	$querystring .= "business=".urlencode($settings['paypal']['email'])."&";
	$querystring .= "cmd=".urlencode("_xclick")."&";
	$querystring .= "no_shipping=".urlencode($product['no_shipping'])."&";
	$querystring .= "item_name=".urlencode($product['name'])."&";
	$querystring .= "currency_code=".urlencode($product['currency'])."&";
	$querystring .= "amount=".urlencode($product['price'])."&";
	$querystring .= "return=".urlencode(stripslashes($product['returnurl']))."&";
	$querystring .= "cancel_return=".urlencode(stripslashes($product['returnurl_cancel']))."&";
	$querystring .= "notify_url=".urlencode($settings['site']['url']).'/listeners/paypal.listener.php';
	
	header('Location: https://www.paypal.com/cgi-bin/webscr?'.$querystring);
	exit();
}

eval("\$content = \"".$templates->get("payment_{$core->input['name']}")."\";"); 

$template->output($content);
?>