<?php
/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.5.0
 * Login/Logout Handler Class
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

class login_adminLoginHandler Extends \INS\Admin
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
        $this->title = \INS\Language::i()->strings['admin_modules_login_title'];
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
			case 'ajax': $this->_ajax();
			break;
			case 'logout': $this->_signout();
			break;
			case 'signout': $this->_signout();
			break;
			default: $this->_default();
			break;
		}
	}

	/**
	 * @brief 	Default Request
	 */
	public function _default()
	{
		\INS\Http::i()->r = \INS\Http::i()->url( \INS\Http::i()->r )->strip( 'sessionKey' );
		$rD = isset( \INS\Http::i()->r ) ? urlencode( \INS\Http::i()->r . '&amp;sessionKey=' . \INS\Http::i()->sessionKey ) : 'sessionKey=' . \INS\Http::i()->sessionKey ;

		$redirect = '?' . $rD;
	    $visibility = 'hidden';

	    if ( \INS\Http::i()->do == "do_login" )
		{ 
			$username = \INS\Db::i()->escapeString( \INS\Http::i()->username );
			$password = \INS\Db::i()->escapeString( \INS\Http::i()->password );

			if( ( $member = \INS\Admin::i()->validateData( $username, $password ) ) !== FALSE )
			{
				if( !empty( $rD ) )
				{
					$rD = urldecode( $rD );
				}

				\INS\Session::i()->load( $member );
				\INS\Http::redirect( '/?' . $rD );
			}
			else
			{	
				$visibility = '';
			}
		}

		eval( "\$this->html = \"".\INS\Template::i()->getAcp( "login" )."\";" );
		\INS\Template::i()->output( $this->html, $this->title );
	}

	/**
	 * @brief 	Ajax Requests
	 */
	public function _ajax()
	{
		if( \INS\Http::i()->verifyAjaxRequest() )
		{
			$username = \INS\Db::i()->escapeString( \INS\Http::i()->username );
			$password = \INS\Db::i()->escapeString( \INS\Http::i()->password );

			if( ( $member = \INS\Admin::i()->validateData( $username, $password ) ) !== FALSE )
			{
				\INS\Session::i()->load( $member );
				\INS\Json::_print( 'You are being logged in, please wait' );
			}
			else
			{
				\INS\Json::_error( 'Validation failed' );
			}
		}
		else
		{
			\INS\Json::_error( 'Invalid Request.' );
		}
	}

	/**
	 * @brief 	Logout
	 */
	public function _signout()
	{
		\INS\Users::i()->logout(); 
		\INS\Http::i()->redirect( \INS\Http::i()->buildUrl( 'core', 'login' ) );
	}
}
?>