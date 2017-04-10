<?php
/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.5.0
 * INSCache Class
 * Last Updated: $Date: 2014-10-28 5:35 (Tue, 26 Oct 2014) $
 * </pre>
 * 
 * @author      $Author: AskAmn$
 * @package     IN.CMS
 * @copyright   (c) 2014 Infusion Network Solutions
 * @license     http://www.infusionnetwork/licenses/license.php?view=main&version=2014  Revision License 0.1.1
 * @since       0.5.0
 * @version     Revision: 0510
 */

namespace INS;

class Cache
{
	/**
	 * Main Cache
	 *
	 * @var		array
	 */
	public $cache = array();
	
	/**
	 * Use Db Cache on failure?
	 *
	 * @var		array
	 */
	private $useDbOnFailure = TRUE;
	
	/**
	 * Cache layer
	 *
	 * @var		resource
	 */
	public $handler = NULL;

	/**
	 * Array containing name for our various applications
	 *
	 * @var		resource
	 */
	public $coreAppTitles = array();

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
	 * Constructor
	 *	
	 * @param 	const		Restrictions on data being pulled
	 * @return	boolean
	 */
	public function __construct( $restriction )
	{	
		\INS\Core::checkState( __CLASS__ );

		$useDbOnFailure = $restriction;
		
		if( empty( \INS\Core::$config ) AND empty( \INS\Core::$config['cache']['method'] ))
		{
			return;
		}
		
		$qualifiedName = "\\INS\\Cache\\Handlers\\" . \INS\Core::$config['cache']['method'];
		$this->handler = new $qualifiedName;
		
		// Failure
		if($this->handler->crashed === true)
		{
			die($this->handler->crash_reason);
		}
	}
	
	/**
	 * Read from cache a value identified by $key
	 *
	 * @param 	string 	
	 * @param 	boolean 
	 * @return 	mixed
	 */
	public function read($key, $linkrefresh = false)
	{
		// Already set?
		if(isset($this->cache[$key]) && $linkrefresh === false)
		{
			return @unserialize($this->cache[$key]);
		}

		$data = $this->handler->get($key);

		// Data not returned: Attempt to fetch from database if we are allowed to do so
		if($data === FALSE && $this->useDbOnFailure === TRUE)
		{	
			\INS\ErrorHandler::logErrorToFile( INS_BAD_CACHE, 'cache' );
			
			$row = \INS\Db::i()->fetch('`key`, `cache`', 'inscache', '`key` = ?', '', array( 1 => $key ) );
			$data = @unserialize($row['cache']);
			
			$this->handler->store($key, serialize($data));
		}

		$this->cache[$key] = $data;
		
		return $data;
	}
	
	/**
	 * Updates value in cache
	 * Always pass serialized value to Update method
	 *
	 * @param 	string
	 * @param 	string
	 * @return 	void
	 */
	public function update($key, $data)
	{
		$this->cache[$key] = $data;
		
		$array = array(
			'cache' => $data
		);
		\INS\Db::i()->update( "inscache", $array, "`key` = ?", '', [ 1 => $key ] );

		$this->handler->update($key, $data);
	}
	
	/**
	 * Inserts value in cache
	 *
	 * @param 	string
	 * @param 	string
	 * @return 	void
	 */
	public function insert($key, $data)
	{
		$this->cache[$key] = $data;
		$data = serialize($data);
		
		$rows = \INS\Db::i()->rowCount('inscache', "`key` = '{$key}'");
		if( $rows > 0 )
		{
			$this->update( $key, $data );
		}
		else
		{
			$array = array(
				'key' => $key,
				'cache' => $data
			);
				
			\INS\Db::i()->insert("inscache", $array);
			$this->handler->store($key, $data);
		}
	}
	
	/**
	 * Removes a value from cache
	 *
	 * @param 	string
	 * @param 	string
	 * @return 	void
	 */
	public function delete($key)
	{
		unset( $this->cache[$key] );
		\INS\Db::i()->delete("inscache", "`key` = '{$key}'");
		$this->handler->remove($key);
	}
	
	/**
	 * Update version
	 *
	 * @return 	void
	 */
	public function recacheVersion()
	{
		$array = array('version' => \INS\Core::$version, 'version_code' => \INS\Core::$versionCode);
		$this->insert('version', $array);
	}	
	
	/**
	 * Update Apps Cache
	 *
	 * @return 	void
	 */
	public function recacheApps()
	{
		$array = array();
		$array['active'] = array();

		$rows = \INS\Db::i()->buildQuery( 'select' )
						->columns( 'a.aid, h.aid, h.hid, a.enabled, h.hook_load_position, h.hook_key, h.file, h.apptype, a.dir' )
						->table( 'inshooks h' )
						->leftJoin( 'applications a', 'a.aid=h.aid' )
						->where( 'a.enabled = \'1\'' )
						->complete();

		foreach($rows as $row)
		{
			if(!is_array($array['active'][$row['hook_load_position']]))
			{
				$array['active'][$row['hook_load_position']] = array();
			}	
			if($row['enabled'] == '1')
			{	
				$a['key'] = $row['hook_key'];
				$a['hid'] = $row['hid'];
				$a['file'] = $row['file'];
				$a['type'] = $row['apptype'];
				$a['dir'] = $row['dir'];
				$array['active'][$row['hook_load_position']][] = $a;
			}	
		}	

		$this->insert('appcache', $array);
	}
}	
?>