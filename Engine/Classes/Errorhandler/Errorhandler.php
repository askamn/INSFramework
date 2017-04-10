<?php
/**
 * <pre>
 * 		Infusion Network Services
 * 		INS.{%APPNAME%} v2.0.0
 * 		Error Handler : Handles Errors, Converts PHP's Horrid Error Boxes Into Our Sleek Error Box.
 * 		Last Updated: $Date: {%DATE%} $
 * </pre>
 *
 * @author 		$Author: AmNX $
 * @copyright	(c) 2014 Infusion Network Services
 * @license		{%LICENSE%}
 * @package		INS.{%APPNAME%}
 * @link		{%WEBSITE%}
 * @since		Monday 14th April 2014 4:30
 * @version		$First Build: 100 $
 *
 */

namespace INS;

class Errorhandler
{
	/**
	 * All errors that the errorhandler is going to work on
	 * Includes Custom & Inbuilt errors
	 *
	 * @var		array
	 */
	static public $insErrorTypes = array( 
		E_ERROR              => 'Error',
		E_WARNING            => 'Warning',
		E_PARSE              => 'Parse Error',
		E_NOTICE             => 'Notice',
		E_CORE_ERROR         => 'Core Error',
		E_CORE_WARNING       => 'Core Warning',
		E_COMPILE_ERROR      => 'Compile Error',
		E_COMPILE_WARNING    => 'Compile Warning',
		E_DEPRECATED		 => 'Deprecated Warning',
		E_USER_ERROR         => 'User Error',
		E_USER_WARNING       => 'User Warning',
		E_USER_NOTICE        => 'User Notice',
		E_STRICT             => 'Runtime Notice',
		E_RECOVERABLE_ERROR  => 'Catchable Fatal Error',
		INS_SQL              => 'SQL Error',
	);

	/**
	 * @var 	array  		Some useful custom error codes
	 */
	static public $errorCodes = [
		'INS_SQL' => 1000
	];
	
	/**
	 * List of errors that will be ignored by this errorhandler
	 * These errors will not be displayed
	 *
	 * @var		array
	 */
	static public $ignoredErrorTypes = array( 
		E_DEPRECATED,
		E_NOTICE,
		E_USER_NOTICE,
		E_STRICT
	);
	
	/**
	 * List of errors that are fatal and cannot be displayed by our custom handler
	 *
	 * @var		array
	 */
	static public $fatalErrors = array(
		E_ERROR,
		E_PARSE,
		E_CORE_ERROR,
		E_CORE_WARNING,
		E_COMPILE_ERROR,
		E_COMPILE_WARNING,
		E_STRICT
	);
	
	/**
	 * Construct the object by setting up the error handler
	 *
	 * @return  void 
	 */
	static public function init()
	{
		defined( 'DATABASE_INFO_MISSING' ) or define( 'DATABASE_INFO_MISSING', 'Database information is missing or incomplete.' );
		defined( 'DATABASE_CONNECTION_FAILURE' ) or define( 'DATABASE_CONNECTION_FAILURE', 'No connection could be made because the target machine actively refused it.' );
		defined( 'DATABASE_NOT_SUPPORTED' ) or define( 'DATABASE_NOT_SUPPORTED', 'The database driver specified in the configuration files is not supported. Please visit http://www.infusionnetwork/install/requirements for more a list supported databases.' );
		defined( 'COOKIE_VALUE_BAD' ) or define( 'COOKIE_VALUE_BAD', 'Cookie value invalid.' );

		foreach( static::$errorCodes AS $k => $v )
		{
			defined( $k ) or define( $k, $v );
		}

		$errorTypes = E_ALL;
		foreach( static::$ignoredErrorTypes AS $type )
		{
			$errorTypes = $errorTypes & ~$type;
		}

		/**
		 * If we don't set display_errors as Off then, we will get two errors -> PHP + Custom
		 */
		ini_set( "display_errors", "off" );
		register_shutdown_function( '\INS\Errorhandler::INSErrorHandler' );
		set_error_handler( '\INS\Errorhandler::INSErrorHandler', $errorTypes );
	}
	
