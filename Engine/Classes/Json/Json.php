<?php
/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.5.0
 * Input handler
 * Last Updated: $Date: 2014-10-26 22:40:30 (Sun, 26 Oct 2014) $
 * </pre>
 * 
 * @author 		$Author: AskAmn$
 * @copyright	(c) 2014 Infusion Network Solutions
 * @license		http://www.infusionnetwork/licenses/license.php?view=main&version=2014
 * @package		IN.CMS
 * @since 		0.5.2; 22 August 2014
 * @version 	$Revision: 	01224 $
 */

namespace INS;

class Json
{
	/**
	 * Prints a JSON error
	 *
	 * @param 		string
	 * @return		void
	 */
	public static function error( $message )
	{
		$array = array(
			'message' 		=> "<div class=\"form-message error\">{$message}</div>",
			'status' 		=> 'Failure',
			'statuscode' 	=> 0
		);
		@header('Content-type: application/json');
		echo json_encode($array);
		exit;
	}

	/**
	 * Prints a JSON message
	 *
	 * @param 		string
	 * @return		void
	 */
	public function printmessage( $message )
	{
		$array = array(
			'message' 		=> "<div class=\"form-message success\">{$message}</div>",
			'status' 		=> "Success",
			'statuscode' 	=> 1
		);
		@header('Content-type: application/json');
		echo json_encode($array);
		exit;
	}	

	/**
	 * Prints a JSON message, as is
	 *
	 * @param 		string
	 * @return		void
	 */
	static public function _print( $message )
	{
		$array = array(
			'message' 		=> $message,
			'status' 		=> 'Success',
			'statuscode' 	=> 1
		);
		@header('Content-type: application/json');
		echo json_encode($array);
		exit;
	}	

	/**
	 * Prints a JSON error, as is
	 *
	 * @param 		string
	 * @return		void
	 */
	public static function _error( $message )
	{
		$array = array(
			'message' 		=> $message,
			'status' 		=> 'Failure',
			'statuscode' 	=> 0
		);
		@header('Content-type: application/json');
		echo json_encode($array);
		exit;
	}

	/**
	 * Prints JSON
	 *
	 * @param 		string
	 * @return		void
	 */
	public static function echoJson( $message, $array )
	{
		$array['message']    = $message;
		$array['status']     = 'Success';
		$array['statuscode'] = 1;

		@header('Content-type: application/json');
		echo json_encode($array);
		exit;
	}

	/**
	 * Prints an array as JSON
	 *
	 * @param 		string
	 * @return		void
	 */
	public static function echoArrayAsJson( $array )
	{
		@header('Content-type: application/json');
		echo json_encode($array);
		exit;
	}
}

?>