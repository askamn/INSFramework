<?php
/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.5.0
 * ApplicationHandler Class
 * Last Updated: $Date: 2014-10-28 5:35 (Tue, 26 Oct 2014) $
 * </pre>
 * 
 * @author      $Author: AskAmn$
 * @package     IN.CMS
 * @copyright   (c) 2014 Infusion Network Solutions
 * @license     http://www.infusionnetwork/licenses/license.php?view=main&version=2014  Revision License 0.1.1
 * @since       0.6.0
 * @version     Revision: 0600
 */

class applications_install Extends \INS\Admin
{
	/**
	 * Output holder
	 *
	 * @var 		string
	 * @access 		protected
	 */
	protected $html;

	/**
	 * Core apps
	 *
	 * @var 		array
	 * @access 		protected
	 */
	protected $coreApps = [
		'instalk',
		'insblog',
	];

    /**
     * Constructor
     *
     * @return 		void
     */
    public function __construct()
    {
        $this->title = \INS\Language::i()->strings['admin_modules_applications_install_title'];
        $this->link = \INS\Core::$settings['site']['url'] . '/' . \INS\Core::$config['admin']['dir'] . '/index.php?app=' . INS_APP . '&module=' . INS_MODULE . '&section=' . INS_SECTION;
    	\INS\Template::i()->addToNavigation( \INS\Language::i()->strings['admin_modules_applications_installing_navtitle'], $this->link );
    }

	/** 
	 * Main Entry Point
	 *
	 * @return 		void
	 * @access 		public
	 */
	public function Execute() 
	{
		switch( \INS\Http::i()->request )
		{
			case 'ajax':
				$this->_ajax();
				break;
			default: 
				$this->_default();
		}
	}
	
	/**
	 * @brief  dEfault Request
	 */
	public function _default()
	{
		$appdir = \INS\Filter::i()->filterString( \INS\Http::i()->dir );
		eval("\$this->html .= \"".\INS\Template::i()->getAcp("install_start")."\";");
		\INS\Template::i()->output( $this->html, $this->title );
	}

	/**
	 * @brief  Start Request
	 */
	public function _start()
	{
		$appdir = \INS\Filter::i()->filterString( \INS\Http::i()->dir );
		
		\INS\XML::i()->addToParser( INS_PLUGINS_DIR . DIRECTORY_SEPARATOR . $appdir . DIRECTORY_SEPARATOR . 'info.xml');
		\INS\XML::i()->parse();
		/* No need to separate parsedXML as different arrays, since only one array exists */
		$info = \INS\XML::i()->getXMLTag( \INS\XML::i()->parsedXML );

		/* App installed, let us check if it needs upgrade */
		if( \INS\Applications::i()->appInstalled( $appdir ) )
		{	
			if( \INS\Applications::i()->versionCompare( $appdir, $info['version_code'] ) )
			{
				$array = [ 'response' => 'upgrade', 'responseCode' => '2' ];
				\INS\Json::echoJson( \INS\Language::i()->strings['admin_modules_application_install_appupgradeavailable'], $array );
			}
			else
			{
				$array = [ 'response' => 'appalreadyinstalled', 'responseCode' => '3', 'modalheader' => \INS\Language::i()->strings['admin_modals_install_appalreadyinstalled'] ];
				\INS\Json::echoJson( \INS\Language::i()->strings['admin_modules_application_install_appalreadyinstalled'], $array );
			}
		}

		$info['installdate'] = time();
		$info['enabled'] = 1;
		$info['dir'] = $appdir;
		$info['description'] = \INS\Db::i()->escapeString($info['description']);
		
		try
		{
			\INS\Db::i()->insert('applications', $info);
			$array = [ 'response' => 'queries', 'responseCode' => '1' ];
            \INS\Json::echoJson( \INS\Language::i()->strings['admin_modules_applications_install_applicationisinstalling'], $array );
		}
		catch(Exception $e)
		{
			\INS\Json::_error( \INS\Language::i()->strings['admin_modules_applications_install_applicationinstallfailed'] );
		}
	}
	