	/**
	 * Custom error handler for handling errors
	 *
	 * @param	string		The error-code/defined constant for error
	 * @param	string		The definition/explanation of error
	 * @param	integer		File in which error occurred
	 * @param	integer		Line number at which error occurred
	 * @return	void
	 */
	static public function printError( $errornumber, $defination, $errorfile, $linenumber )
	{ 
		/**
		 * Error reporting turned off
		 */ 
		if(error_reporting() === 0)
		{
			return;
		} 
		
		/**
		 * Ignore this error
		 */ 
		if(in_array($errornumber, static::$ignoredErrorTypes))
		{ 
			return;
		}
		
		/**
		 * Log error if logging is enabled
		 */ 
		if( \INS\Core::$settings['site']['ins_log_errors'] == 1)
		{
			static::logError();
		}
	
		if( $errornumber == INS_SQL )
		{
			$type = "SQL ERROR";
		}
		else 
		{
			$type = "APPLICATION ERROR";	  
		}

		$url = str_replace( 'index.php', '', $_SERVER['SCRIPT_NAME'] );

		if( $errornumber == INS_SQL )
		{
			/**
			 * Custom error handler HTML
			 */
			$eb  = '<html>';
			$eb .= '<head>';
			$eb .= '<title>SQL Error Encountered</title>';
			$eb .= '<link type="text/css" rel="stylesheet" href="'.$url.'Engine/Skins/errorhandler.style.css" />';
			$eb .= '</head>';
			$eb .= '<body>';
			$eb .= '<table class="table_error" cellpadding="0" cellspacing="0">';
			$eb .= '<thead><tr>';
			$eb .= '<td class="table_header">';
			$eb .= '<strong>'.static::$insErrorTypes[$errornumber].'</strong><span style="float: right;">Error Type: <strong>'.$type.'</strong></span></td></tr></thead>';
			$eb .= '<tbody><tr><td class="table_desc"><div style="margin-top:7px">';
			$eb .= 'IN.Cms has encountered an internal SQL error & cannot continue.</tr>';
			$eb .= '<tr>';
			$eb .= '<td class="table_general">';
			$eb .= '<strong>File:</strong> '.$errorfile.'';
			$eb .= '</td></tr>';
			$eb .= '<tr>';
			$eb .= '<td class="table_general">';
			$eb .= '<strong>Line:</strong> '.$linenumber.' [Relative to SQL::execute]';
			$eb .= '</td></tr>';
			$eb .= '<tr><td class="table_general">';
			$eb .= '<strong>Query:</strong> '.$defination['query'].'';
			$eb .= '</td></tr>';
			$eb .= '<tr><td class="table_general">';
			$eb .= '<strong>Error:</strong> '.$defination['message'].'';
			$eb .= '</td></tr>';
			$eb .= '<tr><td class="table_general">';
			$eb .= '<strong>Error Code:</strong> '.$defination['code'].'';
			$eb .= '</td></tr>';
			$eb .= '</tbody>';
			$eb .= '</table>';		
			$eb .= '</body>';
			$eb .= '<html>'; 	
		}
		else
		{
			/**
			 * Custom error handler HTML
			 */
			$eb  = '<html>';
			$eb .= '<head>';
			$eb .= '<title>Error Encountered</title>';
			$eb .= '<link type="text/css" rel="stylesheet" href="'.$url.'Engine/Skins/errorhandler.style.css" />';
			$eb .= '</head>';
			$eb .= '<body>';
			$eb .= '<table class="table_error" cellpadding="0" cellspacing="0">';
			$eb .= '<thead><tr>';
			$eb .= '<td class="table_header" colspan="2">';
			$eb .= '<strong>'.static::$insErrorTypes[$errornumber].'</strong><span style="float: right;">Error Type: <strong>'.$type.'</strong></span></td></tr></thead>';
			$eb .= '<tbody><tr><td class="table_desc"  colspan="2"><div>';
			$eb .= INS_SUITE . ' has encountered an internal error & cannot continue.</tr>';
			$eb .= '<tr>';
			$eb .= '<td class="table_general" colspan="1">';
			$eb .= '<strong>File:</strong></td><td style="padding-bottom: 20px;" colspan="1" class="table_general">'.$errorfile.'';
			$eb .= '</td></tr>';
			$eb .= '<tr>';
			$eb .= '<td class="table_general" colspan="1">';
			$eb .= '<strong>Line Number:</strong></td><td style="padding-bottom: 20px;" colspan="1" class="table_general">'.$linenumber.'';
			$eb .= '</td></tr>';
			$eb .= static::generateBacktrace( $defination );
			$eb .= '</tbody>';
			$eb .= '</table>';		
			$eb .= '</body>';
			$eb .= '<html>'; 	
        }

		die($eb);
	}
	
