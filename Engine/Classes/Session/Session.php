<?php
/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.5.0
 * One class to secure them all. -> Broken Authentication and Session Management <-
 * Last Updated: $Date: 2015-01-01 5:35 (Tue, 1 Jan 2015) $
 * </pre>
 *
 * <pre>
 * Shoutout to https://wblinks.com/notes/secure-session-management-tips/
 * </pre>
 *
 * <pre>
 * Usage:
 * // foo is the identifier of your session values
 * // It is better to reference your values, with an identifier. Here we have used 'foo'.
 * \INS\Session::i()->set( 'foo.bar', 'FooBar' );
 * \INS\Session::i()->get( 'foo.bar' );
 * </pre>
 * 
 * @author      $Author: AskAmn$
 * @package     IN.CMS
 * @copyright   (c) 2014 Infusion Network Solutions
 * @license     http://www.infusionnetwork/licenses/license.php?view=main&version=2014  Revision License 0.1.1
 * @since       0.5.3
 * @version     Release: 0530
 */

namespace INS;

class Session
{
    /** 
     * Update Session? 
     */
    public static $update = FALSE;

    /**
     * Session identifier
     *
     * @var     string
     */
    protected $key;

    /**
     * Session Name
     *
     * @var     string
     */
    protected $name;

    /**
     * Cookie for storing the sent session value
     *
     * @var     string
     */
    protected $cookie;

    /**
     * Stores session data
     *
     * @var     string
     */
    protected $data;

    /**
     * Useragent Data
     *
     * @var     array
     * @access  public
     */
    public $userAgent = [];

    /** 
     * Loads the instance of this class
     *
     * @return      resource
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
     * __construct
     */
    public function __construct()
    {
        \INS\Core::checkState( __CLASS__ );
    }

    /**
     * Constructor like method
     *
     * @param   string
     * @param   string
     * @param   array
     * @return  void
     */
    public function init( $name = NULL, $cookie = [] )
    {
        $this->key 		= hash( 'sha256', empty( \INS\Core::$config['session_key'] ) ? $this->getPseudoRandomString() : \INS\Core::$config['session_key'], TRUE );
        $this->name 	= 'InsSession_' . ( is_null( $name ) ? \INS\Core::$config['ins_unique_key'] : $name );
        $this->cookie 	= $cookie;

        $this->cookie += [
            'lifetime' => '0',
            'path'     => ( !empty( \INS\Core::$settings['site']['cookie_path'] ) ? \INS\Core::$settings['site']['cookie_path'] : '/' ),
            'domain'   => ( !empty( \INS\Core::$settings['site']['cookie_domain'] ) ? \INS\Core::$settings['site']['cookie_path'] : '' ),
            'secure'   => ( mb_substr( \INS\Core::$settings['site']['url'], 0, 5 ) == 'https' ) ? TRUE : FALSE,
            'httponly' => TRUE
        ];

        $this->setup();
    }

    /**
     * Sets the settings for the session
     *
     * @return  void
     */
    protected function setup()
    {
        ini_set('session.use_cookies', 1);
        ini_set('session.use_only_cookies', 1);

        session_name( $this->name );
        session_write_close();

        session_set_save_handler( 
            array( static::i(), 'open' ), 
            array( static::i(), 'close' ), 
            array( static::i(), 'read' ), 
            array( static::i(), 'write' ),
            array( static::i(), 'destroy' ),
            array( static::i(), 'gc' )
        );

        session_set_cookie_params(
            $this->cookie['lifetime'], 
            $this->cookie['path'],
            $this->cookie['domain'], 
            $this->cookie['secure'],
            $this->cookie['httponly']
        );
        
        $this->start();
        $this->id = session_id();

        register_shutdown_function('session_write_close');
    }

