<?php
/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.5.0
 * Disk cache handler Class
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

class Disk implements \INS\Cache\InterfaceCache
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
		if(!is_writeable( INS_ROOT . DIRECTORY_SEPARATOR . 'cache' ))
		{
			$this->crashed = TRUE;
			$this->crash_reason = "The cache folder is not writeable.";
			return FALSE;
		}
		
		$this->config   =& \INS\Core::Config();
		$this->Db       =  \INS\Core::Db();

		if( !strlen( $this->config['cache']['identifier'] ) )
			$this->identifier = hash(md5, INS_ROOT);
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
		$fh = @fopen( 
			INS_SYSTEM_DIR . DIRECTORY_SEPARATOR . 
			'BackEnd' . DIRECTORY_SEPARATOR . 
		    'Cache' . DIRECTORY_SEPARATOR . 
		    $this->identifier . '_' . $key . '.php', 
			'wb' 
		);
		
		if(!$fh)
			return FALSE;
		
		$flag = "";
		$content = "";
		
		if(is_array($value))
		{
			$value = serialize($value);
			$flag = "\n" . '$is_array = 1;' . "\n\n";
		}
		
		if($expiry)
			$flag .= "\n" . '$expiry = ' .$expiry. ";\n\n";
		$value = '"' .addslashes($value). '"';
		
		/**
		 * Cache content
		 */
		$content .= "<?php\n\n";
		$content .= '$value = ';
		$content .=	"{$value};\n{$flag}";
		$content .= "\n?>";
		
		flock($fh, LOCK_EX);
		fwrite($fh, $content);
		flock($fh, LOCK_UN);
		fclose($fh);
				
		return TRUE;
	}
	
    /**
	 * Retrieve a value from the Cache.	
	 *
	 * @param	string		Cache unique key
	 * @return	mixed		On failure, returns FALSE
	 */
	public function get($key)
	{	
		$return = "";
		
		if( is_file(
				INS_SYSTEM_DIR . DIRECTORY_SEPARATOR . 
				'BackEnd' . DIRECTORY_SEPARATOR . 
			    'Cache' . DIRECTORY_SEPARATOR . 
			    $this->identifier . '_' . $key . '.php'
		    )
		)
		{
			require_once INS_SYSTEM_DIR . DIRECTORY_SEPARATOR . 'BackEnd' . DIRECTORY_SEPARATOR . 'Cache' . DIRECTORY_SEPARATOR . $this->identifier . '_' . $key . '.php';
			
			$return = stripslashes($value);

			// Check for array Values	
			if(isset($is_array))
			{
				$return = unserialize($return);
			}
			// Check Expiry
			if(isset($expiry))
			{
				if($time = filemtime( INS_SYSTEM_DIR . DIRECTORY_SEPARATOR . 'BackEnd' . DIRECTORY_SEPARATOR . 'Cache' . DIRECTORY_SEPARATOR . $this->identifier . '_' . $key . '.php' ))
				{
					// Cache expired
					if((time() - $time) > $expiry)
					{
						@unlink( INS_SYSTEM_DIR . DIRECTORY_SEPARATOR . 'BackEnd' . DIRECTORY_SEPARATOR . 'Cache' . DIRECTORY_SEPARATOR . $this->identifier . '_' . $key . '.php' );
						return FALSE;
					}
				}
			}
		}
		return $return;
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
		if(is_file( INS_SYSTEM_DIR . DIRECTORY_SEPARATOR . 'BackEnd' . DIRECTORY_SEPARATOR . 'Cache' . DIRECTORY_SEPARATOR . $this->identifier . '_' . $key . '.php' ))
		{
			@unlink( INS_SYSTEM_DIR . DIRECTORY_SEPARATOR . 'BackEnd' . DIRECTORY_SEPARATOR . 'Cache' . DIRECTORY_SEPARATOR . $this->identifier . '_' . $key . '.php' );
		}
		
		return TRUE;
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