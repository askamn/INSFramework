<?php
/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.5.0
 * Front Class
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

class Front
{
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
						'defaultSection' => 'LoginHandler',	
					),
				'index' => array(
						'defaultSection' => 'defaultSection',
					),
				'members' => array( 
						'defaultSection' => 'profile',	
					),
				'staff' => array( 
						'defaultSection' => 'tools',	
					),
				'search' => array(
						'defaultSection' => 'search',
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
            return \INS\Core::getInstance( __CLASS__, $arguments);
        else
            return \INS\Core::getInstance( __CLASS__ );
    }

    /**
     * Constructor of class.
     *
     * @return      void
     */
    public function __construct()
    {
        \INS\Core::checkState( __CLASS__ ); 
    }

    /**
     * Initialises the class
     *
     * @access 		public
     * @return 		void	
     */
    public function init() 
    {
    	/* No section */
		if( !mb_strlen( INS_SECTION ) ) 
		{	
			$this->section = $this->coreModules[$module]['defaultSection'];
		}

    	$module = $this->loadModule( INS_MODULE );
    	$this->_appInstances[$module]->Execute();
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
   		$dir = INS_FRONT_APPS_DIR;

    	if( in_array( $module, array_keys( $this->coreModules ) ) )	
   		{
   			/* No section */
    		if( !strlen( INS_SECTION ) ) 
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
				$classToLoad = '\\INS\\' . INS_APP . '\\front\\modules\\' . $module . '\\' .  $module . '_' . $section;

				if( INS_APP != 'core' )
					\INS\Language::i()->load( INS_APP );

				$class = new $classToLoad;
				$this->_appInstances[$module] = $class;
			}
			else
			{
				if( INS_DEV_MODE === TRUE )	
					die("Application: '$module' in $file does not exist.");
				else
					\INS\Http::i()->redirect( \INS\Core::$settings['site']['url'] . '/index.php' );
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
					\INS\Language::i()->load( INS_APP );

				$class = new $classToLoad;
				$this->_appInstances[$module] = $class;
			}
			else
			{
				if( INS_DEV_MODE === TRUE )	
					die("Application: '$module' in $file does not exist.");
				else
					\INS\Http::i()->redirect( \INS\Core::$settings['site']['url'] . '/index.php' );
			}
    	}

    	return $module;
    }
}
?>