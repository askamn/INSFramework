<?php
/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.5.0
 * Object Creator Class, one of the core classes of IN.Cms
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

namespace INS;

class Init
{
	/**
	 * List of constants used throught INS
	 *
	 * @access 		public
	 * @var 		array		
	 */
	static public $constants = array();

	/**
	 * Stores execution time
	 *
	 * @access 		public
	 * @var 		array		
	 */
	static public $time = [ 'start' => 0, 'end' => 0, 'total' => 0 ];

	/**
	 * Does all the initiation work.
	 *
	 * @access 		public
	 * @return 		void	
	 */
	public static function Execute()
	{
		static::$time['start'] = $_SERVER['REQUEST_TIME_FLOAT'];

		/* Define Constants */
		foreach( static::getConstants() AS $C => $V )
			defined( $C ) or define ( $C, $V );

		/* Encoding */
		mb_internal_encoding( strtoupper( INS_DOCUMENT_CHARSET ) );

		/* Set error handlers */
		error_reporting(E_ALL & ~E_NOTICE);

		/* Register our custom Autoloader */
		spl_autoload_register( '\INS\Init::insAutoloader', true, true );

		//\INS\Errorhandler::init();
		\INS\Core::instance()->init();
	}

	/**
	 * Returns list of defined constants
	 * It cannot be done as an separate class property, because
	 * classes don't allow functions to be part of the var during
	 * their declaration, i.e. var $mem = memory_get_usage(); will
	 * produce an error.
	 *
	 * @access 		public
	 * @return 		array		
	 */
	static public function getConstants()
	{
		return array(
				'INS_SCRIPT_TIME'			=> time(),
				'INS_FILE_PERMISSION' 		=> 0644,
				'INS_FOLDER_PERMISION'		=> 0777,
				'INS_DEV_MODE'				=> TRUE,
				'INS_ROOT'					=> __DIR__,
				'INS_SYSTEM_DIR'			=> __DIR__ . DIRECTORY_SEPARATOR . 'Engine',
				'INS_APPS_DIR'				=> __DIR__ . DIRECTORY_SEPARATOR . 'Applications',
				'INS_PLUGINS_DIR'			=> __DIR__ . DIRECTORY_SEPARATOR . 'Plugins',
				'INS_ADMIN_DIR'				=> __DIR__ . DIRECTORY_SEPARATOR . 'Admin',
				'INS_LANGLIB_DIR'			=> __DIR__ . DIRECTORY_SEPARATOR . 'Engine' . DIRECTORY_SEPARATOR . 'Languages',
				'INS_UPLOADS_DIR'			=> __DIR__ . DIRECTORY_SEPARATOR . 'Uploads' . DIRECTORY_SEPARATOR,
				'INS_LANG_IDENTIFIER'       => 'lang',
				'INS_ADMIN_APPS_DIR'		=> 'admin',
				'INS_FRONT_APPS_DIR'		=> 'front',
				'INS_MEMORY_USAGE'			=> ceil( memory_get_usage() ),
				'INS_DOCUMENT_CHARSET'		=> 'UTF-8',
				'INS_DOCUMENT_CHARSET_LC'	=> 'utf-8',
				'INS_SUITE'					=> 'INS Web Suite',
				'INS_BAD_CACHE'				=> 'Last attempt to fetch from remote cache failed.',
			);
	}

	/**
	 * Autoloader as per PSR - 0 Standard. Makes use of PSR - 4 (Improved autoloading)
	 *
	 * @param 		$fileIdentifier		Qualified class name
	 * @return 		void
	 */
	static public function insAutoloader( $fileIdentifier )
	{
		if( !class_exists( $fileIdentifier, FALSE ) )
		{
			$className = explode( '\\', ltrim( $fileIdentifier, '\\' ) );
			$finalName = $className[ count($className) - 1 ];

			/* Remove vendor Namespace */
			array_shift( $className );
			
			/* Remake the qualified name, without namespace */
			$fileIdentifier = str_replace( '\\', DIRECTORY_SEPARATOR, implode( '\\', $className ) );
			$path = INS_SYSTEM_DIR . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . $fileIdentifier . '.php';

			/* Looks like the shorthand method was used */
			if( !file_exists( $path ) )
			{
				/* So lets try to get the exact path */
				$path = mb_substr( $path, 0, -4 ) . DIRECTORY_SEPARATOR . $finalName . '.php';

				if( !file_exists( $path ) )
				{
					return FALSE;
				}
			}

			require_once $path;
		}
	}
}

\INS\Init::Execute();

?>