<?php
/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.5.0
 * Filters data by checking fopr various dangerous code.
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

class Filter
{	
	/**
	 * Array of Replacements & Searches to be made
	 *
	 * @var 	array => ( $id => array( $search, $replacement, $options ) )
	 */
	public $rules = [
		0 => [['\r\n', '\r', '\n\r', '\n'], '<br />'],
		1 => [['&', '<!--', '-->', '<', '>', '"', '$', '!', '\''], ['&amp;', '&#60;&#33;--', '--&#62;', '&lt;', '&gt;', '&quot;', '&#036;', '&#33;', '&#39;']],
		2 => ['#</*(applet|meta|xml|blink|link|style|script|embed|object|iframe|frame|frameset|ilayer|layer|bgsound|title|base)[^>]*>?#i', '', ['name' => 'XSSTags' ,'type' => 'preg_replace', 'optional' => TRUE]],
        3 => ['#!(&#|\\\)[xX]([0-9a-fA-F]+);?!e','chr(hexdec("$2"))#', ['name' => 'HexConvert','type' => 'preg_replace', 'optional' => TRUE]] // Convert Hexadcimals
    ];

	/**
	 * Optional rules to be used
	 *
	 * @var 	array|null
	 * @access 	public
	 */
	public $optionalRules = NULL;

	/**
	 * Extra replacements to be made
	 *
	 * @var 	array|null
	 * @access 	public
	 */
	public $extraRules = NULL;

    /** 
     * Loads the instance of this class
     *
     * @return      resource
     */
    static public function i()
    {
        $arguments = func_get_args();

        if( !empty( $arguments ) )
            return \INS\Core::getInstance( __CLASS__, $arguments);
        else
            return \INS\Core::getInstance( __CLASS__ );
    }

	/**
	 * Checks for XSS & filters values
	 *
	 * @param	string			The value to be cleaned
	 * @param   array|null      Any extra filter options to be applied
	 * @param   bool            Should we check for optional values? TRUE : Yes ; FALSE : Nope
	 * @return	array|null		The input after parse
	 * @access  public
	 */
    public function filter( $value, $extraRules = NULL, $internal = FALSE )
    {
    	if( !mb_strlen($value) ) 	return;

    	/* If any extra rules were supplied, use them */
    	if( $extraRules !== NULL )
    	{
    		static::i()->extraRules = $extraRules;
    		try
    		{
    			static::i()->rules = array_merge( static::i()->rules, static::i()->extraRules );
    		}
    		catch( \Exception $e )
    		{
    			throw new \Exception('rules_structure_wrong');
    		}
    	}

    	/* Perform replacement */
    	foreach( static::i()->rules AS $id => $rule )
    	{
    		/* No options supplied */
    		if( !isset( $rule[2] ) )
    		{
    			$value = str_replace( $rule[0], $rule[1], $value );	
    		}
    		/* We have some options, act accordingly */
    		else
    		{
    			if( isset( $rule[2]['type'] ) AND function_exists( $rule[2]['type'] ) )
    			{
    				/* Optional? */
    				if( isset( $rule[2]['optional'] ) AND $rule[2]['optional'] === TRUE )
    				{
    					/* Internal checks? */
    					if( $internal === TRUE )
	    				{
	    					continue;
	    				}
                        /* 
                            Alright so this rule is optional and we are not parsing this value internally. 
                            Now we check if the user actually wants to do something with this? 
                            Take a look at the optionalRules array to check if it has this rule. 
                        */
    					if( is_array( static::i()->optionalRules ) AND in_array( $rule[2]['name'], static::i()->optionalRules ) )
    					{
    						$rule[2]['type']( $rule[0], $rule[1], $value );
    					}
    				}
    				else
    				{
    					$rule[2]['type']( $rule[0], $rule[1], $value );
    				}
    			}
    			else
    			{
    				trigger_error( $rule[2]['type'] . ': Function doesn\'t exist.'  );
    			}
    		}
    	}

    	return $value;
    }

    /**
     * Cleans URLs
     *
     * @param 		string|int 	Value to be cleaned
     * @param       mixed|null  Just to fit array_walk_recursive
     * @return 		string|int 	Cleaned value
     */
    static public function filterInput( $value, $key = NULL )
    {
    	/* Remove Null Byte */
    	$value = str_replace( chr(0), '', $value );

    	if ( get_magic_quotes_gpc() )
		{
			$value = stripslashes( $value );
		}
    }

    /**
     * Removes BOM | Useful for Template Output
     *
     * @param 		string|int 	Value to be cleaned
     * @return 		string|int 	Cleaned value
     */
    public function filterBOM( $value )
    {
    	return str_replace( "\xEF\xBB\xBF", '', $value ); 
    }

    /**
	 * Filters Strings
	 *
	 * @param 		string 		Input string
	 * @return 		string		Cleaned string
	 * @since 		0.5.1
	 */
	public function filterString( $string )
	{
		/* Underscore qualifies as an identifier */
		return preg_replace( "/[^a-zA-Z0-9\-\_]/", '', $string );
	}
}    
?>