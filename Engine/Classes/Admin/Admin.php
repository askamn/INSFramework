<?php
/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.5.0
 * Admin Class
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

class Admin extends \INS\Users
{
	/**
	 * @var 	boolean	 	Is the Adminstrator logged in?
	 */
	static public $loggedIn = FALSE;

	/**
	 * Holds instances of apps
	 *
	 * @var		array	
	 */
	public $_appInstances = array();

	/**
	 * User Object
	 *
	 * @var		array	
	 */
	protected $user;

	/**
	 * Application Details
	 *
	 * @var		array	
	 */
	public $coreModules = array(
				'login' => array( 
						'defaultSection' => 'adminLoginHandler',	
					),
				'overview' => array(
						'defaultSection' => 'dashboard',
					),
				'system' => array( 
						'defaultSection' => 'settings',	
					),
				'tools' => array( 
						'defaultSection' => 'server',	
					),
				'members' => array( 
						'defaultSection' => 'list'
					),
				'customize' => array( 
						'defaultSection' => 'templates',
					),
				'applications' => array(
						'defaultSection' => 'applications'
					),
		);

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
     * Just to over ActiveRecord's stuff
     */
    public function ___get( $name )
    {
    	return $this->$name;
    }

 	/**
     * Just to over ActiveRecord's stuff
     */
    public function ___set( $name, $value )
    {
    	$this->$name = $value;
    }

    /**
     * Constructor of class.
     *
     * @return      void
     */
    public function __construct()
    {
        
    }

    /**
     * Initialises the class
     *
     * @access 		public
     * @return 		void	
     */
    public function Execute() 
    {
    	if( static::$loggedIn === TRUE )
    	{
    		static::$member->logIP();
    		\INS\Template::i()->pushToCache( "'header', 'navigation', 'navigation_links_textonly', 'navigation_links', 'footer', 'sidebar', 'sidebar_links', 'sidebar_sublinks', 'sidebar_sublinks_link', 'headerinclude'", \INS\Core::$settings['admin']['theme'] );
    		\INS\Applications::i()->runHooks( 'admin_header_apps' );
    		\INS\Template::i()->addToNavigation( \INS\Language::i()->strings['ins_admin'], \INS\Core::$settings['site']['url'] . '/' . \INS\Core::$config['admin']['dir'] );
    	}
    	else
    	{
    		/* JSON request in an invalid session? */
    		if( 
				(( isset( \INS\Http::i()->section ) AND \INS\Http::i()->view == 'ajaxrequesthandler' ) OR 
				( isset( \INS\Http::i()->type ) AND \INS\Http::i()->type == 'ajax' ) OR
				( isset( \INS\Http::i()->request ) AND \INS\Http::i()->request == 'ajax' )) AND 
				( INS_MODULE != 'login' )
			)
			{
				\INS\Json::_error( \INS\Language::i()->strings['admin_sessionexpired'] );
				exit();
			}

			$rD = ( mb_strlen( $_SERVER['QUERY_STRING'] ) ) ? '&r=' . urlencode( $_SERVER['QUERY_STRING'] ) : '';

			if( INS_MODULE != 'login' )	
			{
				\INS\Http::i()->redirect( \INS\Http::i()->buildURL( 'core', 'login', NULL, $rD ) );
			}
    	}

    	/* No section */
		if( !mb_strlen( INS_SECTION ) ) 
		{	
			$this->section = $this->coreModules[$module]['defaultSection'];
		}

    	$module = $this->loadModule( INS_MODULE );
    	$this->_appInstances[$module]->Execute();
    }
	
   /**
	* Checks for valid session of an Admin
	*
	* @return 	boolean
	*/
	public function checkValidSession()
	{		
        $uid = \INS\Session::i()->get('ins.uid');
		$key = \INS\Session::i()->get('ins.key');
		
		if( !$key OR !$uid ) 
		{
			return FALSE;
		}

		if( \INS\Db::i()->f( 'COUNT(*)', 'admin_sessions', '`dateline` > ? AND `uid`=? AND `sessionkey`=? AND `ip`=? AND `useragent`=?', NULL, [ 1=>INS_SCRIPT_TIME, 2=>$uid, 3=>$key, 4=>\INS\Http::i()->getIP(), 5=>\INS\Http::i()->useragent[ 'string' ] ] )->get() == 1 )
		{
			static::$loggedIn = TRUE;
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Validates a users login details
	 *
	 * @param 	string
	 * @param	string
	 * @return	boolean
	 * @access 	public
	 */
	public function validateData( $username, $password )
    { 
		if( ( $member = parent::validate( $username, $password ) ) !== FALSE )
		{ 
			return $member;
		}

		return FALSE;
    } 

    /**
	 * Builds the current module link
	 *
	 * @param 		string 		The App Component to load
	 * @return 		string 	
	 * @access 		public	
	 */
    public function loadModule( $module )
    {
    	$dir = INS_ADMIN_APPS_DIR;

    	if( in_array( $module, array_keys( $this->coreModules ) ) )	
   		{
   			/* No section */
    		if( !mb_strlen( INS_SECTION ) ) 
    		{	
				$toLoad  = $this->coreModules[$module]['defaultSection'] . '.php';
				$section = $this->coreModules[$module]['defaultSection'];
    		}
    		/* We have a section */
    		else
    		{
    			$toLoad = INS_SECTION . '.php';
    			$section = INS_SECTION;
    		}

    		if( is_file( $file = INS_APPS_DIR . DIRECTORY_SEPARATOR . INS_APP . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . $toLoad ) )
			{
				require_once $file;
				$classToLoad = $module . '_' . $section;

				if( INS_APP != 'core' )
				{
					\INS\Language::i()->load( INS_APP );
				}

				$class = new $classToLoad;
				$this->_appInstances[$module] = $class;
			}
			else
			{
				if( INS_DEV_MODE === TRUE )	
				{
					die("Application: '$module' in $file does not exist.");
				}
				else
				{
					\INS\Http::i()->redirect( \INS\Core::$settings['site']['url'] . '/' . \INS\Core::$config['admin']['dir'] . '/index.php' );
				}
			}
    	}
    	else
    	{
    		$toLoad = INS_SECTION . '.php';
    		$section = INS_SECTION;

    		if( is_file( $file = INS_APPS_DIR . DIRECTORY_SEPARATOR . INS_APP . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . $toLoad ) )
			{
				require_once $file;
				$classToLoad = $module . '_' . $section;

				if( INS_APP != 'core' )
				{
					\INS\Language::i()->load( INS_APP );
				}

				$class = new $classToLoad;
				$this->_appInstances[$module] = $class;
			}
			else
			{
				if( INS_DEV_MODE === TRUE )	
				{
					die("Application: '$module' in $file does not exist.");
				}
				else
				{
					\INS\Http::i()->redirect( \INS\Core::$settings['site']['url'] . '/' . \INS\Core::$config['admin']['dir'] . '/index.php' );
				}
			}
    	}

    	return $module;
    }
}
?>