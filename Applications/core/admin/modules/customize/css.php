<?php
/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.5.0
 * CSS
 * Last Updated: $Date: 2014-10-28 5:35 (Tue, 26 Oct 2014) $
 * </pre>
 * 
 * @author      $Author: AskAmn$
 * @package     IN.CMS
 * @copyright   (c) 2014 Infusion Network Solutions
 * @license     http://www.infusionnetwork/licenses/license.php?view=main&version=2014  Revision License 0.1.1
 * @since       0.5.0
 * @version     Release: 0510
 */

class customize_css Extends \INS\Admin
{
	/**
	 * The complete link to this module
	 *
	 * @var		string	
	 */
	public $link;
	
	/**
	 * Constructor
	 *
	 * @return 		void
	 */
	public function __construct()
	{
		$this->title = \INS\Language::i()->strings['admin_modules_system_themes_title'];
		$this->link = \INS\Http::i();
		\INS\Template::i()->addToNavigation( \INS\Language::i()->strings['admin_modules_css_navtitle'], $this->link );
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
			case 'list': 
				\INS\Template::i()->addToNavigation( \INS\Language::i()->strings['admin_modules_css_list_navtitle'], $this->link . '&request=' . \INS\Http::i()->request );
				$this->_files();
				break;
			case 'edit': 
				\INS\Template::i()->addToNavigation( \INS\Language::i()->strings['admin_modules_css_edit_navtitle'], $this->link . '&request=' . \INS\Http::i()->request );
				$this->_edit();
				break;
			case 'ajax': 
				$this->_ajax();
				break;	
			default: 
				$this->_default();
				break;
		}
	}

	/**
	 * @brief 	Default Request
	 */
	public function _default()
	{
		$theme_list = $this->generateThemeList( \INS\Http::i()->gid );
		eval( "\$this->html = \"".\INS\Template::i()->getAcp("themes")."\";" );
		\INS\Template::i()->output( $this->html, \INS\Language::i()->strings['admin_modules_customize_themes_title'] );
	}
	
	/**
	 * CSS Files Overview
	 *
	 * @return 		void
	 */
	public function _files()
	{
		$cssfiles = \INS\Css::i()->get();
		$gid = intval( \INS\Http::i()->gid ); 

		foreach($cssfiles AS $file)
		{
			$filestring = urlencode( $file );
			eval("\$files .= \"".\INS\Template::i()->getAcp("css_files")."\";");
		}
			
		eval("\$this->html = \"".\INS\Template::i()->getAcp("css_main")."\";");	
		\INS\Template::i()->output( $this->html, \INS\Language::i()->strings['admin_modules_customize_themes_cssfiles'] );
	}
	
	/**
	 * CSS Editor
	 *
	 * @return 		void
	 */
	public function _edit()
	{
		$themedir = \INS\Css::i()->getThemeName( \INS\Http::i()->gid );
		$name = \INS\Http::i()->name;
		$gid = intval( \INS\Http::i()->gid );

		if( \INS\Http::i()->request_method == 'post' && isset( \INS\Http::i()->submit ) )
		{
			$data = stripslashes( \INS\Http::i()->data );
			file_put_contents( INS_SYSTEM_DIR . "/FrontEnd/Theme/" . $themedir . "/css/{$name}", $data );
			\INS\Template::i()->globalNotification = \INS\Language::i()->strings['admin_modules_customize_css_updated'];
		}
		
		$css = file_get_contents( INS_SYSTEM_DIR . "/FrontEnd/Theme/" . $themedir . "/css/{$name}");
		
		eval("\$this->html = \"".\INS\Template::i()->getAcp("css_editor")."\";");
		\INS\Template::i()->output( $this->html, \INS\Language::i()->strings['admin_modules_customize_themes_editing'] . $name );	  
	}
	
	/**
	 * Theme list
	 * 
	 * @return		string
	 */ 
	public function generateThemeList()
	{
		$rows = \INS\Db::i()->fetchAll('*', 'themes');
		
		foreach ($rows AS $row)
		{ 
			if($row['group'] === 'admin')
			{
				if(!defined("INS_DEV_MODE"))	
				{
					continue;
				}
			}
		
			eval("\$themes_list .= \"".\INS\Template::i()->getAcp("themes_list")."\";");
		}
		
		return $themes_list;
	}

	/**
	 * AJAX Request Handler
	 *
	 * @return 	void
	 */
	public function _ajax()
	{
		if( \INS\Http::i()->verifyAjaxRequest() )
		{
			if( \INS\Http::i()->request_method == 'post' )	
			{	
				$gid 	= intval( \INS\Http::i()->gid );
				$name 	= \INS\Db::i()->escapeString( \INS\Http::i()->name );
				$data 	= stripslashes( \INS\Http::i()->cssdata );
				$themedir = \INS\Css::i()->getThemeName( \INS\Http::i()->gid );

				file_put_contents( INS_SYSTEM_DIR . "/FrontEnd/Theme/" . $themedir ."/css/{$name}", $data );

				\INS\Json::_print( \INS\Language::i()->strings['admin_globalnotification_customize_cssupdated'] );
			}
		}
		else
		{
			\INS\Json::_error( 'Invalid Request.' );
			exit();
		}
	}	
}	
?>