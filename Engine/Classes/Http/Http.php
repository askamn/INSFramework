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

class Http
{
	/**
	 * URL Constants
	 */
	const NORMAL_URLS 	= 0;
	const SEO_URLS 		= 1;
	const SHORT_URLS 	= 2;

	/**
	 * Include index.php in urls?
	 */
	const INDEX_INC 	= 0;

	/**
	 * @var 	URL Type
	 */
	public $urlType = 0;

	/**
	 * Stores Cookies
	 *
	 * @var  	array
	 * @access  public
	 * @since   Legacy Builds | 0.1
	 */
	public $cookie = array();

	/**
     * Bots Map
     *
     * @var     array
     */    
    public $botList = 
    [
        "008\/0.83",
        "AbachoBOT",
        "Acoon",
        "AESOP_com_SpiderMan",
        "ah-ha.com crawler",
        "appie",
        "Arachnoidea",
        "ArchitextSpider",
        "Atomz",
        "DeepIndex",
        "ESISmartSpider",
        "EZResult",
        "FAST-WebCrawler",
        "Fido",
        "Fluffy the spider",
        "Googlebot",
        "Gigabot",
        "Gulliver",
        "Gulper",
        "HenryTheMiragoRobot",
        "ia_archiver",
        "KIT-Fireball\/2.0",
        "LNSpiderguy",
        "Lycos_Spider_(T\-Rex)",
        "MantraAgent",
        "MSN",
        "NationalDirectory\-SuperSpider",
        "Nazilla",
        "Openbot",
        "Openfind piranha,Shark",
        "Scooter",
        "Scrubby",
        "Slurp.so\/1.0",
        "Slurp\/2.0j",
        "Slurp\/2.0",
        "Slurp\/3.0",
        "Tarantula",
        "Teoma_agent1",
        "UK Searcher Spider",
        "WebCrawler",
        "Winona",
        "ZyBorg",
        "YandexBot",
        "YandexImages",
        "facebookexternalhit",
    ];

    /**
     * @var 	Bot or not?
     */
    public $isBot = FALSE;

    /** 
     * Loads the instance of this class
     *
     * @return      resource
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
	 * Parses Request variables
	 *
	 * @param 	string 		URL to be redirected to
	 * @return	void
	 */
	public function __construct() 
	{
		$this->applyFilter( $_GET, $_POST );

		array_walk_recursive( $_COOKIE, '\INS\Filter::filterInput' );

		/* Cookies are set to have a prefix? Strip them */
		if( !empty( \INS\Core::$settings['site']['cookie_prefix'] ) )
		{
			foreach( $_COOKIE AS $name => $value )
			{	
				/* Match prefix */
				if( mb_strpos( $name, $prefix ) === 0 )
				{
					$this->cookies[ preg_replace( sprintf( '/^%s\_/', $prefix ), '', $name ) ] = $value;
				}
			}
		}
		else
		{	
			$this->cookies = $_COOKIE;
		}
	
		$this->request_method = mb_strtolower( $_SERVER['REQUEST_METHOD'] );

		if( \INS\Core::$settings['site']['seo_urls'] )
		{
			$this->urlType = static::SEO_URLS;
		}
		elseif( \INS\Core::$settings['site']['short_urls'] == "1" )
		{
			$this->urlType = static::SHORT_URLS;
		}

		/* Parse the useragent */
		$this->useragent = $this->parseUserAgent( NULL );

		/* Check if the useragent is a bot */
		$match = implode( '|', $this->botList );
		$this->isBot = ( preg_match( "/{$match}/i", $this->useragent['string'] ) ) ? TRUE : FALSE;
	}
	
	/**
	 * Applies a cleaning function to each of the supplied param
	 *
	 * @return 		void
	 */	
	public function applyFilter()
	{
		foreach( func_get_args() AS $argument )
		{
			if( is_array( $argument ) )
			{
				foreach( $argument AS $key => $val )
				{
					if( is_array( $val ) )
					{
						array_walk_recursive( $val,  '\INS\Filter::filterInput' );
					}
					else
					{
						\INS\Filter::filterInput( $val );
					}
					
					$this->$key = $val;
				}
			}
		}
	}

