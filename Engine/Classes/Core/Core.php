<?php
/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.5.0
 * INSController Class
 * Last Updated: $Date: 2014-10-28 5:35 (Tue, 26 Oct 2014) $
 * </pre>
 * 
 * @author      $Author: AskAmn$
 * @package     IN.CMS
 * @copyright   (c) 2014 Infusion Network Solutions
 * @license     http://www.infusionnetwork/licenses/license.php?view=main&version=2014  Revision License 0.1.1
 * @since       0.5.0
 * @version     Revision: 0510
 */

namespace INS;

class Core 
{
	/**
	 * Config
	 *
	 * @var 	array
	 * @since 	0.5
	 */
	static public $config = array();

	/**
	 * Settings
	 *
	 * @var 	array
	 * @since 	0.5
	 */
	static public $settings = array();

	/**
	 * Input
	 *
	 * @var 	array
	 * @since 	0.5.1
	 */
	static public $input = array();

	/**
	 * List of LoginHandlers
	 *
	 * @var 	array
	 */
	static public $loginHandlers = array();

	/**
	 * Version
	 *
	 * @var 	string
	 */
	static public $version = "0.5.1";

	/**
	 * Version code
	 *
	 * @var 	integer
	 */
	static public $versionCode = 0510;
	
	/**
	 * Get or Post?
	 *
	 * @var 	string
	 */
	static public $request_method = "";
	
	/**
	 * Yummy Cookies!
	 *
	 * @var 	string
	 */
	static private $cookies = array();
	
	/**
	 * Core request integer vars
	 *
	 * @var		array
	 */
	public $internalProperties = array(
		'uid', 'gid', 'cid'
	);	

	/**
     * Stores the current instance of a class
     *
     * @var 	array
     * @access 	private	
     */ 
    private static $instances = array();

    /**
     * Stores the class names as keys and a boolean value to indicate if they are instantiated or not
     *
     * @var 	array
     * @access 	private	
     */ 
    private static $classes = array();
     
    /**
     * Get Object instance.
     *
     * @param   string      $class_name Class name (can be retrieved using '__CLASS__' constant).
     * @return  object      Instance
     */
    public static function getInstance( $className, $arguments = NULL )
    {
    	$count = count( static::$classes );

    	/* Not yet instantiated? */
        if( ( $index = array_search( $className, static::$classes ) ) === FALSE ) 
        {
            $reflector = new \ReflectionClass( $className );
            static::$instances[ $count ] = !is_null( $arguments ) ? $reflector->newInstanceArgs( $arguments ) : $reflector->newInstance();
        	static::$classes[ $count ] = $className;
        }
        else
        {
        	$count = $index;
        }

        return static::$instances[ $count ];
    }
     
    /**
     * Check if the Class was instantiated using the getInstance() method.
     *
     * @param   string      The class name to work on
     */
    public static function checkState( $className )
    {
        if( ( $index = array_search( $className, static::$classes ) ) !== FALSE )
        {
        	if(defined("INS_DEV_MODE"))	
        	{
          		trigger_error("Cannot continue. Class has already been instantiated or the getInstance() method was not used.", E_USER_ERROR);		
        	}
        }
    }

	/** 
	 * Loads the instance of INSController class
	 *
	 * @return 		resource
	 */
    public static function instance()
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
	public function __construct()
	{
		\INS\Core::checkState( __CLASS__ );
	}

	/**
	 * INS Controller initiator
	 *
	 * @return 		void
	 * @since 		0.1
	 */
	public function init()
	{		
		if( is_file(INS_SYSTEM_DIR . "/settings.php") )
		{
			require ( INS_SYSTEM_DIR . "/settings.php" ); 
			static::$settings = &$settings;
		}
		if( is_file( INS_SYSTEM_DIR . "/config.php" ) )
		{
			require_once( INS_SYSTEM_DIR . "/config.php" );
			if( is_array( $config ) )
			{
				static::$config = &$config;
			}
		}

		if( !empty( static::$config ) )
		{
			\INS\Db::i( static::$config['database'] ); 
			\INS\Cache::i( static::$config['cache']['use_database_on_fail'] ); 
		 	\INS\Language::i(); 
			\INS\Template::i();
			\INS\Http::i()->generateCrumb();
			\INS\Session::i()->init();
			\INS\Language::i()->load("globals");
			\INS\Applications::i()->loadAddons();

			/* Do admin stuff if we are in admin panel */
			if( defined( "IN_ACP" ) )
			{ 
				/* What? */
				if( \INS\Http::i()->sessionKey != \INS\Session::i()->id )
				{
					\INS\Session::i()->forget();
				}

				\INS\Admin::i()->init();
			}		
			/* Because, we let Admin initialise stuff if we are in ACP */
			else
			{
				\INS\Users::i();
			}	
		}

		$predefined_arrays = array("_GET", "_POST", "_SERVER", "_COOKIE", "_FILES", "_ENV", "GLOBALS");
		
		/* Nope */
		foreach($predefined_arrays as $_parse)
		{
			if(isset($_REQUEST[$_parse]) || isset($_FILES[$_parse]))
			{
				die("Invalid Request.");
			}
		}

		static::instance()->buildComponentData(); 

		/* A useful constant */
		define( 'IS_AJAX', \INS\Http::i()->isAjax() );
	}

