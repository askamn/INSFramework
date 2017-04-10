<?php
/**
 *
 * <pre>
 *	Filename: payments.class.php
 *	Description: Resource made for handling payment requests.
 *	Last Modified: 4 April 2014
 *  Author: AskAmn
 * </pre>
 *
 * @author AskAmn
 * @link 		
 *
 */
class Product 
{
	/**
	 * Integer to store ID's of payments
	 * 
	 * @int
	 */
	public $id;
	
	/**
	 * Array to store columns from Payments table
	 * 
	 * @int
	 */
	public $params = array();

	/**
	 * Constructor
	 * 
	 * @return
	 */	
	public function __construct($id)
	{
		$this->id = $id;
		self::get();
	}
	
	/**
	 * Function to store all values from table `products` in params array.
	 * 
	 * @return
	 */	
	public function get()
	{
		global $db;
		$this->params = $db->smart_query("SELECT * FROM `products` WHERE `id` = {$this->id}");
	}
	
	/**
	 * Function to create new product
	 * 
	 * @return
	 */	
	public function _new()
	{
		global $db;
		
		/* Do */
	}
	
	/**
	 * Function to delete a product
	 * 
	 * @return
	 */	
	public function delete()
	{
		global $db;
		
		/* Do */
	}
}

?>