	/**
	 * Redirects to a specific URL
	 *
	 * @param 	string 		URL to be redirected to
	 * @return	void
	 */
	public function redirect($url) 
	{
		if( !headers_sent() )
		{
			exit( header( "Location: ".$url ) );
		}
		else
		{
			echo " <meta http-equiv=\"Location\" content=\"{$url}\"> <script> location.replace(\"{$url}\"); </script> ";
		}
	}

	/**
	 * Gets IP
	 *
	 * @since 	0.5.1						
	 * @return	string
	 */
	public function getIP()
	{
		$ip = $_SERVER['REMOTE_ADDR'];
		return $ip;
    }

    /**
	 * Generates a Crumb
	 *					
	 * @return	string
	 */
    public function generateCrumb()
    {
    	if( \INS\Session::i()->get( 'ins.csrfkey' ) )
    	{
    		$this->crumb = \INS\Session::i()->get( 'ins.csrfkey' );
		}
		else
		{
			$this->crumb = base64_encode( sha1( \INS\Core::$config['ins_unique_key'] . uniqid( rand(), TRUE ) . \INS\Session::i()->id ) );
			\INS\Session::i()->set( 'ins.csrfkey', $this->crumb );
    	}

    	return $this->crumb;
    }

    /**
	 * Verify token
	 *					
	 * @return	string
	 */
    public function verifyToken( $token )
    {
    	return ( $token === $this->crumb ) ? TRUE : FALSE;
    }

    /**
	 * Verify Ajax Request
	 *					
	 * @return	string
	 */
    public function verifyAjaxRequest( )
    {
    	if( $this->isAjax() )
    	{
    		if( isset( $_SERVER['HTTP_REFERER'] ) AND mb_strpos( trim( $_SERVER['HTTP_REFERER'] ), \INS\Core::$settings['site']['url'] ) !== FALSE ) 
    		{
    			if( defined( IN_ACP ) )
    			{
    				/* Redirect if the Ajax Request is in an expired session */
    				if( !\INS\Admin::i()->checkValidSession() )
	    			{
	    				return FALSE;
	    			}  
    			}
    			if( isset( \INS\Http::i()->crumb ) AND  $this->verifyToken( str_replace( '"', '', \INS\Http::i()->crumb ) ) ) 
    			{
    				return TRUE;
    			}
    		}
    	}

    	return FALSE;
    }

    /**
     * Parse Array Based Get/Post Values
     *
     * @param 		The key to be checked
     */
    public function parseArray( $key )
    {
    	$this->$key = ( preg_match( '#\[[\w\d,]+\]#', $this->$key ) ) ? explode( ',', $this->$key ) : NULL;
    }