	/**
	 * Set up request redirect stuff
	 *
	 * @return	void
	 * @access	protected
	 */
	protected static function buildComponentData()
	{	
		/* Short Urls Turned on? */
		if( static::$settings['site']['short_urls'] == "1" AND !empty( \INS\Http::i()->load ) )
		{
			$structure = explode( '_', \INS\Http::i()->load );
			\INS\Http::i()->app = $structure[0];
			\INS\Http::i()->module = $structure[1];
			\INS\Http::i()->section = $structure[2];
		}  

		if( empty( \INS\Http::i()->app ) )
		{
			\INS\Http::i()->app = 'core';
		}

		if( empty( \INS\Http::i()->module ) )
		{
			if( IN_ACP === TRUE )
			{
				if( \INS\Admin::$loggedIn === TRUE )
				{
					\INS\Http::i()->module = 'overview';
				}
				else
				{
					\INS\Http::i()->module = 'login';
				}
			}
			else
			{
				\INS\Http::i()->module = 'index';
			}
		}
		
		define( 'INS_APP', ( trim( \INS\Http::i()->app ) ) );
		define( 'INS_SECTION', ( trim( \INS\Http::i()->section ) ) );
		define( 'INS_MODULE', ( trim( \INS\Http::i()->module ) ) );
	}

	/**
	 * Return Settings
	 *
	 * @since 	0.5.1
	 * @return 	array
	 */
	static public function &Settings() 
	{
		return static::$settings;
	}	

	/**
	 * Return Config
	 *
	 * @since 	0.5.1
	 * @return 	array
	 */
	static public function &Config() 
	{
		return static::$config;
	}
	
	/**
	 * Encodes the password
	 *
	 * @param 	string  Value to be encrypted
	 * @param   string  Salt
	 * @return 	string
	 */
	static public function encode( $value, $hash )
	{
		if( CRYPT_BLOWFISH == 1 )
		{
			return crypt( $value, '$2y$12$' . $hash );
		}
		else
		{
			return sha1( $value . $hash );
		}
	}

	/**
	 * Generates a random Salt
	 */
	static public function generateSalt()
	{
		return str_replace( '+', '.', mb_substr( base64_encode( openssl_random_pseudo_bytes( 17 ) ), 0, 22 ) );
	}

	/**
	 * Performs emailing
	 *
	 * @param 	string 		The email where the message will be sent
	 * @param 	string 		The subject of the message
	 * @param 	string 		The message to send
	 * @param 	boolean		Determines HTML/Text status	
	 * @return 	boolean 	Status of mail object
	 * @access  public
	 */
	static public function mailer($email, $subject, $message, $html_state)
	{	
		// Mail should load lang within itself.
		//static::$_instances['Language']->load("mail");
		return \INS\Mail::i()->init( $email, $subject, $message, $html_state )->send();
	}

	/**
	 * Shows an error page in case of bad situations
	 *
	 * @param 	string 		The error encountered
	 * @param 	int 		The title to show on error page
	 * @return 	void
	 * @access  public
	 */
	static public function error( $error = FALSE, $title = FALSE )
	{	
		if( !$error )
		{
			$error = \INS\Language::i()->strings['unknown_error'];
		}
		if( !$title )
		{
			$title = 'Error Encountered:' . static::$settings['site']['name'];
		}
		
		eval("\$content = \"". \INS\Template::i()->get("error")."\";");
		\INS\Template::i()->output( $content, $title );
	}

	/**
	 * Rebuilds settings
	 *
	 * @return		string
	 * @access 		public
	 */
	static public function rebuildSettings()
	{
		$rows = \INS\Db::i()->fetchAll('*', 'settings');
		ksort( $rows );

		$data = "<?php\n";

		foreach($rows AS $row)
		{ 
			$value = '"'. str_replace( '"', '\"', $row['value'] ) .'"';
		    $data .= '$settings[\''.$row['identifier'].'\'][\''.$row['name'].'\'] = '.$value.';';
		    $data .= "\n";
		}

		$data .= "?>";
		file_put_contents( INS_SYSTEM_DIR . '/settings.php', $data );
	} 

	/**
	 * Runs final destructs
	 *
	 * @return 		void
	 */
	static public function runFinalDestructs(  )
	{
		//@unlink( INS_SYSTEM_DIR . DIRECTORY_SEPARATOR . 'BackEnd' . DIRECTORY_SEPARATOR . 'Sessions' . DIRECTORY_SEPARATOR . 'sess_' . \INS\Sessions::i()->oldId );
		
		\INS\Init::$time['total'] = round( microtime( TIME ) - \INS\Init::$time['start'], 4 );
		echo 'Page Generated In: ' . ( \INS\Init::$time['total']*1000 ) . 'ms Queries: ' . \INS\Db::i()->totalQueries;
	}

	/**
	 * Debug
	 *
	 * @return 		void
	 */
	static public function Debug( $var )
	{
		echo "<pre>"; print_r($var) . "</pre>";
	}
}
?>