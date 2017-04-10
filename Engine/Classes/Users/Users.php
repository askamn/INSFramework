<?php
/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.5.0
 * Users
 * Last Updated: $Date: 2014-10-28 5:35 (Tue, 26 Oct 2014) $
 * </pre>
 * 
 * @author      $Author: AskAmn$
 * @package     IN.CMS
 * @copyright   (c) 2014 - SVN_YYYY Infusion Network Solutions
 * @license     http://www.infusionnetwork/licenses/license.php?view=main&version=2014  Revision License 0.1.1
 * @since       0.5.0
 * @version     SVN_VERSION_NUMBER
 */

namespace INS;

class Users extends \INS\Ins\Patterns\ActiveRecord
{
	/**
	 * Default avatar file name
	 */
	const DEFAULT_AVATAR = 'default.png';

	/**
	 * @var 	boolean User logged in?
	 */
	static public $loggedIn = FALSE;

	/**
	 * @var   object 	Contains information about a loggen in member
	 */
	static public $member = NULL;

	/**
	 * List of values that need not be fetched
	 *
	 * @var		array
	 */
	private $fieldList = array(
		'username', 
		'password',
		'email',
		'userrole',
		'avatar',
		'googleplus',
		'twitter',
		'facebook',
		'activation_state',
		'activation_code',
		'regip',
		'joindate',
		'lastlogin',
		'lastloginip'
	); 

	/**
	 * @var		array 	The data store
	 */
	static public $store = array();

	/**
	 * @var		array 	Table
	 */
	static public $table = 'users';

	/**
	 * @var		array 	Column Id
	 */
	static public $idField = 'uid';

	/** 
	 * Loads the instance of this class
	 *
	 * @return 		resource
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
	 * Initialises Stuff
	 *
	 * @var		array
	 */ 
    public function init()
    {  	
    	if( static::$member !== NULL )
    	{ 
    		if( static::$member->uid )
    		{
    			static::$loggedIn = TRUE;
    		}	
    	}
 	}   	

 	/**
 	 * Load a Record
 	 */ 
 	static public function fetch( $id, $idField = NULL, $where = NULL, $binds = NULL )
 	{
 		if( empty( $id ) OR $id == 0 )
 		{
 			$class = get_called_class();
 			$instance = new $class;
 			return $instance;
 		}
 		else
 		{
 			return parent::fetch( $id, $idField, $where, $binds );
 		}
 	}

 	/**
 	 * Load a Default Anonymous Record
 	 */ 
 	public function loadAnonymousData( )
 	{
 		$this->uid 			= 0;
 		$this->username 	= \INS\Language::i()->strings[ 'user_guest' ];
 		$this->lastloginip 	= \INS\Http::i()->getIP();
 		$this->joindate 	= INS_SCRIPT_TIME;
 		$this->userrole 	= \INS\Core::$settings['users']['guest_group'];
 		$this->language     = ( !empty( \INS\Http::i()->cookies['language'] ) AND isset( \INS\Http::i()->cookies['language'] ) ) ? \INS\Http::i()->cookies['language'] : 'en-US';
 		$this->defaulttheme = ( !empty( \INS\Http::i()->cookies['theme'] ) AND isset( \INS\Http::i()->cookies['theme'] ) ) ? \INS\Http::i()->cookies['theme'] : \INS\Core::$settings['theme']['tid'];
 	}
	
	/**
	 * Validates a users login details
	 *
	 * @param 	string
	 * @param	string
	 * @return	boolean
	 */
	public function validate( $username, $password )
    { 
    	$member = \INS\Db::i()->fetch( '*', 'users', '`username` = ?', NULL, [ 1 => $username ] );

    	/* Username does not exist */
    	if( empty( $member ) )
    	{
    		$this->last_error = 'username_not_found';
    		return FALSE;
    	}

    	if( \INS\Core::encode( $password, $member['hash'] ) === $member['password'] )
    	{
    		return $member;
    	}
		
		$this->last_error = 'auth_failed';
		return FALSE;
    }
	
	/**
	 * Logs IP Address of a user
	 *
	 * @return		void
	 */
	public function logIP()
	{
		$this->lastloginip = \INS\Http::i()->getIP();
		$this->save();
	}
	
    /**
	 * Activates a user
	 *
	 * @return 	boolean
	 */
	public function activate($code)
	{
		$row = \INS\Db::i()->fetch('`activation_state`', 'users', '`activation_code` = ?', '', array( 1 => $code ));
		if($row['activation_state'] == 1)		
		{
			return NULL;
		}
		if(\INS\Db::i()->num_rows() != 1)
		{
			return FALSE;
		}
		else
		{
			\INS\Db::i()->update( 'users', [ 'activation_state' => '1' ], '`activation_code`=?', '', [ 1 => $code ] );
			return TRUE;
		}	
	}
	
