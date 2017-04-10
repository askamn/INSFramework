<?php

/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.5.0
 * WinCache cache handler Class
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

class Win implements \INS\Cache\InterfaceCache
{
	/**
	 * Identifier
	 *
	 * @var		string
	 */
	protected $identifier = '';
	
	/**
	 * Crash status of connection
	 *
	 * @var		boolean
	 */
	public $crashed;
	
	/**
	 * Reason of Crash
	 *
	 * @var		boolean
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
		
		// Win Extension check
		if(!function_exists('wincache_ucache_get'))
		{
			$this->crashed = TRUE;
			$this->crash_reason = "Function 'wincache_ucache_get' does not exist. Please make sure that you have WinCache extension installed.";
			return FALSE;
		}
		
		if($this->config['cache']['identifier'] == '')
			$this->identifier = md5( INS_ROOT );
		else
			$this->identifier = $this->config['cache']['identifier'];
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
		return wincache_ucache_set("{$this->identifier}_{$key}", $value, intval($expiry));
	}
	
    /**
	 * Retrieve a value from the Cache.	
	 *
	 * @param	string		Cache unique key
	 * @return	mixed		On failure, returns FALSE
	 */
	public function get($key)
	{
		$value = wincache_ucache_get("{$this->identifier}_{$key}");
		
		if($value === FALSE)
		{
			return FALSE;
		}
		else
		{
			return @unserialize($value);
		}
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
		return wincache_ucache_delete("{$this->identifier}_{$key}");
	}
	
	/**
	 * Not used by this library
	 *
	 * @return	boolean
	 */
	public function disconnect()
	{
		return TRUE;
	}
}
?>