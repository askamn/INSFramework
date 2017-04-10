<?php
/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.5.0
 * Date Class
 * Last Updated: $Date: 2014-12-27 4:10:16 (Sun, 27 Dec 2014) $
 * </pre>
 * 
 * @author 		$Author: AskAmn$
 * @copyright	(c) 2014 Infusion Network Solutions
 * @license		http://www.infusionnetwork/licenses/license.php?view=main&version=2014
 * @package		IN.CMS
 * @since 		0.5.2; 22 August 2014
 */

namespace INS;

class Date 
{
	/**
	 * @var 	Array 		Numbers in words
	 */
	public $wordsForNumbers = [
		1 => 'one',
		2 => 'two',
		3 => 'three',
		4 => 'four',
		5 => 'five',
		6 => 'six',
		7 => 'seven',
		8 => 'eight',
		9 => 'nine',
		10 => 'ten',
		11 => 'eleven',
		12 => 'twelve'
  	];

  	/**
	 * @var 	integer|null  Timestamp
	 */
  	public $timestamp = NULL;

	/** 
	 * Loads the instance of this class
	 *
	 * @return 		resource
	 */
	public static function i()
    {
    	$arguments = func_get_args();

    	if( !empty( $arguments ) )
    	{
        	return \INS\Core::getInstance( __CLASS__, $arguments);
    	}
        else
        {
        	return \INS\Core::getInstance( __CLASS__ );
        }
    }

	/**
	 * Constructor of class.
	 *
	 * @return 		void
	 */
	public function __construct( )
	{
		\INS\Core::checkState( __CLASS__ ); 
	}

	/**
	 * Converts a given timestamp into user-friendly date/time
	 *
	 * @param 	integer 	The Unix Stamp to work on
	 * @param 	string 		Format, the date will be converted to
	 * @param 	boolean 	Convert to "today" & "yesterday" format (To be added later) // Added at 24th Oct 2014 @ 8:12PM
	 * @return 	string 		The formatted timestamp
	 */
	static public function convertTimestamp( $timestamp = NULL, $format = 'Y-m-d H:i:s', $relative = FALSE )
	{
		if( $timestamp === NULL )
		{
			$timestamp = INS_SCRIPT_TIME;
		}

		if( $relative === TRUE )
		{
			$date = date( 'Y-m-d', $timestamp );

		    if( $date == date( 'Y-m-d' ) ) 
		    {
		      	$dayname = 'Today';
		    }
		    elseif( $date == date('Y-m-d', $timestamp - 86400 ) ) 
		    {
		     	$dayname = 'Yesterday';
		    }
		    else
		    {
				$dayname = date( $format, $timestamp );
		    }

		    return $dayname;
		}
		else
		{
			$date = gmdate( $format, $timestamp );
		}

		return $date;
	}

	/**
	 * This function is used to feed a time interval to the \INS\Date class
	 *
	 * @param 	The Time
	 */
	public function setTime( $time )
	{
		/* Already a time stamp */
		if( is_numeric( $time ) )
		{
			$this->timestamp = $time;
			return TRUE;
		}

		$parts = explode( ' ', $time );
		$number = $parts[0];
		$word = $parts[1];
		$multiplier = 1;

		if( !is_numeric( $parts[0] ) )
		{
			$offset = array_search( mb_strtolower( $number ), $this->wordsForNumbers );
			/* May be its a word */
			if( $offset !== FALSE )
			{
				$number = $offset;
			}
			else
			{
				return FALSE;
			}
		}

		switch( $word )
		{
			case 'seconds':
			case 'second': 
				break;
			case 'minute':
			case 'minutes':
				$multiplier = 60;
				break;
			case 'hour':
			case 'hours':
				$multiplier = 3600;
				break;
			case 'day':
			case 'days':
				$multiplier = 86400;
				break;
			case 'month':
			case 'months':
				$multiplier = 2592000;
				break;
			case 'year':
			case 'years':
				$multiplier = 946080000;
				break;
		}

		$this->timestamp = $number * $multiplier;

		return static::i();
	} 

	/**
	 * Get Timestamp
	 *
	 * @return 		integer 	The timestamp
	 */
	public function getTimestamp()
	{
		return $this->timestamp === NULL ? INS_SCRIPT_TIME : $this->timestamp;
	}

	/**
	 * Alias for getTimestamp
	 */
	public function ts()
	{
		return $this->getTimestamp();
	}
}

?>