	/**
	 * Checks if a FATAL_ERROR [E_ERROR] occurred
	 *
	 * @return	void
	 */
	public static function INSErrorHandler()
	{
		$error = error_get_last();
		if(in_array($error['type'], static::$fatalErrors))
		{
			static::printError($error['type'], $error['message'], $error['file'], $error['line']);
		}		
	}	
	
	/**
	 * Logs the error that occurred
	 *
	 * @return	void
	 */
	public static function logError()
	{
		$error = error_get_last();
		
		defined("PHP_EOL") or define("PHP_EOL", "\n");
		defined("INS_ROOT") or define("INS_ROOT", dirname(dirname(__FILE__)));
		
		$logfile = 'errorlog_'.md5( \INS\Core::$config['admin']['admin_dir'] );
		
		$message = "Date: ". date("Y-m-d h:i:s", time()) . PHP_EOL ."Type: {$error['type']}". PHP_EOL ."Message: {$error['message']}". PHP_EOL ."File: {$error['file']}". PHP_EOL ."Line: {$error['line']}";
	    $message .= PHP_EOL ."-----------------------------------------------------------------------------------------------------" . PHP_EOL;
        @file_put_contents(INS_ROOT . "/" . \INS\Core::$config['admin']['admin_dir'] . "/{$logfile}.txt", $message . PHP_EOL, FILE_APPEND );
	}	
	