	/**
	 * Changes a user's group
	 *
	 * @return 	void
	 */
	public function changeGroup($uid, $gid)
	{	
		\INS\Db::i()->update( 'users', array( 'userrole' => '?' ), '`uid` = ?', '', array( 1 => $gid, 2 => $uid ) );
	}
	
	/**
	 * Get's a users details (for admin CP)
	 *
	 * @return 	void
	 */
	public function get( $where, $value )
	{	
		if( \INS\Db::i()->rowCount( 'users', "`{$where}` = '{$value}'" ) == 1 )
		{
			$this->user = \INS\Db::i()->fetch('*', 'users', "`{$where}` = '{$value}'");
			$this->user['joindate_date'] = \INS\Date::i()->convertTimestamp( $this->user['joindate'], \INS\Core::$settings['datetime']['date_format'] );
			$this->user['group_name'] = $this->getGroup( $this->user['userrole'] );
			$this->user['socialbuttons'] = $this->getSocialLinks();
			$this->user['lastlogin'] = \INS\Date::i()->convertTimestamp($this->user['lastlogin'], \INS\Core::$settings['datetime']['date_format']. " " .\INS\Core::$settings['datetime']['time_format']);
			
			if($this->user['activation_state'] == 1)
			{
				$this->user['state'] = \INS\Language::i()->strings['activated'];
			}	
			else
			{
				$this->user['state'] = \INS\Language::i()->strings['not_activated'];
			}	

			return $this->user;
		}	
		else
		{
			return FALSE;
		}
	}
	
	/**
	 * Gets a users social links
	 *
	 * @return 	void
	 */
	public function getSocialLinks( $uid = NULL )
	{
		$this->uid = is_null( $uid ) ? $this->uid : $uid;
		
		$row = \INS\Db::i()->fetch( '`facebook`,`twitter`,`googleplus`', 'users', '`uid` = ?', '', array( 1 => $this->uid ) );
		$data = '';

		foreach( $row AS $name => $value )
		{
			if( empty( $value ) )
			{
				continue;
			}

			$parsedName = \INS\Language::i()->strings['admin_members_socialsites_' . $name . '_name'];

			if( $name == 'googleplus' )
			{
				$name = 'google-plus';
			}

			$data .= \INS\Template::i()->getTemplate( 'core', 'members', 'profile_sociallinks' )->sprintf( $value, $parsedName, $name );
		}

		return ( !empty( $data ) ? $data : \INS\Language::i()->strings['admin_no_social_links'] );
	}
	
	/**
	 * Get's a users Group
	 *
	 * @return 	void
	 */
	public function getGroup()
	{
		return \INS\Db::i()->f( 'name', 'usergroups', '`gid` = ?', '', array( 1 => $this->userrole ) )->get();
	}
	
	/**
	 * Get's a users details
	 *
	 * @param   integer 		UID of the user
	 * @param   array           Array of userfields to be updated
	 * @return 	void
	 */
	public function update( $uid, $array, $binds = NULL )
	{		
		$binds = ( $binds !== NULL ) ? $binds[ count( $binds ) + 1 ] = $uid : [ 1 => $uid ]; 
		\INS\Db::i()->update( 'users', $array, '`uid`=?', '', $binds );
	}
	
	/**
	 * Removes a user's avatar & also deletes the avatar file
	 *
	 * @param   integer     	UID of the user
	 * @return 	void
	 */
	public function removeAvatar( $uid )
	{
		$avatar = \INS\Db::i()->f( '`avatar`', 'users', '`uid` = ?', '', array( 1=> $uid ) )->get();
		@unlink(INS_ROOT."/uploads/avatars/{$avatar}");	
	}
	
	/**
	 * Converts a given date to Age
	 *
	 * @param   date 		Birthdate of the user
	 * @return 	boolean
	 */
	public function getAge( $birthdate = '0000-00-00' ) 
	{
		if ($birthdate == '0000-00-00') 
		{
			return;
		}

		$bits = explode('-', $birthdate);
		$age = date('Y') - $bits[0] - 1;

		$arr[1] = 'm';
		$arr[2] = 'd';

		for ($i = 1; $arr[$i]; $i++) 
		{
			$n = date($arr[$i]);
			if ($n < $bits[$i])
			{
				break;
			}
			if ($n > $bits[$i]) 
			{
				++$age;
				break;
			}
		}

		return $age;
	}
	
