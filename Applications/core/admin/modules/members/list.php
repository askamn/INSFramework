<?php
/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.5.0
 * Members List
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

class members_list Extends \INS\Admin
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
		\INS\Template::i()->pushToCache( "'members_list_activateuser', 'members_list_banneduser', 'members_list_banuser', 'members_list_usercannotbebanned', 'users_build_list', 'users_view'", \INS\Core::$settings['admin']['theme'] );
		$this->title = \INS\Language::i()->strings['admin_modules_members_list_title'];
		$this->link = \INS\Http::i();
		\INS\Template::i()->addToNavigation( \INS\Language::i()->strings['admin_modules_members_navtitle'], $this->link );
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
			case 'list': $this->_default();
			break;
			case 'edit': $this->_edit();
			break;
			case 'ban': $this->_ban();
			break;	
			case 'unban': $this->_unban();
			break;	
			case 'activate': $this->_activate();
			break;	
			default: $this->_default();
			break;
		}
	}
	
	/**
	 * Overview
	 *
	 * @return		void
	 */
	public function _default()
	{
		if(isset(\INS\Http::i()->sort))
		{
			switch(\INS\Http::i()->sort)
			{
				case 'unactivated': $users_build_list = $this->buildUsersList('unactivated');
				break;
				case 'banned': $users_build_list = $this->buildUsersList('banned');
				break;
				case 'admin': $users_build_list = $this->buildUsersList('admins');
				break;
				case 'staff': $users_build_list = $this->buildUsersList('staff');
				break;
				default: $users_build_list = $this->buildUsersList();
				break;
			}	
		}
		else
		{
			$users_build_list = $this->buildUsersList();
		}
		
		$num = \INS\Db::i()->rowCount('users');
		
		if( !isset( \INS\Http::i()->page ) )
		{
			\INS\Http::i()->page = 1;	
		}
		
		$pagination = \INS\Template::i()->buildPagination( intval( \INS\Http::i()->page ), \INS\Core::$settings['admin']['resultsperpage'], $num, $this->link );
		
		eval("\$this->html = \"".\INS\Template::i()->getAcp("users_view")."\";");
		\INS\Template::i()->output( $this->html, \INS\Language::i()->strings['admin_modules_members_list_title'] );   
	}	
	
	/**
	 * Editing a user
	 *
	 * @return		void
	 */
	public function _edit()
	{
		$user = \INS\Users::i()->fetch( \INS\Http::i()->uid );
				
		if( \INS\Http::i()->request_method == "post" && isset( \INS\Http::i()->submit ) )
		{
			// Fields that can be empty
			$fields = array(
				'facebook' 		=> \INS\Http::i()->facebook,
				'twitter' 		=> \INS\Http::i()->twitter,
				'google-plus' 	=> \INS\Http::i()->google,
			);
			
			// Fields that cannot be empty
			$array = array(
				'username' => '?',
				'email' => '?',
			);

			$binds[1] = \INS\Http::i()->username;
			$binds[2] = \INS\Http::i()->email;
			
			$i = 3;
			foreach( $fields AS $key => $value )
			{
				if( !empty( $value ) )
				{
					$array[ $key ] = '?';
					$binds[ $i++ ] = $value;
				}	
			}
			
			// We only change the password if the field ain't blank
			if( !empty( \INS\Http::i()->password ) )
			{
				$array['password'] = '?';
				$binds[ $i++ ] = \INS\Core::encode( \INS\Http::i()->password );
			}
			
			foreach($array AS $key => $value)
			{
				if($value == "")
				{
					$crash = TRUE;
					$empty[] = "'{$key}'";
				}	
			} 
			
			\INS\File::i()->uploadFormField = 'file';
			\INS\File::i()->maxFileSize = 1024*150; // 150 KB
			\INS\File::i()->useRandomName = TRUE;
			\INS\File::i()->parse_dangerous_scripts = 1;
			\INS\File::i()->allowedExtensions = array('gif', 'jpg', 'jpeg', 'png');
			\INS\File::i()->uploadFileLocation = 'avatars'; 
			\INS\File::i()->Execute();
			
			if( \INS\File::i()->errorString != '' )
			{
				$crash = TRUE;
			}
			
			if($crash === TRUE)
			{
				if(\INS\File::i()->errorString != '')
				{
					$notify = sprintf( '<div class="form-message error">%s</div>', \INS\File::i()->errorString );
				}
				if(is_array($empty))
				{
					$empty = implode(', ', $empty);
					$notify .= sprintf( '<div class="form-message error">%s - %s</div>', \INS\Language::i()->strings['admin_fields_empty'], $empty );
				}
			}		
			else	
			{
				if(\INS\File::i()->uploadBox != FALSE)
				{
					\INS\Users::i()->removeAvatar( \INS\Http::i()->uid );
					$array['avatar'] = '?';
					$binds[ $i++ ] = \INS\File::i()->parsedFileName;
				}	

				$binds[ $i ] = \INS\Http::i()->uid;

				\INS\Db::i()->update( "users", $array, "`uid`= ?", '', $binds);
				\INS\Template::i()->globalNotification = \INS\Language::i()->strings['admin_user_edited'];
			}	
		}

		/* Add this to the navigation */
		\INS\Template::i()->addToNavigation( \INS\Language::i()->strings( 'admin_modules_members_editingmember_navtitle' )->parse( $user->username, $user->uid ), \INS\Http::i()->url( $this->link )->append( [ 'uid' => $user->uid, 'request' => 'edit' ] ) );

		$sociallinks = $user->getSocialLinks();
		$regdate = \INS\Date::i()->convertTimestamp( $user->joindate, \INS\Core::$settings['datetime']['date_format']. " " .\INS\Core::$settings['datetime']['time_format'] );
		$lastlogin = \INS\Date::i()->convertTimestamp( $user->lastlogin, \INS\Core::$settings['datetime']['date_format']. " " .\INS\Core::$settings['datetime']['time_format'] );
		$rank = $user->getGroup();
		$activationstate = ( $user->activation_state == 1 ) ? \INS\Language::i()->strings['activated'] : \INS\Language::i()->strings['not_activated'];

		eval("\$this->html = \"".\INS\Template::i()->getAcp("users_edit")."\";"); 
		\INS\Template::i()->output( $this->html, \INS\Language::i()->strings['admin_modules_members_edit_title'] . ' ' . $user->username );
	}	
	
	/**
	 * Ban page
	 *
	 * @return		void
	 */
	public function _ban()
	{
		$uid = intval( \INS\Http::i()->uid );
		
		/* Can't ban a super admin */
		if( $uid != 1 )
		{
			\INS\Users::i()->changeGroup( $uid, 0 );
		}
		else
		{
			\INS\Template::i()->globalNotification = \INS\Language::i()->strings['admin_members_cannotbebanned'];
		}

		\INS\Template::i()->globalNotification = \INS\Language::i()->strings['admin_members_userbanned'];
		
		if( isset( \INS\Http::i()->sort ) )
		{
			switch(\INS\Http::i()->sort)
			{
				case 'unactivated': 
					$users_build_list = $this->buildUsersList('unactivated');
					break;
				case 'banned': 
					$users_build_list = $this->buildUsersList('banned');
					break;
				case 'admin': 
					$users_build_list = $this->buildUsersList('admins');
					break;
				case 'staff': 
					$users_build_list = $this->buildUsersList('staff');
					break;
				default: 
					$users_build_list = $this->buildUsersList();
					break;
			}	
		}
		else
		{
			$users_build_list = $this->buildUsersList();
		}	

		eval("\$this->html = \"".\INS\Template::i()->getAcp("users_view")."\";");   
		\INS\Template::i()->output( $this->html, $this->title );
	}	
	
	/**
     * Activate page
	 *
	 * @return		void
	 */
	public function _activate()
	{
		$uid = intval( \INS\Http::i()->uid );
		
		\INS\Db::i()->update( 'users', ['activation_state' => '1'], '`uid` = ?', '', [ 1 => $uid ]);
		\INS\Template::i()->globalNotification = \INS\Language::i()->strings['admin_members_useractivation_success'];

		if(isset(\INS\Http::i()->sort))
		{
			switch(\INS\Http::i()->sort)
			{
				case 'unactivated': 
					$users_build_list = $this->buildUsersList('unactivated');
					break;
				case 'banned': 
					$users_build_list = $this->buildUsersList('banned');
					break;
				case 'admin': 
					$users_build_list = $this->buildUsersList('admins');
					break;
				case 'staff': 
					$users_build_list = $this->buildUsersList('staff');
					break;
				default: 
					$users_build_list = $this->buildUsersList();
					break;
			}	
		}
		else
		{
			$users_build_list = $this->buildUsersList();
		}	

		eval("\$this->html = \"".\INS\Template::i()->getAcp("users_view")."\";");  
		\INS\Template::i()->output( $this->html, $this->title ); 
	}	
	
	/**
	 * Un-Ban page
	 *
	 * @return		void
	 */
	public function _unban()
	{
		$uid = intval( \INS\Http::i()->uid );
		$user->changeGroup($uid, 2); /* 0: is the Banned group ID */
		
		\INS\Template::i()->globalNotification = \INS\Language::i()->strings['admin_members_userunbanned'];
		if( isset( \INS\Http::i()->sort ) )
		{
			switch( \INS\Http::i()->sort )
			{
				case 'unactivated': 
					$users_build_list = $this->buildUsersList('unactivated');
					break;
				case 'banned': 
					$users_build_list = $this->buildUsersList('banned');
					break;
				case 'admin': 	
					$users_build_list = $this->buildUsersList('admins');
					break;
				case 'staff': 
					$users_build_list = $this->buildUsersList('staff');
					break;
				default: 
					$users_build_list = $this->buildUsersList();
					break;
			}	
		}
		else
		{
			$users_build_list = $this->buildUsersList();
		}	
		eval("\$this->html = \"".\INS\Template::i()->getAcp("users_view")."\";");   
		\INS\Template::i()->output( $this->html, $this->title ); 
	}	
	
	/**
	 * Builds current list of users
	 *
	 * @return		string
	 */
	public function buildUsersList($sortby = '') 
	{
		$page = !isset( \INS\Http::i()->page ) ? 1 : intval( \INS\Http::i()->page );
	   
		$limit = $page * \INS\Core::$settings['admin']['resultsperpage'];
		$_limit = $limit - \INS\Core::$settings['admin']['resultsperpage'];
		$limit = \INS\Core::$settings['admin']['resultsperpage'];
	   
		if($sortby != '')
		{
			switch ($sortby)
			{
				case 'unactivated': 
					$rows = \INS\Db::i()->fetchAll( '*', 'users', '`activation_state` = \'0\'', '', "{$_limit}, {$limit}" );
				break;
				case 'banned':
					$rows = \INS\Db::i()->fetchAll( '*', 'users', '`group` = \'1\'', '', "{$_limit}, {$limit}" );
				break;
				case 'admins':
					$rows = \INS\Db::i()->fetchAll( '*', 'users', '`group` = \'2\'', '', "{$_limit}, {$limit}" );
				break;
				case 'staff':
					$rows = \INS\Db::i()->fetchAll( '*', 'users', '`group` = \'4\'', '', "{$_limit}, {$limit}" );
				break;
			}
	    }
		else
		{
			$rows = \INS\Db::i()->fetchAll( '*', 'users', '', "{$_limit}, {$limit}" );
		}

		foreach($rows AS $row)
		{
			if( $row['activation_state'] == '0' )
				eval("\$activate = \"".\INS\Template::i()->getAcp("members_list_activateuser")."\";");
			else
				$activate = '';

			if($row['uid'] != 1)
			{
				/* User is banned */
				if($row['group'] == 1)
					eval("\$userban = \"".\INS\Template::i()->getAcp("members_list_banneduser")."\";");
				else
					eval("\$userban = \"".\INS\Template::i()->getAcp("members_list_banuser")."\";");
			}
			else
			{
				eval("\$userban = \"".\INS\Template::i()->getAcp("members_list_usercannotbebanned")."\";");
			}	
			
			eval("\$userlist .= \"".\INS\Template::i()->getAcp("users_build_list")."\";");
		}	
		
		return $userlist;
	}
}
?>