	/**
	 * Backtrace Generator
	 *
	 * @return	void
	 */
	public static function generateBacktrace( $errorDefinition = NULL )
	{
		if( $errorDefinition !== NULL )
		{
			if( is_array( $errorDefinition ) )
			{
				$errorDefinition = $errorDefinition['message'];
			}

			preg_match( '#^(.*?)\sStack trace:(.*)#is', $errorDefinition, $matches );

			if( empty( $matches ) )
			{
				$eb = '<td class="table_general" colspan="1">';
				$eb .= '<strong>Full Error:</strong></td><td style="padding-bottom: 20px;" colspan="1" class="table_general">'. $errorDefinition.'';
				$eb .= '</td></tr>';
				return $eb;
			}

			$eb = '<tr><td class="table_general" colspan="1" style="padding-bottom: 20px;">';
			$eb .= '<strong>Error Statement:</strong></td><td style="padding-bottom: 20px;" colspan="1" class="table_general">'.$matches[1].'';
			$eb .= '</td></tr>';

			$eb .= '<thead>';
			$eb .= '<tr>';
			$eb .= '<td class="table_header table_top_border" colspan="2">';
			$eb .= '<strong>Generated Backtrace: </strong><span style="float: right;">Memory Usage: <strong>'.INS_MEMORY_USAGE.' Bytes</strong></span>';
			$eb .= '</td>';
			$eb .= '</tr>';
			$eb .= '</thead>';
			$eb .= '<tbody>';
			
			$eb .= '<tr>';
			$eb .= '<td class="table_desc" colspan="2">';
			$eb .= '<div>';
			$eb .= "This is a custom generated backtrace for your ease.";
			$eb .= '</div>';
			$eb .= '</td>';
			$eb .= '</tr>';
			
			preg_match_all( '/#[\d](.*)+/', $errorDefinition, $traces );

			if(empty($traces))
			{
				return $eb;
			}

			foreach ($traces[0] AS $trace)
			{	
				$details = explode( " ", $trace );

				array_shift( $details );
				preg_match( '/(.*?)\(([\d]+)\)/', $details[0], $_matches );

				$file = $_matches[1];
				$line = $_matches[2];

				if( !mb_strlen( trim( $file ) ) OR !mb_strlen( trim( $line ) ) )
					continue;

				$eb .= '<tr>';
				$eb .= '<td class="table_general">';
				$eb .= '<strong>File</strong>';
				$eb .= '</td>';
				$eb .= "<td class=\"table_general\">{$file}</td>";
				$eb .= '</tr>';
				
				$eb .= '<tr>';
				$eb .= '<td class="table_general">';
				$eb .= '<strong>Line</strong>';
				$eb .= '</td>';
				$eb .= "<td class=\"table_general\">{$line}</td>";
				$eb .= '</tr>';
			}	
			
			$eb .= '</tbody>';	

			return $eb;
		}
		else
		{
			static $starttime = NULL;
			static $startline = 0;
			
			$traces = debug_backtrace();
			$eb  = '';
			$eb .= '<thead>';
			$eb .= '<tr>';
			$eb .= '<td class="table_header table_top_border" colspan="2">';
			$eb .= '<strong>Generated Backtrace: </strong><span style="float: right;">Memory Usage: <strong>'.INS_MEMORY_USAGE.' Bytes</strong></span>';
			$eb .= '</td>';
			$eb .= '</tr>';
			$eb .= '</thead>';
			$eb .= '<tbody>';
			
			$eb .= '<tr>';
			$eb .= '<td class="table_desc" colspan="2">';
			$eb .= '<div>';
			$eb .= "This is a custom generated backtrace for your ease.";
			$eb .= '</div>';
			$eb .= '</td>';
			$eb .= '</tr>';
			
			if( !INS_DEV_MODE )
			{
				array_shift($traces);
			}

			if(empty($traces))
			{
				return;
			}
			foreach ($traces AS $trace)
			{	
				if(empty($trace['file'])) 
				{
					$trace['file'] = "[PHP]";
				}			
				if(empty($trace['line']))
				{
					$trace['line'] = "&nbsp;";
					continue;
				}			
				if(!empty($trace['class'])) 
				{
					$trace['function'] = $trace['class'].$trace['type'].$trace['function'];
				}	
				$trace['file'] = str_replace(INS_ROOT, "/", $trace['file']);
				$line = $trace['line'];
				$explode = explode('/', $trace['file']);
				$file = array_pop($explode);
				
				if($starttime === NULL)
				{
					$startline = $line;
				}
				
				$length = count($trace);
				$result = array();
			   
				$starttime = time() + microtime();
				$exectime = time() + microtime() - $starttime;
				
				$eb .= '<tr>';
				$eb .= '<td class="table_general">';
				$eb .= '<strong>File</strong>';
				$eb .= '</td>';
				$eb .= "<td class=\"table_general\">{$file}</td>";
				$eb .= '</tr>';
				
				$eb .= '<tr>';
				$eb .= '<td class="table_general">';
				$eb .= '<strong>Line</strong>';
				$eb .= '</td>';
				$eb .= "<td class=\"table_general\">{$line}</td>";
				$eb .= '</tr>';
				
				$eb .= '<tr>';
				$eb .= '<td class="table_general" style="border-bottom: 3px double #9d9d9d">';
				$eb .= '<strong>Execution Time:</strong>';
				$eb .= '</td>';
				$eb .= "<td class=\"table_general\" style=\"border-bottom: 3px double #9d9d9d\">{$exectime}</td>";
				$eb .= '</tr>';
			}	
			
			$eb .= '</tbody>';	
			
			return $eb;
		}
	}
	
	/**
	 * Custom Error Logger
	 * Logs an error to the log file
	 *
	 * @return	void
	 */
	static public function logErrorToFile( $error, $file = 'gen' )
	{
		$message  = "Date: ". date( "Y-m-d h:i:s", time() ) . PHP_EOL;
		$message .= "Info: {$error}";
	    $message .= PHP_EOL . "--------------------------------------------------" . PHP_EOL;
		
        $logfile = 'errorlog_' . md5( time() );
		
		if( $file === 'gen' )
			@file_put_contents( INS_ROOT . "/" . \INS\Core::$config['admin']['dir'] . "/{$logfile}.txt", $message . PHP_EOL, FILE_APPEND);
		else if( $file === 'cache' )
			@file_put_contents( INS_ROOT . "/cache/{$logfile}.txt", $message . PHP_EOL, FILE_APPEND );
	}
}

?>