<?php
/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.5.0
 * Validation Helper Class
 * Last Updated: $Date: 2014-10-28 5:35 (Tue, 26 Oct 2014) $
 * </pre>
 * 
 * @author      $Author: AskAmn$
 * @package     IN.CMS
 * @copyright   (c) 2014 - SVN_YYYY Infusion Network Solutions
 * @license     http://www.infusionnetwork/licenses/license.php?view=main&version=2014  Revision License 0.1.1
 * @since       0.5.0
 * @version     SVN_VERSION_NUMBER
 */

namespace INS\Data;

class Validation
{
    /** 
     * Loads the instance of this class
     *
     * @return      resource
     * @access 		public
     */
    public static function i()
    {
        $arguments = func_get_args();
        return !empty( $arguments ) ? \INS\Core::getInstance( __CLASS__, $arguments) : \INS\Core::getInstance( __CLASS__ );
    }

	/**
	 * Constructor of class.
	 *
	 * @param 		integer
	 * @return 		void
	 */
	public function __construct()
	{
		\INS\Core::checkState( __CLASS__ ); 
	}

	/**
	 * Checks if a string has a minimum length
	 *
	 * @param 		string 		The string itself
	 * @param 		integer 	The minimum length
	 * @param 		boolean 	If TRUE, "or equals to" condition will be dropped 
	 * @return 		boolean 	
	 */
	public function minLength( $value, $length, $strict=FALSE )
	{
		if( $strict === TRUE )
			return mb_strlen( $value ) > $length;

		return mb_strlen( $value  ) >= $length;
	}

	/**
	 * Checks if a string has a maximum length
	 *
	 * @param 		string 		The string itself
	 * @param 		integer 	The maximum length
	 * @param 		boolean 	If TRUE, "or equals to" condition will be dropped 
	 * @return 		boolean 	
	 */
	public function maxLength( $value, $length, $strict=FALSE )
	{
		if( $strict === TRUE )
			return mb_strlen( $value ) < $length;
		
		return mb_strlen( $value  ) <= $length;
	}

	/**
	 * Checks if a string is a valid url
	 * 
	 * @see 		https://www.owasp.org/index.php/OWASP_Validation_Regex_Repository
	 * @param 		string 		The string itself
	 * @return 		boolean 	
	 */
	public function url( $value )
	{
		return (bool)preg_match( "/((((https?|ftps?|gopher|telnet|nntp)://)|(mailto:|news:))(%[0-9A-Fa-f]{2}|[-()_.!~*';/?:@&=+$,A-Za-z0-9])+)([).!';/?:,][[:blank:]])?$/", $value );
	}

	/**
	 * Checks for validity of an Email
	 * 
	 * @param 		string 		The email
	 * @return 		boolean 	
	 */
	public function email( $email )
	{
		return (bool)filter_var( $email, FILTER_VALIDATE_EMAIL );
	}

	/**
	 * Checks if a string is a valid url
	 * 
	 * @param 		string 		The string itself
	 * @param 		integer 	The minimum length
	 * @param 		integer 	The maximum length
	 * @return 		boolean 	
	 */
	public function lengthInBetween( $value, $min, $max, $strict=FALSE )
    {
    	$length = mb_strlen( $value );

        if( $strict === TRUE )
        	return ( $length > $min AND $length < $max );

        return ( $length >= $min AND $length <= $max );
    }

    /**
	 * Checks if a string is complex in nature | Useful for passwords
	 * 
	 * @param 		string 		The string itself
	 * @return 		boolean 	
	 */
    public function isComplex( $value )
    {
    	return (bool)preg_match( '#^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?![\s]){6,}.*$#', $value );
    }

    /**
	 * Checks if the given value is a number
	 * 
	 * @param 		string 		The value
	 * @return 		boolean 	
	 */
    public function isNumeric( $value )
    {
    	return is_numeric( $value );
    }
}