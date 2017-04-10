<?php
/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.5.0
 * GeoLocation
 * Last Updated: $Date: 2014-10-26 22:40:30 (Sun, 26 Oct 2014) $
 * </pre>
 * 
 * @author 		$Author: AskAmn$
 * @copyright	(c) 2014 Infusion Network Solutions
 * @license		http://www.infusionnetwork/licenses/license.php?view=main&version=2014
 * @package		IN.CMS
 * @since 		0.5.2; 22 August 2014
 * @version 	$Revision: 	01224 $
 */

namespace INS\Geo;

class Geo
{
	/** 
	 * Loads the instance of this class
	 *
	 * @return 		resource
	 */
	public static function instance()
    {
    	$arguments = func_get_args();

    	if( !empty( $arguments ) )
        	return \INS\Core::getInstance( __CLASS__, $arguments);
        else
        	return \INS\Core::getInstance( __CLASS__ );
    }
    
	/**
	 * Returns a geo ip lookup
	 * Currently uses: http://netip.de API
	 *
	 * @since 	0.5.2	
	 * @param	string		IP to lookup		
	 * @return	array
	 */	
	static public function geoIP($ip)
	{
		if(!filter_var($ip, FILTER_VALIDATE_IP))
		{
			return false;
		}

		$response = @file_get_contents('http://www.netip.de/search?query='.$ip);
		
		if(empty($response))
		{
			return false;
		}
		
		$patterns = array();
		$patterns["domain"] = '#Domain: (.*?)&nbsp;#i';
		$patterns["country"] = '#Country: (.*?)&nbsp;#i';
		$patterns["state"] = '#State/Region: (.*?)<br#i';
		$patterns["town"] = '#City: (.*?)<br#i';

		$ipInfo = array();

		foreach ($patterns as $key => $pattern)
		{
			$ipInfo[$key] = preg_match($pattern, $response, $value) && !empty($value[1]) ? $value[1] : 'N/A';
		}

		return $ipInfo;
	}
}