<?php
/**
 * <pre>
 * Ebony PHP Services
 * EPS.{%APPNAME%} v2.0.0
 * Cache Interface: The basic interface for various Cache methods
 *					Here we define a set of basic methods required by every cache handler	
 * Last Updated: $Date: {%DATE%} $
 * </pre>
 *
 * @author 		$Author: AmNX $
 * @copyright	(c) 2014 Ebony PHP Services
 * @license		{%LICENSE%}
 * @package		EPS.{%APPNAME%}
 * @link		http://www.ebonyservices.com/
 * @since		Monday 16th April 2014 13:00
 * @version		$First Build: 100 $					
 * 
 */

namespace INS\Cache;

interface InterfaceCache
{
	/**
	 * Disconnect from remote cache
	 * Only for specific Cache methods [Example: MemCache]
	 *
	 * @return boolean
	 */
	public function disconnect();
	
	/**
	 * Update value in remote cache store
	 *
	 * @param	string		Cache unique key
	 * @param	string		Cache value to set
	 * @param	integer		Expiration time
	 * @return	@e boolean
	 */
	public function update($key, $value, $expiry=0);
	
	/**
	 * Deletes a value from Cache
	 *
	 * @param	string		Cache unique key
	 * @return	boolean
	 */
	public function remove($key);
	
	/**
	 * Retrieves a value from Cache
	 *
	 * @param	string		Cache unique key
	 * @return	mixed
	 */
	public function get($key);
	
	/**
	 * Store data in cache
	 *
	 * @param	string		Cache unique key
	 * @param	string		Cache value to add
	 * @param	integer		Expiration time
	 * @return	boolean
	 */
	public function store($key, $value, $expiry=0);
}
?>