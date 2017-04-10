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

class applications_uninstall Extends \INS\Admin
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
        $this->title = \INS\Language::i()->strings['admin_modules_applications_install_title'];
        $this->link = \INS\Core::$settings['site']['url'] . '/' . \INS\Core::$config['admin']['dir'] . '/index.php?app=' . INS_APP . '&module=' . INS_MODULE . '&section=' . INS_SECTION;
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
			case 'start':
				$this->_default();
				break;
			case 'deactivate':
				$this->_deactivate();
				break;	
			case 'activate':
				$this->_activate();
				break;		
			default: 	
				$this->_default();
				break;
		}
    } 
	
	/**
	 * Uninstalls an application
	 *
	 * @return 		void
	 */
	public function _default()
	{	
		if( !isset( \INS\Http::i()->id ) OR empty( \INS\Http::i()->id ) )
			\INS\Http::i()->redirect('index.php?app=core&module=applications&section=applications&completed=uninstallfailed');
			
		$failed = FALSE;
		/* Delete details from DB */
		try
		{
			if( \INS\Db::i()->rowCount( 'applications', 'aid = ' . intval( \INS\Http::i()->id ) ) == 0 )
			{
				$failed = TRUE;
			}
			else
			{
				\INS\Db::i()->delete("applications", "`aid` = ?", [ 1 => \INS\Http::i()->id ] );
			}
		}
		catch(Exception $e)
		{
			$failed = TRUE;
		}	
		
		if( !$failed )
		{
			$dir = \INS\Db::i()->f( 'dir', 'applications', 'aid = ?', '', [ 1 => \INS\Http::i()->id ] )->get( 'dir' );

			if( is_file( INS_PLUGINS_DIR . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $dir . '.php' ) )
			{
				require_once( INS_PLUGINS_DIR . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $dir . '.php' );
				$uc = $dir . '_controller';
				$uninstallClass = new $uc;
				$uninstallClass->uninstall();
			}

			\INS\Cache::i()->recacheApps();
			\INS\Http::i()->redirect('index.php?app=core&module=applications&section=applications&completed=uninstallfailed');
		}
		else
			\INS\Http::i()->redirect('index.php?app=core&module=applications&section=applications&completed=uninstall');
	}
	
	/**
	 * Deactivate an application
	 *
	 * @return 		void
	 */
	public function _deactivate()
	{	
		if( is_file( INS_PLUGINS_DIR . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $dir . '.php' ) )
		{
			require_once( INS_PLUGINS_DIR . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $dir . '.php' );
			$uc = $dir . '_controller';
			$uninstallClass = new $uc;
			$uninstallClass->deactivate();
		}
		
		\INS\Db::i()->update( "applications", [ 'enabled' => '0' ], '', "`aid` = ?", [ 1 => \INS\Http::i()->id ] );
		\INS\Http::i()->redirect('index.php?app=core&module=applications&section=applications&completed=deactivate');
	}

	/**
	 * Application: Activate
	 *
	 * @return 		void
	 */
	public function _activate()
	{	
		if( is_file( INS_PLUGINS_DIR . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $dir . '.php' ) )
		{
			require_once( INS_PLUGINS_DIR . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $dir . '.php' );
			$uc = $dir . '_controller';
			$uninstallClass = new $uc;
			$uninstallClass->activate();
		}
		
		\INS\Db::i()->update( "applications", [ 'enabled' => '1' ], '', "`aid` = ?", [ 1 => \INS\Http::i()->id ] );
		\INS\Http::i()->redirect('index.php?app=core&module=applications&section=applications&completed=activate');
	}		
}	
?>