    /**
	 * Is this is an Ajax request?
	 *					
	 * @return	string
	 */
    public function isAjax()
    {
    	return ( $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' ) ? TRUE : FALSE;
    }

    /**
	 * Verify acp session
	 *					
	 * @return	string
	 */
    public function verifyACPSession( \INS\Session $session )
    {
    	return ( $session['sid'] == $this->sessionKey );
    }

    /**
	 * Useragent Parser
	 * @brief   Needs further enhancement, like detection of Consoles
	 *
	 * @author  Chris
	 * @return	array
	 */
    public function parseUserAgent( $useragent = NULL )
	{
	    $useragent 		= ( is_null( $useragent ) OR empty( $useragent ) ) ? $_SERVER['HTTP_USER_AGENT'] : $useragent;
	    $platforms  	= "Windows|iPad|iPhone|Macintosh|Android|BlackBerry";
	    $browsers   	= "Firefox|Chrome"; 
	    $browsers_v 	= "Safari|Mobile"; // Mobile is mentioned in Android and BlackBerry UA's
	    $engines    	= "Gecko|Trident|Webkit|Presto";
	    $regex_pat 		= "/((Mozilla)\/[\d\.]+|(Opera)\/[\d\.]+)\s\(.*?((MSIE)\s([\d\.]+).*?(Windows)|({$platforms})).*?\s.*?({$engines})[\/\s]+[\d\.]+(\;\srv\:([\d\.]+)|.*?).*?(Version[\/\s]([\d\.]+)(.*?({$browsers_v})|$)|(({$browsers})[\/\s]+([\d\.]+))|$).*/i";
	    $replace_pat 	= '$7$8|$2$3|$9|${17}${15}$5$3|${18}${13}$6${11}';
	    $ua_array 		= explode( "|", preg_replace( $regex_pat, $replace_pat, $useragent, PREG_PATTERN_ORDER ) );

	    if( count( $ua_array ) > 1 )
	    {
	    	$return['string']  	 = $useragent;
	        $return['platform']  = $ua_array[0];  // Windows / iPad / MacOS / BlackBerry
	        $return['type']      = $ua_array[1];  // Mozilla / Opera etc.
	        $return['renderer']  = $ua_array[2];  // WebKit / Presto / Trident / Gecko etc.
	        $return['browser']   = $ua_array[3];  // Chrome / Safari / MSIE / Firefox
	        $return['version'] 	 = ( preg_match( "/^[\d]+\.[\d]+(?:\.[\d]{0,2}$)?/", $ua_array[4], $matches ) ) ? $matches[0] : $ua_array[4];
	    }
	    else
	    {
	        return FALSE;
	    }

	    switch( mb_strtolower( $return['browser'] ) )
	    {
	        case "msie":
	        case "trident":
	            $return['browser'] = "Internet Explorer";
	        break;
	        case "": // IE 11 is a steamy turd (thanks Microsoft...)
	            if ( mb_strtolower($return['renderer']) == "trident" )
	                $return['browser'] = "Internet Explorer";
	        break;
	    }

	    switch( mb_strtolower( $return['platform'] ) )
	    {
	        case "android":    // These browsers claim to be Safari but are BB Mobile 
	        case "blackberry": // and Android Mobile
	            if ($return['browser'] =="Safari" || $return['browser'] == "Mobile" || $return['browser'] == "")
	            {
	                $return['browser'] = "{$return['platform']} mobile";
	            }
	        break;
	    }

	    return $return;
	} 

	/**
	 * Builds the link to the current section
	 *
	 * @return 	string 	Built URL
	 */
	public function link( $app = NULL, $module = NULL, $section = NULL, $extraQueryString = NULL )
	{
		$app 		= ( $app === NULL ) ? INS_APP : ( ( $app === 'FALSE' ) ? NULL : $app );
		$module 	= ( $module === NULL ) ? INS_MODULE : ( ( $module === 'FALSE' ) ? NULL : $module );
		$section 	= ( $section === NULL ) ? INS_SECTION : ( ( $section === 'FALSE' ) ? NULL : $section );

		return \INS\Core::$settings['site']['url'] . ( ( IN_ACP === TRUE ) ? '/' . \INS\Core::$config['admin']['dir'] : '' ) . '/' . $this->buildURL( $app, $module, $section, $extraQueryString );
	}

	/**
	 * Returns the current url
	 */
	public function __tostring()
	{
		return $this->link();
	}

	/**
	 * Builds urls as per supplied app, module & section
	 *
	 * @return 	string 		The URL
	 */
	public function buildURL( $app = 'core', $module = NULL, $section = NULL, $extraQueryString = NULL )
	{
		switch( $this->urlType )
		{
			case 0: 
				$url = 
				( static::INDEX_INC == 0 ? '?app=' : 'index.php?app=' ) . $app . 
				( $module  !== NULL ? '&amp;module=' . $module : '' ) . 
				( $section !== NULL ? '&amp;section=' . $section : '' ) .
				( $extraQueryString !== NULL ? '&amp;' . $extraQueryString : '' );
			case 1:
				return;
			case 2: 
				$url = 
				( static::INDEX_INC == 0 ? '?load=' : 'index.php?load=' ) . $app . 
				( ( $module  !== NULL AND !empty( $module ) ) ? '_' . $module : '' ) . 
				( ( $section !== NULL AND !empty( $section ) ) ? '_' . $section : '' ) .
				( ( $extraQueryString !== NULL AND !empty( $extraQueryString ) ) ? '&amp;' . $extraQueryString : '' );
		}

		return ( IN_ACP === TRUE ) ? $url . '&amp;sessionKey=' . $this->sessionKey : $url;
	}

	/**
	 * Adds a url for parsing
	 */
	public function url( $url = NULL )
	{
		$this->url = $url;

		if( $this->url === NULL )
		{
			$this->url = $this->link();
		}

		return static::i();
	}

	/**
	 * Appends array as the query string of a url
	 */
	public function append( $array )
	{
		if( empty( $this->url ) )
		{
			return $this->url;
		}

		if ( mb_substr( $this->url, 0, 2 ) === '//' )
		{
			$this->url = 'http:' . $this->url;
		}

		$urlParts = parse_url( $this->url );
		$sep = '?';

		/* We have a query */
		if( !empty( $urlParts['query'] ) )
		{
			$sep = '&amp;';
		}

		foreach( $array AS $key => $value )
		{
			$this->url .= $sep . $key . '=' . $value;
		}

		return $this->url;
	}

	/**
	 * Strips a key from the query string
	 */
	public function strip( $keys )
	{
		if( empty( $this->url ) )
		{
			return $this->url;
		}

		if( !is_array( $keys ) )
		{
			$keys = array( $keys );
		}

		parse_str( $this->url, $urlBits );

		foreach( $keys AS $key )
		{
			if( mb_strpos( $this->url, $key . '=' ) !== FALSE OR mb_strpos( $this->url, $key . '%3D' ) !== FALSE )
			{
				if( array_key_exists( $key, $urlBits ) )
				{
					unset( $urlBits[ $key ] );
				}
			}
		}
		
		$this->url = http_build_query( $urlBits );

		return $this->url;
	}

	/**
	 * Sets a cookie.
	 *
	 * @param	string		Cookie Name
	 * @param	string		Cookie Value
	 * @param	integer		Cookie Expiry in days
	 * @param	string 		Cookie Path
	 * @param 	string 		Cookie Domain
	 * @param 	boolean 	Over Https only?
	 * @param 	boolean     Over Http only?
	 * @return	void
	 */
	public function setCookie( $name, $value, $expire = 0, $path = '/', $domain = NULL, $secure = FALSE, $httpOnly = TRUE )
	{
		/* Cookie path set in settings */
		if( !empty( \INS\Core::$settings['site']['cookie_path'] ) )
		{
			$path = \INS\Core::$settings['site']['cookie_path'];
		}
		/* No path? Figure it out */
		elseif( $path === NULL )
		{
			$path = mb_substr( \INS\Core::$settings['site']['url'], mb_strpos( \INS\Core::$settings['site']['url'], $_SERVER['SERVER_NAME'] ) + mb_strlen( $_SERVER['SERVER_NAME'] ) );
			$path = mb_substr( $path, mb_strpos( $path, '/' ) );
		}

		// Cookie path should end with a trailing slash
		if( mb_substr( $path, -1 ) != '/' )
 		{
 			$path .= '/';
 		}

		/* Figure out the domain */
		if( $domain === NULL AND !empty( \INS\Core::$settings['site']['cookie_domain'] ) )
		{
			$domain = \INS\Core::$settings['site']['cookie_domain'];
		}
		else
		{
			$domain = '';
		}

		/* Check if we are on an SSL and secure flag is not false */
		if( mb_substr( \INS\Core::$settings['site']['url'], 0, 5 ) == 'https' AND $secure !== FALSE )
		{
			$secure = TRUE;
		}

		/* Has prefix? */
		$__name = $name;
		if( !empty( \INS\Core::$settings['site']['cookie_prefix'] ) )
		{
			$name = \INS\Core::$settings['site']['cookie_prefix'] . $name;
		}

		if( $expire instanceof \INS\Date )
		{
			$expire = INS_SCRIPT_TIME + $expire->ts(); 		
		}
		else
		{
			$expire = INS_SCRIPT_TIME + gmdate('D, d-M-Y H:i:s \\G\\M\\T', $expire );
		}

		if( setcookie( $name, $value, $expire, $path, $domain, $secure, $httpOnly ) === TRUE )
		{
			$this->cookies[ $__name ] = $value;
			return TRUE;
		}

		return FALSE;
	}
}