    /**
     * Our session_start() function
     *
     * @return  void
     */
    public function start()
    {
        /* Only start the session if it has been started yet */
        if( session_status() !== PHP_SESSION_ACTIVE )
        {
            if( session_start() === TRUE )
            {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Open
     */
    public function open( $savePath, $sessionName )
    {
        return TRUE;
    }

    /**
     * Close
     */
    public function close()
    {
        return TRUE;
    }

    /**
     * Our session_destroy() function
     */
    public function destroy( $sid )
    {
        $table = ( defined( 'IN_ACP' ) AND IN_ACP === TRUE ) ? 'admin_sessions' : 'user_sessions';
        \INS\Db::i()->delete( $table, 'sid = ?', [ 1 => $sid ]  );
        return TRUE; 
    }  

    /**
     * Cleaner
     */
    public function gc( $maxlifetime )
    {
        $table = ( defined( 'IN_ACP' ) AND IN_ACP === TRUE ) ? 'admin_sessions' : 'user_sessions';
        \INS\Db::i()->delete( $table, 'dateline < ?', [ 1 => $maxlifetime ] );
        return TRUE;
    }

    /**
     * Write
     */
    public function write( $sid, $data )
    {
        if( defined( 'IN_ACP' ) AND IN_ACP === TRUE )
        {
            $inserts = [ 
                'uid'       => '0',
                'loginkey'  => ''
            ];

            /* Not having a loginkey? */
            if( empty( \INS\Users::$member->loginkey ) AND \INS\Users::$member->uid )
            {
                \INS\Users::$member->loginkey = md5( uniqid( microtime(), TRUE ) . \INS\Users::$member->uid );
                \INS\Users::$member->save();
            }

            /* If user is logged in */
            if( \INS\Users::$member->uid )
            {
                $inserts = [ 
                    'uid'       => \INS\Users::$member->uid,
                    'loginkey'  => \INS\Users::$member->loginkey,
                ];
            }

            $this->sessionData = array_merge( [
                'sid'           => $sid,
                'ip'            => \INS\Http::i()->getIP(),
                'dateline'      => INS_SCRIPT_TIME + 3600, 
                'useragent'     => \INS\Http::i()->useragent['string'],
                'session_data'  => $data
            ], $inserts );

            \INS\Db::i()->replace( 'admin_sessions', $this->sessionData );
        }

        else
        {
            if( static::$update === TRUE OR $this->sessionData['session_data'] !== $data )
            {
                $inserts = [ 
                    'uid'       => 0,
                    'loginkey'  => '',
                    'usertype'  => \INS\Http::i()->isBot ? 'bot' : 'user',
                ];

                if( \INS\Users::$member->uid )
                {
                    $inserts = [ 
                        'uid'       => \INS\Users::$member->uid,
                        'loginkey'  => \INS\Users::$member->loginkey,
                        'usertype'  => \INS\Http::i()->isBot ? 'bot' : 'user',
                    ];
                }
                
                $this->sessionData = array_merge( [
                    'sid'           => $sid,
                    'ip'            => \INS\Http::i()->getIP(),
                    'dateline'      => INS_SCRIPT_TIME, 
                    'useragent'     => \INS\Http::i()->useragent['string'],
                    'session_data'  => $data,
                    'app'           => INS_APP,
                    'module'        => INS_MODULE,
                    'section'       => INS_SECTION,
                    'extra'         => \INS\Http::i()->urlExtra
                ], $inserts );

                \INS\Db::i()->replace( 'user_sessions', $this->sessionData );
            }
        }
    }

    /**
     * Read
     */
    public function read( $sid )
    {
        if( defined( 'IN_ACP' ) AND IN_ACP === TRUE )
        {
            /* Set the key here */
            \INS\Http::i()->sessionKey = $sid;

            $session = \INS\Db::i()->fetch( '*', 'admin_sessions', '`dateline` > ? AND `sid`=? AND `ip`=? AND `useragent`=?', NULL, [ 1=>INS_SCRIPT_TIME, 2=>$sid, 3=>\INS\Http::i()->getIP(), 4=>\INS\Http::i()->useragent[ 'string' ] ] );

            /* A valid session  */
            if( !empty( $session ) )
            {
                /* Validate uid */
                if( $session['uid'] )
                {
                    \INS\Users::$member = \INS\Users::i()->fetch( $session['uid'] );

                    /* Only proceed if the user is an admin */
                    if( \INS\Users::$member->isAdmin() AND  $session['sid'] == \INS\Http::i()->sessionKey )
                    {  
                        return $session['session_data'];       
                    }
                    else
                    { 
                        \INS\Users::$member = NULL;
                    }
                }
            }

            return $session['session_data'];
        }
        else
        {
            if( \INS\Http::i()->isBot )
            {
                $session = \INS\Db::i()->fetch( '*', 'user_sessions', '`ip`=? AND `useragent`=?', NULL, [ 1=>\INS\Http::i()->getIP(), 2=>\INS\Http::i()->useragent[ 'string' ] ] );
                
                /* Make sure this is a bot */
                if( $session['usertype'] == 'bot' )
                {
                    $sid = $session['sid'];
                }
            }

            /* We have the cookies */
            elseif( ( isset( \INS\Http::i()->cookies['uid'] ) AND !empty( \INS\Http::i()->cookies['uid'] ) ) AND ( isset( \INS\Http::i()->cookies['key'] ) AND !empty( \INS\Http::i()->cookies['key'] ) ) )
            {
                $session = \INS\Db::i()->fetch( '*', 'user_sessions', '`sid`=? AND `useragent`=?', NULL, [ 1=>$sid, 2=>\INS\Http::i()->useragent[ 'string' ] ] );
                
                /* We have a session */
                if( !empty( $session ) )
                {
                    /* Validate uid */
                    if( $session['uid'] )
                    {
                        /* IP match if we are to force ip check on login */
                        if( \INS\Core::$settings['users']['force_ip_check_on_login'] == '1' AND \INS\Http::i()->getIP() != $session['ip'] )
                        {
                            $session = [];
                        }
                    }
                }
            }   

            /* We still have a session, that means we are good to go */
            if( !empty( $session ) )
            {
                /* Update the session if the user is online for more than 20 seconds than his last updated time */
                if( $session['dateline'] < ( INS_SCRIPT_TIME - 20 ) )
                {
                    static::$update = TRUE;
                }

                /* Load user */
                if( $session['uid'] )
                {
                    /* Load the member */
                    \INS\Users::$member = \INS\Users::i()->fetch( $session['uid'] );

                    /* Just in-case, verify the login key too */
                    if( \INS\Users::$member->loginkey !== \INS\Http::i()->cookies['key'] )
                    {
                        \INS\Users::$member = new \INS\Users;
                    }
                    else
                    {
                        /* Not having a loginkey? */
                        if( empty( \INS\Users::$member->loginkey ) AND \INS\Users::$member->uid )
                        {
                            \INS\Users::$member->loginkey = md5( uniqid( microtime(), TRUE ) . \INS\Users::$member->uid );
                            \INS\Users::$member->save();
                        }

                        \INS\Http::i()->setCookie( 'uid', \INS\Users::$member->uid, \INS\Date::i()->setTime( '7 days' ) );
                        \INS\Http::i()->setCookie( 'key', \INS\Users::$member->loginkey, \INS\Date::i()->setTime( '7 days' ) );
                    }
                }
                /* Load guest */
                else
                {
                    /* Load the member */
                    \INS\Users::$member = new \INS\Users;
                }
            }
            else
            {
                \INS\Users::$member = new \INS\Users;
            }

            return $session['session_data']; 
        }

        /* If we are here then no valid session exists */
        \INS\Users::$member = new \INS\Users;
        return '';
    }

    /** 
     * Load member if he has logged in
     */
    public function load( $member )
    {
        \INS\Users::$member = \INS\Users::i()->fetch( $member['uid'] );

        /* Update the uid in ACP */
        if( defined( 'IN_ACP' ) AND IN_ACP === TRUE )
        {
            $_SESSION['sid'] = \INS\Http::i()->sessionKey;
            //\INS\Db::i()->update( 'admin_sessions', [ 'uid' => \INS\Users::$member->uid ], [ 'sid' => \INS\Http::i()->sessionKey ] );
        }
    }

    /**
     * Removes the contents of '$_SESSION'. Our session_destroy() function
     *
     * @return  void
     */
    public function forget()
    {
        /* No session? */
        if ( session_status() !== PHP_SESSION_ACTIVE ) 
        {
            return FALSE;
        }

        $_SESSION = [];

        setcookie(
            $this->name, '', time() - 42000,
            $this->cookie['path'], $this->cookie['domain'],
            $this->cookie['secure'], $this->cookie['httponly']
        );

        return session_destroy();
    }    

    /**
     * Replaces session identifier
     *
     * @return  void
     */
    public function refresh()
    {
        session_regenerate_id( TRUE );
    }

    /**
     * Expired?
     *
     * @param   int     $ttl    Time in minutes.
     * @return  boolean
     */
    public function isExpired( $ttl = 30 )
    {
        $activity = isset( $_SESSION['_last_activity'] ) ? $_SESSION['_last_activity'] : FALSE;

        /* We have the last_activity key, check if it is in our range */
        if ( $activity !== FALSE AND ( time() - $activity ) > ( $ttl * 60 ) ) 
        {
            return TRUE;
        }

        /* Reset */
        $_SESSION['_last_activity'] = time();

        return FALSE;
    }

    /**
     * Check session fingerprint
     *
     *  @return     boolean
     */
    public function isFingerprint()
    {
        $hash = md5( \INS\Http::i()->useragent[ 'string' ] . ( ip2long( $_SERVER['REMOTE_ADDR'] ) & ip2long('255.255.0.0') ) );

        if ( isset( $_SESSION['_fingerprint'] ) ) 
        {
            return ( $_SESSION['_fingerprint'] === $hash );
        }

        $_SESSION['_fingerprint'] = $hash;

        return TRUE;
    }

    /**
     * Checks for a valid session
     *
     *  @return     boolean
     */
    public function isValid( $ttl = 30 )
    {
        /* Not expired & we have fingerprint */
        if( !$this->isExpired( $ttl ) AND $this->isFingerprint() )
        {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Gets a value from session
     *
     * @param   mixed       $key
     * @param   mixed       $value
     */
    public function get( $key )
    {
        $parsed = explode( '.', $key );

        if( is_array( $parsed ) )
        {
            $key = implode( '', $parsed );

            if( isset( $_SESSION[ $key ] ) ) 
            {
                $result = $_SESSION[ $key ];
            }
            else
            {
                return FALSE;
            }
        }

        return $result;
    }

    /**
     * Sets a specific value to a specific key of the session
     *
     * @param   mixed       $key
     * @param   mixed       $value
     */
    public function set( $key, $value )
    {
        $parsed = explode( '.', $key );

        if( is_array( $parsed ) )
        {
            $key = implode( '', $parsed );
            if ( !isset( $_SESSION[ $key ] ) OR !is_array( $_SESSION[ $key ] ) )
            {
                $_SESSION[ $key ] = [];
            }
        }

        $_SESSION[ $key ] = $value;
    }

    /**
     *
     *
     *
     */
    public function getPseudoRandomString()
    {
    	$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    	$string = '';

    	for( $i = 0; $i < 62; $i++ )
    	{
    		$string .= $characters[ rand( 0, 61 ) ]; 
    	} 

    	return $string;
    }
}
?>