	/**
	 * Logs a user out
	 *
	 * @return 	boolean
	 */
	public function logout()
	{	
		/* Delete session if Admin */
		if( defined("IN_ACP") && IN_ACP == 1 )
		{
			\INS\Db::i()->delete( 'admin_sessions', '`uid`=?', [ 1 => $this->uid ] );
		}
		else
		{
			\INS\Db::i()->delete( 'user_sessions', '`uid`=?', [ 1 => $this->uid ] );
		}	

		\INS\Session::i()->forget();
	}
	
	/**
	 * Checks if a user is banned
	 *
	 * @return 		boolean	
	 */
	public function isBanned()
	{	
		return ( $this->userrole == 1 ) ? TRUE : FALSE;
	}

	/**
	 * Checks if a user is an administrator
	 *
	 * @return 		boolean	
	 */
	public function isAdmin()
	{	
		return ( $this->userrole == 2 ) ? TRUE : FALSE;
	}

	/**
	 * Converts uid into username
	 *
	 * @param 	int
	 * @return 	string
	 */
	public function getUsernameByUID( $uid )
	{
		return \INS\Db::i()->f( '`username`', 'users', '`uid`= ?', '', array( 1 => $uid ) )->get();
	} 

	/**
	 * Converts username to uid
	 *
	 * @param 	int
	 * @return 	string
	 */
	public function getUidByUsername( $username )
	{
		return \INS\Db::i()->f( '`uid`', 'users', '`username` = ?', '', array( 1 => $username ) )->get();
	} 
   
    /**
     * Generates a random activation code
     *
     * @return 		void
     * @access 		public
     */
	public function generateActivationCode( )
	{
		return sha1( uniqid( mt_rand(1,9) * 99999 ) );
	}   

	/**
     * Generates an SEO friendly username identifier
     *
     * @return 		void
     * @access 		public
     */
	public function generateUsernameIdentifer( )
	{
		return preg_replace( "/[^A-Za-z0-9 ]/", '', $this->data['username'] );
	}

	/**
     * Generates a random activation code
     *
     * @return 		void
     * @access 		public
     */
	public function createSession( )
	{
		\INS\Http::i()->setCookie( 'uid', \INS\Db::i()->lastInsertId( 'uid' ), \INS\Date::i()->setTime( '7 days' ) );
		\INS\Http::i()->setCookie( 'key', md5( uniqid( microtime(), TRUE ) ), \INS\Date::i()->setTime( '7 days' ) );

		$this->uid = \INS\Http::i()->cookies[ 'uid' ];
		$this->sid = \INS\Http::i()->cookies[ 'key' ];

		/* Delete any previous sessions with the same ip */
		if( $this->newRegistration === FALSE )
		{
			\INS\Db::i()->delete( 'user_sessions', 'ip = ? AND sid != ?', [ 1 => \INS\Http::i()->getIP(), $this->sid ] );
		}

		/* Create the session with new ip */
		\INS\Db::i()->insert( 'user_sessions', 
			[ 
				'sid' 		=> '?', 
				'ip' 		=> '?', 
				'dateline' 	=> '?', 
				'uid' 		=> '?',
				'useragent' => '?',
				'app' 		=> 'core',
				'module' 	=> 'members',
				'section' 	=> 'register',
				'extra'		=> ''
			], 
			[ 
				1 => $this->sid, 
				2 => \INS\Http::i()->getIP(), 
				3 => INS_SCRIPT_TIME, 
				4 => $this->uid,
				5 => \INS\Http::i()->useragent['string'],
			] 
		);

		\INS\Db::i()->update( 'users', [ 'lastlogin' => '?', 'lastloginip' => '?' ],  '`uid` = ?', '', [ 1 => INS_SCRIPT_TIME, 2 => \INS\Http::i()->getIP(), 3 => $this->uid ] );
	}  

	/**
     * Generates a random activation code
     *
     * @return 		void
     * @access 		public
     */
	public function checkValidSession()
	{
		/*if( \INS\Http::i()->cookies['uid'] )
		{
			if( \INS\Http::i()->cookies['key'] )
			{
				try
				{
					if( \INS\Db::i()->f( 'COUNT(*)', 'user_sessions', '`uid`= ? AND `sid`= ?', NULL, [ 1 =>\INS\Http::i()->cookies['uid'], 2 => \INS\Http::i()->cookies['key'] ] )->get() == 1 )
					{
						// Update session
						static::$loggedIn = TRUE;
						return TRUE;
					}
				}
				catch( \Exception $e )
				{
					return FALSE;
				}
			}
			else
			{
				return FALSE;
			}
		}*/

		return FALSE;
	}
}
?>