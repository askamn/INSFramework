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

class applications_applications Extends \INS\Admin
{
	/**
	 * Output holder
	 *
	 * @var 		string
	 * @access 		protected
	 */
	protected $html;

    /**
     * Constructor
     *
     * @return 		void
     */
    public function __construct()
    {
        $this->title = \INS\Language::i()->strings['admin_modules_applications_main_title'];
        $this->link = \INS\Core::$settings['site']['url'] . '/' . \INS\Core::$config['admin']['dir'] . '/index.php?app=' . INS_APP . '&module=' . INS_MODULE . '&section=' . INS_SECTION;
    	\INS\Template::i()->addToNavigation( \INS\Language::i()->strings['admin_modules_applications_navtitle'], $this->link );
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
			case 'details': 
				\INS\Template::i()->addToNavigation( \INS\Language::i()->strings['admin_modules_applications_appdetails_navtitle'], $this->link . '&request=details&id=' . \INS\Http::i()->id );
				$this->_details();
			break;
			default: 
				$this->_default();
			break;
		}

		if( isset( \INS\Http::i()->completed ) )
		{
			switch( \INS\Http::i()->completed )
			{
				case 'deactivate': 
					\INS\Template::i()->globalNotification = \INS\Language::i()->strings['admin_modules_applications_appdeactivatesuccessful'];
					break;
				case 'activate': 
					\INS\Template::i()->globalNotification = \INS\Language::i()->strings['admin_modules_applications_appactivatesuccessful'];
					break;
				case 'uninstall': 
					\INS\Template::i()->globalNotification = \INS\Language::i()->strings['admin_modules_applications_appdeuninstallsuccessful'];
					break;
				case 'uninstallfailed': 
					\INS\Template::i()->globalNotification = \INS\Language::i()->strings['admin_modules_applications_appuninstallfailed'];
					break;
			}
		}
	}

	/**
	 * Applications Overview
	 *
	 * @return 		void
	 */
	public function _default()
	{	
		$plugins = \INS\File::i()->readDir( INS_PLUGINS_DIR );
		$installed = array();
		
		/**
		 * Installed applications
		 */
		$installedapps = \INS\Db::i()->fetchAll( '*', 'applications' );
		if( is_array($installedapps) AND !empty($installedapps) )
		{
			foreach($installedapps AS $app)
			{
				$installed[] = $app['dir'];

				if($app['enabled'] == 1)
					eval("\$applist_activated .= \"".\INS\Template::i()->getacp("apps_applist_activated")."\";");
				else
					eval("\$applist_deactivated .= \"".\INS\Template::i()->getacp("apps_applist_deactivated")."\";");
			}
		}
		else
		{
			eval("\$applist_activated .= \"".\INS\Template::i()->getacp("apps_applist_noapps")."\";");
			eval("\$applist_deactivated .= \"".\INS\Template::i()->getacp("apps_applist_noapps")."\";");
		}
		
		/** 
		 * Not yet installed apps, let us pull their XML
		 */
		$applist = '';
		if( is_array( $plugins ) )
		{
			foreach( $plugins AS $plugin )
			{
					\INS\XML::i()->addToParser( INS_PLUGINS_DIR . DIRECTORY_SEPARATOR . $plugin . DIRECTORY_SEPARATOR . "info.xml" );
					\INS\XML::i()->parse();
					/* No need to separate parsedXML as different arrays, since only one array exists */
					$info = \INS\XML::i()->getXMLTag( \INS\XML::i()->parsedXML );
					$info['apptype'] = '';	

					if(!in_array( $plugin, $installed ) )
					{
						$text = \INS\Language::i()->strings['install'];
						eval("\$applist_notinstalled .= \"".\INS\Template::i()->getAcp("apps_applist_notinstalled")."\";");
					}	
					else
					{
						if( \INS\Applications::i()->versionCompare( $plugin, $info['version_code'] ) )
						{
							$text = \INS\Language::i()->strings['upgrade'];
							eval("\$applist_notinstalled .= \"".\INS\Template::i()->getAcp("apps_applist_notinstalled")."\";");
						}
					}
			}
		}
		
		eval("\$this->html .= \"".\INS\Template::i()->getacp("apps_overview")."\";");
		\INS\Template::i()->output( $this->html, $this->title );
	}
	
	/**
	 * An app's details
	 *
	 * @return 		void
	 */
	public function _details()
	{	
		$row = \INS\Db::i()->fetch('*', 'applications', '`aid` = ?', '', [ 1 => intval( \INS\Http::i()->id ) ]);
		$row['installdate'] = \INS\Date::convertTimestamp($row['installdate'], 'l, d M, Y');

		eval("\$this->html .= \"".\INS\Template::i()->getacp("apps_appdetails")."\";");
		\INS\Template::i()->output( $this->html, $this->title );
	}	
}
?>