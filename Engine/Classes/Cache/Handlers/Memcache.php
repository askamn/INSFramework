<?php
/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.5.0
 * INSMemcache Cache Handler Class
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

namespace INS\Cache\Handlers;

class Memcache implements \INS\Cache\InterfaceCache
{
	/**
	 * Identifier
	 *
	 * @var		string
	 */
	protected $identifier = '';
	
	/**
	 * Connection resource of MemCache class
	 *
	 * @var		resource
	 */
	protected $link	= null;
	
	/**
	 * Crash status of connection
	 *
	 * @var		string
	 */
	public $crashed;
	
	/**
	 * Reason of Crash
	 *
	 * @var		string
	 */
	public $crash_reason;
	
    /**
	 * Constructor
	 *	
	 * @return	boolean
	 */
	public function __construct()
	{
		$this->config   =& \INS\Core::Config();
		$this->Db       =  \INS\Core::Db();
		
		// Memcache Extension check
		if( !class_exists( 'Memcache' ) )
		{
			$this->crashed = TRUE;
			$this->crash_reason = "Memcache extension is not loaded.";
			return FALSE;
		}
		// Do we have a Config File?
		if( !is_array( $this->config ) )
		{
			$this->crashed = TRUE;
			$this->crash_reason = INS_SUITE . " has not been configured properly.";
			return FALSE;
		}
		// Do we have a Memcache Config?
		if( !array_key_exists( 'memcache', $this->config ) )
		{
			$this->crashed = TRUE;
			$this->crash_reason = "Memcache Configuration is missing. Please read the DOCS before using this module.";
			return FALSE;
		}
		// Do we have any servers?
		if( !is_array( $this->config['memcache']['servers'] ) )
		{
			$this->crashed = TRUE;
			$this->crash_reason = "No server specified. Please read the DOCS before using this module.";
			return FALSE;
		}

		if( !mb_strlen( trim( $this->config['cache']['identifier'] ) ) )
		{
			$this->identifier = md5( INS_ROOT );
		}
		else
		{
			$this->identifier = trim( $this->config['cache']['identifier'] );
		}
		
		return $this->INS__connect();
	}
	
    /**
	 * Connect to memcache Server/s
	 *
	 * @param	array 		Connection information
	 * @return	boolean
	 */
	protected function INS__connect()
	{	
		$masterserver = TRUE;
		
		foreach( $this->config['memcache']['servers'] AS $array )
		{	
			extract($array);
			// Connect to first server if available
			if($masterserver === TRUE)
			{
				$this->link = new \MemCache;
				$this->link->connect($server, $port);
				
				// Unable to connect?
				if(!$this->link)
				{
					// Check if any more servers are specified
					if($this->INS__servers() > 1)
						continue;
					// No more servers specified	
					else
					{
						$this->crashed = TRUE;
						$this->crash_reason = "Unable to connect to the specified server.";
						return FALSE;
					}	
				}
				// We were connected to the first server, now other servers will be added to connection pool
				else				
					$masterserver = FALSE;
			}	
			else
			{
				$this->link->addserver($server, $port);
			}
		}
		
		$this->link->setCompressThreshold(20000, 0.2);
		
		return TRUE;
	}
	
	/**
	 * Returns number of Servers
	 *
	 * @param	array 		Connection information
	 * @return	array
	 */
	public function INS__servers()
	{
		return count($this->config['memcache']['servers']);
	}	
	
    /**
	 * Disconnect from remote Cache
	 *
	 * @return	 boolean
	 */
	public function disconnect()
	{
		if($this->link)
		{
			return $this->link->close();
		}
		else
		{
			return FALSE;
		}
	}
	
    /**
	 * Insert a value in the Cache
	 *
	 * @param	string		Cache unique key
	 * @param	string		Cache value to add
	 * @param	integer		Expiration time
	 * @return	boolean
	 */
	public function store($key, $value, $expiry=0)
	{
		return $this->link->set( $this->identifier . '_' . $key, $value, MEMCACHE_COMPRESSED, intval( $expiry ) );
	}
	
    /**
	 * Retrieve a value from the Cache.	
	 *
	 * @param	string		Cache unique key
	 * @return	mixed		On failure, returns FALSE
	 */
	public function get($key)
	{
		$value = $this->link->get( $this->identifier . '_' . $key );
		if($value === FALSE)
		{
			return FALSE;
		}
		else
		{
			return unserialize($value);
		}
	}
	
	/**
	 * Retrieve an array of values from the Cache.	
	 *
	 * @param	string		Cache unique key
	 * @return	array		On failure, returns FALSE
	 */
	public function get_array($keys)
	{
		// Update each element with our Unique identifier
		foreach($keys AS &$key)
		{
			$key = $this->identifier . '_' . $key;
		}
		
		$values = $this->link->get($keys);
		
		return $values;
	}
	
    /**
	 * Updates a value in Cache
	 *
	 * @param	string		Cache unique key
	 * @param	string		Cache value to set
	 * @param	integer		Expiration time
	 * @return	boolean
	 */
	public function update($key, $value, $expiry=0)
	{
		$this->remove($key);
		return $this->store($key, $value, $expiry);
	}	
	
    /**
	 * Deletes a value from Cache
	 *
	 * @param	string		Cache unique key
	 * @return	boolean
	 */
	public function remove($key)
	{
		return $this->link->delete( $this->identifier . '_' . $key, 0 );
	}
}

?>