	/**
	 * Upgrade app
	 *
	 * @return		void
	 */
	public function _upgrade()
	{
		$appdir = \INS\Filter::i()->filterString( \INS\Http::i()->dir );

		\INS\XML::i()->addToParser( INS_PLUGINS_DIR . DIRECTORY_SEPARATOR . $appdir . DIRECTORY_SEPARATOR . 'info.xml');
		\INS\XML::i()->parse();
		/* No need to separate parsedXML as different arrays, since only one array exists */
		$info = \INS\XML::i()->getXMLTag( \INS\XML::i()->parsedXML );

		if( !\INS\Applications::i()->versionCompare( $appdir, $info['version_code'] ) )
		{
			$array = [ 'response' => 'appalreadyinstalled', 'responseCode' => '3', 'modalheader' => \INS\Language::i()->strings['admin_modals_install_appalreadyinstalled'] ];
			\INS\Json::echoJson( \INS\Language::i()->strings['admin_modules_application_install_appalreadyinstalled'], $array );
		}

		if( is_file( INS_PLUGINS_DIR . DIRECTORY_SEPARATOR . $appdir . DIRECTORY_SEPARATOR . 'upgrade.php' ) )
		{
			require_once INS_PLUGINS_DIR . DIRECTORY_SEPARATOR . $appdir . DIRECTORY_SEPARATOR . 'upgrade.php';
		}
		else
		{
			$array = [ 'response' => 'upgradefilemissing', 'responseCode' => '4' ];
			\INS\Json::echoJson( \INS\Language::i()->strings['admin_modules_application_install_upgradefilemissing'], $array );
		}
		
		$uc = $appdir.'_Upgrade';
		$class = new $uc;
		
		$class->Execute();

		$array = [ 'response' => 'end', 'responseCode' => '1' ];
        \INS\Json::echoJson( \INS\Language::i()->strings['admin_modules_applications_install_upgradingapp'], $array );
	}
	
	/**
	 * Queries to be executed
	 *
	 * @return		void
	 */
	public function _queries()
	{	
		$appdir = \INS\Filter::i()->filterString( \INS\Http::i()->dir );
		
		require_once INS_PLUGINS_DIR . DIRECTORY_SEPARATOR . $appdir . DIRECTORY_SEPARATOR . 'install/queries.php';

		$skipped = 0;
		$failed_queries = array();
		
		if(!empty($queries))
		{
			foreach( $queries AS $query )
			{
				if( strpos($query, "SELECT") !== FALSE || strpos($query, "select") !== FALSE )
				{
					$skipped++;
					continue;
				}
				else
				{	
					try
					{
						$statement = \INS\Db::i()->db->prepare($query);
						$statement->execute();
						
						if( \INS\Db::i()->num_rows() == 0 )
						{
							$failed_queries[] = $query;
						}
					}
					catch( \PDOException $e )
					{
						$failed_queries[] = $e;
					}	
				}	
			}
			$num_queries = \INS\Db::i()->num_rows();
		}
		else
		{
			$num_queries = 0;
		}
		
		/* Some queries failed */
		if(!empty($failed_queries))
		{
			$errors = "<ol>";
			foreach($failed_queries as $f)
			{
				$errors .= "<li>Query: {$f}</li>";
			}	
			$errors .= "</ol>";

			file_put_contents( INS_PLUGINS_DIR . DIRECTORY_SEPARATOR . $appdir . DIRECTORY_SEPARATOR . 'errors_queries.html', $errors );
		}
		
		$array = [ 'response' => 'templates', 'responseCode' => '1' ];
        \INS\Json::echoJson( \INS\Language::i()->strings['admin_modules_applications_install_queriesinjected'], $array );
	}
	
	/**
	 * Template creation
	 *
	 * @return		void
	 */
	public function _templates()
	{
		$appdir = \INS\Filter::i()->filterString( \INS\Http::i()->dir );
		
		$num = 0;
        require_once INS_PLUGINS_DIR . DIRECTORY_SEPARATOR . $appdir . DIRECTORY_SEPARATOR . "install/templates.php";

		if( !empty($template ) )
		{
			foreach($template as $group => $array)
			{
				$gid = ( $group == 'admin' ) ? 1 : -1;
				foreach( $array as $name => $_content )
				{
					\INS\Template::i()->insert($name, $_content, $group, $gid);
					$num++;
				}
			}
		}
	
		$array = [ 'response' => 'hooks', 'responseCode' => '1' ];
        \INS\Json::echoJson( \INS\Language::i()->strings['admin_modules_applications_install_templatesinserted'], $array );
	}
	
