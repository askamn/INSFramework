<?php
/**
 *
 * <pre>
 *	Filename: ipn.class.php
 *	Description: Resource made for handling paypal ipn requests.
 *	Last Modified: 4 April 2014
 *  Author: AskAmn
 * </pre>
 *
 * @author AskAmn
 * @link 		
 *
 */

class IPN 
{
	/**
	 * Handles paypal's URL [Sandbox/Test or Real Payment]
	 * 
	 * @string
	 */
	private $url = "";
	
	/**
	 * Stores incoming Request
	 * 
	 * @array
	 */
	private $data = array();
	
	/**
	 * Charset
	 * 
	 * @string
	 */
	private $charset = "utf-8";
	
	/**
	 * Stores current status. 0: Not yet initiated. 1: Success. -1: Failure
	 * 
	 * @int
	 */
	public $status = 0;
	
	/**
	 * Stores current status. 0: Not yet initiated. 1: Success. -1: Failure
	 * 
	 * @int
	 */
	public $item_id;

	/**
	 * The heart of the class. Parses, initiates & stores Success or Failure.
	 * 
	 * @param Incoming POST request
	 */
	public function __construct($data, $item_id)
	{
		global $lang;
		$lang->load("paypal.module");
		
		$this->data = $data;
		$this->item_id = $item_id;
		
		if($this->item_id == "")
		{
			error($lang->paypal_item_id_blank);
		}
		
		/* Sandbox? */
		if(array_key_exists('test_ipn', $this->data) && 1 === (int)$this->data['test_ipn'])
			$this->url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
			
		/* Nah, real payment probably. */
		else
			$this->url = 'https://www.paypal.com/cgi-bin/webscr';
				
		$this->initiate();	
	}
	
	/**
	 * Sends confirmation Request to PayPal
	 * 
	 * @return 
	 */
	public function initiate()
	{
		if(!is_array($this->data))
		{	
			return;
		}
		
		$request = curl_init();
		curl_setopt_array($request, array
		(
			CURLOPT_URL => $url,
			CURLOPT_POST => TRUE,
			CURLOPT_POSTFIELDS => http_build_query(array('cmd' => '_notify-validate') + $this->data),
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_HEADER => FALSE,
			CURLOPT_SSL_VERIFYPEER => TRUE,
			CURLOPT_CAINFO => 'cacert.pem',
		));

		$response = curl_exec($request);
		$status   = curl_getinfo($request, CURLINFO_HTTP_CODE);

		curl_close($request);

		if($status == 200 && $response == 'VERIFIED')
		{
			$this->price_validate();
			$this->parse_data();
			$this->log();
			$this->status = 1;
		}
		else
		{
			$this->status = -1;
		}
	}
	
	/**
	 * Fixes Charset
	 * 
	 * @return
	 */
	private function parse_data()
	{
		ksort($this->data);

		if(array_key_exists('charset', $this->data))
		{
			/* Same as ours? Return */
			if($this->data['charset'] == $this->charset)
				return;

			/* Oh no, this is some alien language, let us convert into our own */
			foreach($this->data as $key => &$value)
			{
				$value = mb_convert_encoding($value, 'utf-8', $charset);
			}

			/* I dont know why, but just store these incase. */
			$this->data['charset_original'] = $this->data['charset'];
			$this->data['charset'] = 'utf-8';
		}
	}
	
	/**
	 * Has the user paid the right amount of money?
	 * 
	 * @array
	 */
	 
	private function price_validate()
	{
		global $db;
		
		$db->smart_query("SELECT * FROM `products` WHERE `price` = {$this->data['mc_gross']} AND `id` = `{$this->item_id}`", array("rows" => 2));
		
		if($db->rowCount('products', '`price` = {$this->data['mc_gross']} AND `id` = `{$this->item_id}`') == 1)
			return true;
		else
			return false;
	}	
	
	/**
	 * Logs data to a file.
	 * 
	 * @array
	 */
	public function log()
	{
		$output = implode("\t", array(time(), json_encode($this->data)));
		file_put_contents('ipn.log.txt', $output.PHP_EOL, FILE_APPEND);
	}
}
?>