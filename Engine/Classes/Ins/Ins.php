<?php
/**
 * <pre>
 * 	Infusion Network Services
 * 	INS.{%APPNAME%} v0.5.0
 * 	The core class, all functions are declared static for the ability to be used anywhere & everywhere
 * 	Last Updated: $Date: Monday 19th April 2014 4:30 $
 * </pre>
 *
 * @author 		$Author: AmNX $
 * @copyright	(c) 2014 Infusion Network Services
 * @license		{%LICENSE%}
 * @package		INS.{%APPNAME%}
 * @link		
 * @since		Monday 16th April 2014 13:00
 * @version		$First Build: 100 $		
 * 
 */

class INS
{	
	/**
	 * A function to get Extension
	 *
	 * @param	string	
	 * @return	string
	 */
	static public function getExtension($file)
	{
		if(!strstr($file, '.'))		return false;

		/**
		 * The UNIX-like filesystems use a different model without the segregated extension metadata. 
		 * The dot character is just another character in the main filename, 
		 * and filenames can have multiple extensions, 
		 * usually representing nested transformations, 
		 * such as files.tar.gz	
		 */ 
		if(substr_count($file, '.') > 1)
		{
			$ext = ltrim(strstr($file, '.'), '.');
		}
		else
		{
			if(function_exists('pathinfo'))
			{
				$ext = pathinfo($file, PATHINFO_EXTENSION);	
			}
			else if(class_exists('SplFileInfo'))
			{
				$ext = new SplFileInfo($file);
				$ext = $ext->getExtension();
			}
			else
			{			
				$ext = strtolower(str_replace(".", "", substr($file, strrpos($file, '.'))));
			}
		}
		if($ext) 	return $ext;
		else 		return FALSE;
	}
	
	/**
	 * Fix integer overflow
	 *	
	 * @param 	integer		Supplied file size
	 * @return	string		Fixed File size
	 */	
    static public function fixIntegerOverflow($size) 
	{
        if ($size < 0) 
            $size += 0.5 * (PHP_INT_MAX + 1);
        return $size;
    }

	/**
	 * Gets file size
	 *	
	 * @param 	string 
	 * @return	string
	 */	
    static public function getFileSize($file) 
	{
        return self::fixIntegerOverflow(filesize($file));
    }
	
	/**
	 * Generates a random string
	 *
	 * @param	integer		
	 * @return 	string 		The random string
	 */
	static public function randomStr( $length = NULL )
	{
		$code = md5( uniqid(  ( mt_rand(1,9) ) ) );
		
		if( $length !== NULL )
			$code = substr($code, 0, $length);

		return $code;
	}   

	/**
	 * Reads a dir & removes the ./ ../ from dir array
	 *
	 * @param	string		The dir to read
	 * @return 	array 		
	 */
	static public function readDir($dir)
	{
		$dirArray = scandir($dir);
		array_shift($dirArray);
		array_shift($dirArray);
		return $dirArray;
	}
	
	/**
	 * Gets actual IP of a user
	 * Please note, that it does not & cannot detect Spoofed IP's. So, yea, sucks. 
	 * But there is no way to check IP spoofing.
	 *
	 * @since 	0.5.1						
	 * @return	string
	 */
	static public function getIP()
	{
		$ip = $_SERVER['REMOTE_ADDR'];
		return $ip;
    }

	/**
	 * Returns a geo ip lookup
	 * Currently uses: http://netip.de API
	 *
	 * @since 	0.5.1		
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

	/**
	 * A healthy and cleaner version of print_r
	 *
	 * @param  		mixed
	 * @return 		void
	 * @access 		public
	 * @since 		0.5.1
	 */
	static public function Debug( $parameter )
	{
		echo "<pre>"; print_r( $parameter ); echo "</pre>";
	}

	/**
	 * Updates statistics
	 *
	 * @param 	Changes 	array
	 */
	static public function updateStats( $changes = array() )
	{
mktime(0, 0, 0, date("m"), date("j"), date("Y"));
	}
}

?>