<?php
/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.5.0
 * Db cache handler Class
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

class Database implements \INS\Cache\InterfaceCache
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
	 * @var		boolean
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
		if( !strlen( \INS\Core::$config['cache']['identifier'] ) )
			$this->identifier = hash(md5, INS_ROOT);
		else
			$this->identifier = \INS\Core::$config['cache']['identifier'];
	}

    /**
	 * Not used by this layer
	 *
	 * @return	 boolean
	 */
	public function disconnect()
	{
		return;
	}
	
    /**
	 * Not used by this library
	 *
	 * @param	string		Cache unique key
	 * @param	string		Cache value to add
	 * @param	integer		Expiration time
	 * @return	boolean
	 */
	public function store( $key, $value, $expiry=0 )
	{
		return;
	}
	
    /**
	 * Retrieve a value from the Cache.	
	 *
	 * @param	string		Cache unique key
	 * @return	mixed		On failure, returns false
	 */
	public function get( $key )
	{
		$row = \INS\Db::i()->fetch( '`key`, `cache`', 'inscache', "`key` = ?", NULL, [ 1 => $key ] );
		$data = $row['cache'];
		$data = @unserialize($data);
		return $data;
	}
	
    /**
	 * No need to use in this layer
	 *
	 * @param	string		Cache unique key
	 * @param	string		Cache value to set
	 * @param	integer		Expiration time
	 * @return	boolean
	 */
	public function update($key, $value, $expiry=0)
	{
		return;
	}	
	
    /**
	 * Not used by this layer
	 *
	 * @param	string		Cache unique key
	 * @return	boolean
	 */
	public function remove($key)
	{
		return;
	}
}
?>