	/**
	 * Hooks
	 *
	 * @return		void
	 */
	public function _hooks()
	{
		$appdir = \INS\Filter::i()->filterString( \INS\Http::i()->dir );
		$type = ( in_array( $appdir, $coreApps ) ) ? '1' : '2';
		
		require_once INS_PLUGINS_DIR . DIRECTORY_SEPARATOR . $appdir . DIRECTORY_SEPARATOR . "install/hooks.php";
		
		/* Pull application ID of this app */
		$aid = \INS\Db::i()->fetch('aid', 'applications', "`dir`='{$appdir}'");
		$aid = $aid['aid'];
		$num = 0;

		/* Insert hooks, if any */
		if(!empty($hooks))
		{
			foreach($hooks as $hook)
			{
				$hook['aid'] = $aid;
				$hook['apptype'] = $type;
				\INS\Db::i()->insert('inshooks', $hook);
				$num++;
			}
		}
			
		$array = [ 'response' => 'language', 'responseCode' => '1' ];
        \INS\Json::echoJson( \INS\Language::i()->strings['admin_modules_applications_install_hooksinserted'], $array );
	}
	
	/**
	 * Language
	 *
	 * @return		void
	 */
	public function _language()
	{
		$appdir = \INS\Filter::i()->filterString( \INS\Http::i()->dir );
		
		$langfile = INS_PLUGINS_DIR . DIRECTORY_SEPARATOR . $appdir . DIRECTORY_SEPARATOR . "install/language.php";

		if( file_exists( $langfile ) )
		{
			require_once $langfile;
		
			/* Insert hooks, if any */
			if(! empty( $lang ) )
			{
				foreach( $lang AS $langfile => $array )
				{
					\INS\Applications::i()->addLanguageStrings( $array, $appdir, $langfile );
				}
			}
		}
			
		$array = [ 'response' => 'cache', 'responseCode' => '1' ];
        \INS\Json::echoJson( \INS\Language::i()->strings['admin_modules_applications_install_languagestringsinserted'], $array );
	}

	/**
	 * Cache
	 *
	 * @return		void
	 */
	public function _cache()
	{
		$appdir = \INS\Filter::i()->filterString( \INS\Http::i()->dir );
		
		/* Do we have cache to update or insert? */
		if( is_file( INS_PLUGINS_DIR . DIRECTORY_SEPARATOR . $appdir . DIRECTORY_SEPARATOR . "install/cache.php" ) )
		{
			require_once INS_PLUGINS_DIR . DIRECTORY_SEPARATOR . $appdir . DIRECTORY_SEPARATOR . "install/cache.php";

			foreach( $caches as $_cache )
			{
				if( \INS\Cache::i()->read($_cache['key']) === false)
				{
					\INS\Cache::i()->insert( $_cache['key'], $_cache['cache'] );
				}
				else
				{
					\INS\Cache::i()->delete($_cache['key']);
					\INS\Cache::i()->insert( $_cache['key'], $_cache['cache'] );
				}
			}
		}
		
		$array = [ 'response' => 'recache', 'responseCode' => '1' ];
        \INS\Json::echoJson( \INS\Language::i()->strings['admin_modules_applications_install_cacheinserted'], $array );
	}
	
	/**
	 * Re-Cache
	 *
	 * @return		void
	 */
	public function _recache()
	{
		\INS\Cache::i()->recacheApps();
		
		$array = [ 'response' => 'end', 'responseCode' => '1' ];
        \INS\Json::echoJson( \INS\Language::i()->strings['admin_modules_applications_install_recaching'], $array );
	}
	
	/**
	 * End
	 *
	 * @return		void
	 */
	public function _end()
	{
		$array = [ 'response' => 'done', 'responseCode' => '1' ];
        \INS\Json::echoJson( \INS\Language::i()->strings['admin_modules_applications_install_done'], $array );
	}

	/**
	 * @brief 	Ajax Requests
	 */
	public function _ajax()
	{
		if( \INS\Http::i()->verifyAjaxRequest() )
		{
			switch( \INS\Http::i()->step )
			{
				case 'start':
					$this->_start();
					break;
				/* Now we query the database (if any) */	
				case 'queries':
					$this->_queries();
					break;
				/* Insert templates */	
				case 'templates':
					$this->_templates();
					break;
				/* Hooks */	
				case 'hooks':
					$this->_hooks();
					break;	
				/* Hooks */	
				case 'language':
					$this->_language();
					break;
				/* Build cache */	
				case 'cache':
					$this->_cache();
					break;
				/* Recache the stuff */
				case 'recache':
					$this->_recache();
					break;
				/* Finish */
				case 'end':
					$this->_end();
					break;	
				case 'upgrade':
					$this->_upgrade();
					break;	
			}

			exit;
		}
		else
		{
			die( 'Invalid Request.' );
		}
	}
}	
?>	