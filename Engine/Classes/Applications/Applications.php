<?php
/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.5.0
 * Application Class
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

class Applications
{
	/** 
     * Loads the instance of this class
     *
     * @return      resource
     */
    public static function i()
    {
        $arguments = func_get_args();

        if( !empty( $arguments ) )
            return \INS\Core::getInstance( __CLASS__, $arguments);
        else
            return \INS\Core::getInstance( __CLASS__ );
    }

    /** 
	 * Constructor
	 *
	 * @return 		void
	 */
	public function __construct()
	{
        \INS\Core::checkState( __CLASS__ );
	}

	/**
	 * Hooks To be Loaded
	 *
	 * @var		array
	 */
	public $hooksToLoad = array(); 
	
	/**
	 * Loads an addon's hooks into hookstoload property.
	 *
	 * @return 		void
	 */
	public function loadAddons()
	{	
		$addons = \INS\Cache::i()->read('appcache', true); 

		if( empty( $addons ) )
		{
			/* Cache seems to be broken */
			\INS\Cache::i()->recacheApps();
			$addons = \INS\Cache::i()->read('appcache', true); 
			/* Still empty? */
			if( empty( $addons ) )
			{
				return;
			}
		}
		if(!empty($addons['active']))
		{
			$this->hooksToLoad = $addons['active'];
		}	
	}	
	/**
	 * Runs hooks at a particular position
	 *
	 * @param 		string 		Position, where the hook must be run
	 * @param  		array|string 		Array of variables that must be passed inside the function	
	 * @return		string	
	 */
	public function runHooks( $position, $array = [] )
	{
		if(!isset($this->hooksToLoad[$position]))
		{
			return;
		}

		foreach($this->hooksToLoad[$position] AS $i => $arrayOfHookDetails)
		{

			/* Let us check if the plugin dev forgot to remove extension from hook file */
			$ext = ( \INS\File::getExtension($arrayOfHookDetails['file']) == 'php' ) ? '' :  '.php';
			
			require_once INS_PLUGINS_DIR . DIRECTORY_SEPARATOR . $arrayOfHookDetails['dir'] . DIRECTORY_SEPARATOR . 'hooks' . DIRECTORY_SEPARATOR . $arrayOfHookDetails['file'] . $ext;
			
			$function = $arrayOfHookDetails['dir'].'_'.$arrayOfHookDetails['key'];

			$return = !empty( $array ) ? $function( $array ) : $function();

			/* The hook requires params, but the function did not return anything to store back, lets store old values */
			if( empty( $return ) AND !empty( $array ) )
				$return = $array;

			return $return;
		}
	}	

	/**
	 * Compares version numbers of two apps
	 *
	 * @since 		0.4.0
	 * @return 		boolean
	 */
	public function versionCompare( $dir, $version )
	{
		$row = \INS\Db::i()->fetch('`version_code`', 'applications', "dir='{$dir}'");
		
		if($row['version_code'] < $version) 
			return TRUE;
		
		return FALSE;
	}
	
	/**
	 * Checks if an app is installed
	 *
	 * @return 		boolean
	 */
	public function appInstalled( $dir )
	{
		if(\INS\Db::i()->rowCount('applications', "dir='{$dir}'") == 1) 
			return TRUE;
		return FALSE;	
	}

	/**
	 * Rebuilds Settings
	 *
	 * @access 		public
	 * @since		0.3.0
	 */ 
	public function rebuildSettings()
	{
		$rows = \INS\Db::i()->fetchAll('*', 'settings');
		$string = array();
		$data = "<?php\n";
	   
		foreach( $rows AS $row )
		{ 
			/* Escape double quotes */
			$row['value'] = str_replace('"', '\"', $row['value']);
		    if(is_int($row['value']))
			{
				$value = $row['value'];
			} 
		    else
			{
				$value = '"'.$row['value'].'"';
			}
		    $data .= '$this->settings[\''.$row['group'].'\'][\''.$row['name'].'\'] = $_'.$row['name'].' = '.$value.';';
		    $data .= "\n";
		}
		
		$data .= "?>";
		file_put_contents( INS_ROOT."/core/settings.php", $data );
	} 

	/**
	 * Adds language strings to a language file
	 *
	 * @param       array 		Language array
	 * @param 		string  	App name
	 * @param       string 		File to inject language variables
	 * @access 		public
	 * @since		0.6.0
	 */ 
	public function addLanguageStrings( $langArray, $appname, $file )
	{
		if( !is_array( $langArray ) )
			return;

		$data = PHP_EOL . '/* @' . $appname . '@ */' . PHP_EOL;
		foreach( $langArray AS $key => $string )
		{
			$data .= '$l[\'' . $key . '\'] = \'' . $string . '\';' . PHP_EOL;
		}

		$data .= '/* @end:' . $appname . '@ */' . PHP_EOL;

		file_put_contents( 
			INS_LANGLIB_DIR . DIRECTORY_SEPARATOR . \INS\Language::i()->getLanguage() . DIRECTORY_SEPARATOR . $file . '.' . INS_LANG_IDENTIFIER . ".php",
		 	$data, FILE_APPEND 
		);
	} 	

	/**
	 * Removes language strings from a language file
	 *
	 * @param 		string  	App name
	 * @param       string 		File from which language strings are to be removed
	 * @access 		public
	 * @since		0.6.0
	 */
	public function removeLanguageStrings( $appname, $file ) 
	{
		$filedata = file_get_contents( 
			INS_LANGLIB_DIR . DIRECTORY_SEPARATOR . \INS\Language::i()->getLanguage() . DIRECTORY_SEPARATOR . $file . '.' . INS_LANG_IDENTIFIER . ".php" 
		);
		
		$filedata = preg_replace( '#\/\* @' . $appname . '@ \*\/(.*?)\/\* @end:' . $appname . '@ \*\/#s', '', $filedata );

		file_put_contents( 
			INS_LANGLIB_DIR . DIRECTORY_SEPARATOR . \INS\Language::i()->getLanguage() . DIRECTORY_SEPARATOR . $file . '.' . INS_LANG_IDENTIFIER . ".php",
		 	$filedata 
		);
	}
}
?>