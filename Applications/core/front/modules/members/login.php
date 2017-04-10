<?php
/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.6.0
 * Members: Register
 * Last Updated: $Date: 2015-03-01 4:10:16 (Sun, 1 Mar 2015) $
 * </pre>
 * 
 * @author 		$Author: AskAmn$
 * @copyright	(c) 2014 Infusion Network Solutions
 * @license		http://www.infusionnetwork/licenses/license.php?view=main&version=2014
 * @package		IN.CMS
 * @since 		0.6.1; 22 August 2014
 */
namespace INS\core\front\modules\members;

class members_login
{
	/**
	 * The complete link to this module
	 *
	 * @var		string	
	 */
	public $link;

	/**
	 * Errors
	 *
	 * @var		string	
	 */
	public $errors = NULL;
	
	/**
	 * Constructor
	 *
	 * @return 		void
	 */
	public function __construct()
	{
		\INS\Language::i()->load( 'members' );

		if( !IS_AJAX )
		{
			\INS\Template::i()->pushToCache( "'header', 'footer', 'sidebar', 'members_login', 'headerinclude'", \INS\Core::$settings['theme']['tid'] );
		}

		$this->title = \INS\Language::i()->strings['members_login_title'];
		$this->link = \INS\Http::i();
	}

	/**
	 * Main Entry Point
	 *
	 * @return 		void
	 * @access  	public
	 */
	public function Execute()
	{
		switch( \INS\Http::i()->request )
		{
			case 'register': $this->_Register();
			break;
			case 'ajax':	$this->_Ajax();
			break;
			default: $this->_Main();
		}
	}

	/**
	 * Main
	 *
	 * @return 		void
	 * @access  	public
	 */
	public function _Main()
	{
		//\INS\Template::i()->addNotification( \INS\Language::i()->strings['members_login_failed'] );

		if( \INS\Users::$loggedIn === TRUE )
		{
			\INS\Template::i()->error( \INS\Language::i()->strings['ins_errors_global_useralreadyloggedin'] );
		}

		/* No login handler set? Assume default */
		if( !isset( \INS\Http::i()->handler ) )
		{
			$handler = 'default';
		}

		$form = \INS\Login::i( $handler )->getHandlerForm();

		eval( "\$this->html = \"" . \INS\Template::i()->get('members_login') . "\";" );
		\INS\Template::i()->output( $this->html, $this->title );
	}
}
?>