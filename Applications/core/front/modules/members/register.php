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

class members_register
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
		\INS\Language::i()->load("members");

		if( !IS_AJAX )
		{
			\INS\Template::i()->pushToCache( "'header', 'footer', 'sidebar', 'members_register', 'headerinclude'", \INS\Core::$settings['theme']['tid'] );
		}

		$this->title = \INS\Language::i()->strings['members_register_title'];
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
		if( \INS\Users::$loggedIn === TRUE )
		{
			\INS\Template::i()->error( \INS\Language::i()->strings['ins_errors_global_useralreadyregistered'] );
		}
		
		if( \INS\Core::$settings['users']['force_ip_check_on_register'] == 1 AND INS_DEV_MODE === FALSE )
		{
			if( \INS\Db::i()->f( 'COUNT(*)', 'users', 'regip = ?', NULL, [ 1 => \INS\Http::i()->getIP() ] )->get() > 0 )
			{
				\INS\Template::i()->error( \INS\Language::i()->strings['ins_errors_global_useralreadyregistered'] );
			}
		}

		$form = new \INS\Data\Form( 'register', NULL, NULL, [ 'style' => 'width: 100%', /*'ajaxify' => TRUE*/ ], [ 'lang' => 'register' ] );

		/* Build the cache */
		$form->preCacheTemplates( 'textbox', 'password', 'select', 'select_option', 'email', 'checkbox', 'heading' );

		/* Add the form name */
		$form->addHeading( \INS\Language::i()->strings['members_register_form_heading'] );

		/* Build the form fields */
		$form->push( new \INS\Data\Form\Text( 'username', NULL, TRUE,
			[ 
				'minlength' => ( \INS\Core::$settings['members']['username_minlength'] ) ? \INS\Core::$settings['members']['username_minlength'] : 4,
				'maxlength' => ( \INS\Core::$settings['members']['username_maxlength'] ) ? \INS\Core::$settings['members']['username_maxlength'] : 16, 
				'autofocus' => TRUE,
				'icon'		=> 'user'
			], function( $value ) 
			{
				$rows = \INS\Db::i()->f( 'COUNT(*) AS count', 'users', 'username=?', '', [ 1 => $value ] )->get();
				if( $rows > 0 )
				{
					throw new \Exception( 'members_validation_username_already_exists' );
				}
			}
		) );

		/* Password fields */
		$form->push( new \INS\Data\Form\Password( 'password', NULL, TRUE, [ 'icon' => 'lock' ] ) );
		$form->push( new \INS\Data\Form\Password( 'password_confirm', NULL, TRUE, [ 'icon' => 'lock' ], function( $value ){
			if( $value !== \INS\Http::i()->password )
			{
				throw new \Exception( 'members_validation_passwords_do_not_match' );
			}
		} ) );

		/* Email */
		$form->push( new \INS\Data\Form\Email( 'email', NULL, TRUE, 
			[ 
				'icon' => 'envelope', 
				'placeholder' => \INS\Language::i()->strings['members_email_placeholder'] 
			], function( $value ) {
				$rows = \INS\Db::i()->f( 'COUNT(*) AS count', 'users', 'email=?', '', [ 1 => $value ] )->get();
				if( $rows > 0 )
				{
					throw new \Exception( 'members_validation_email_already_exists' );
				}
			}
		) );

		/* Generate a question */
		if( !empty( \INS\Core::$settings['users']['regqa'] ) )
		{
			$questions = explode( PHP_EOL, \INS\Core::$settings['users']['regqa'] );
			$position = rand( 0, count( $questions ) - 1 );
			$question = $questions[ $position ];

			/* Add this position to a field */
			$form->hiddenFields['regqa_pos'] = $position; 

			$form->push( new \INS\Data\Form\Text( 'regqa', NULL, TRUE, [ 'icon' => 'question', 'description' => $question, 'description_top' => TRUE ], function( $value ){
				$answers = explode( PHP_EOL, \INS\Core::$settings['users']['regqa_a'] );
				$answer = $answers[ \INS\Http::i()->regqa_pos ]; /* The correct answers position */
				$answers = explode( '|', mb_strtolower( $answer ) );

				if( !in_array( mb_strtolower( $value ), $answers ) )
				{
					throw new \Exception( 'members_validation_security_question_error' );
				}
			} ) );
		}

		/* Only proceed further if the stuff is valid */
		if( $this->values = $form->validate() )
		{
			$return = static::i()->_Register();

			/* Array? what happened? */
			if( is_array( $return ) )
			{
				if( $return['failed'] === TRUE )
				{
					$message = $return['message'];
				}	
			}
			/* Success */
			elseif( $return instanceof \INS\Users )
			{
				$member = $return;
				$message = \INS\Language::i()->strings( 'members_register_success_message' )->parse( \INS\Core::$settings['site']['url'], \INS\Core::$settings['site']['name'] );
			}

			else
			{
				$message = \INS\Language::i()->strings[ 'members_register_failed_message' ];
			}

			$form = $message;
		}	

		/* Print this form if this is an ajax request */
		if( IS_AJAX )
		{
			\INS\Json::_print( (string)$form );
		}

		eval( "\$this->html = \"" . \INS\Template::i()->get('members_register') . "\";" );
		\INS\Template::i()->output( $this->html, $this->title );
	}

	/**
	 * Performs Registration
	 *
	 * @return 		void
	 * @access  	public
	 */
	public function _Register()
	{
		$member = new \INS\Users;
		$member->newRecord 				= TRUE;
		$member->username 				= $this->values['username'];
		$member->email    				= $this->values['email'];
		$member->activation_code  		= $member->generateActivationCode();
		$member->joindate 				= INS_SCRIPT_TIME;
		$member->regip 					= \INS\Http::i()->getIP();
		$member->activation_state 		= ( \INS\Core::$settings['users']['instant_activation'] == 1 ) ? 1 : 0 ;
		$member->avatar 				= $member->DEFAULT_AVATAR;
		$member->userrole 				= \INS\Core::$settings['users']['default_registered_users_group'];
		$member->lastlogin 				= '0';
		$member->lastloginip 			= \INS\Http::i()->getIP();
		$member->facebook 				= '';
		$member->twitter 				= '';
		$member->googleplus 			= '';
		$member->hash 					= \INS\Core::generateSalt();
		$member->password				= \INS\Core::encode( $this->values['password'], $member->hash );
		$member->username_identifier    = $member->generateUsernameIdentifer();

		$member->save();
		$member->createSession();

		if( !$member->activation_state )
		{
			\INS\Language::i()->load("mail");
			\INS\Language::i()->activation_message = \INS\Language::i()->parse(
														\INS\Language::i()->strings['activation_message'], 
														\INS\Language::i()->strings['activation_subject'], 
														$member->username, 
														\INS\Core::$settings['site']['url'], 
														$member->activation_code, 
														\INS\Core::$settings['site']['name']
													);
			
			\INS\Mail::i( $member->email, \INS\Language::i()->strings['activation_subject'], \INS\Language::i()->strings['activation_message'], TRUE );
			
			if( !\INS\Mail::i()->send() )
			{
			    return [ 'code' => 1, 'message' => \INS\Language::i()->strings['activation_mail_not_sent'], 'failed' => TRUE ];
			}
		}
		
		return $member;
	}

	/**
	 * Ajax Requests
	 *
	 * @return 		void
	 * @access  	public
	 */
	public function _Ajax()
	{
		if( \INS\Http::i()->verifyAjaxRequest() )
		{
			$this->_Main();
		}
		else
		{
			\INS\Json::_error( 'Invalid request.' );
		}
	}
}
?>