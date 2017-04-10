<?php
/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.5.0
 * Handles all file related work
 * Last Updated: $Date: 2014-12-27 4:10:16 (Sun, 27 Dec 2014) $
 * </pre>
 * 
 * @author 		$Author: AskAmn$
 * @copyright	(c) 2014 Infusion Network Solutions
 * @license		http://www.infusionnetwork/licenses/license.php?view=main&version=2014
 * @package		IN.CMS
 * @since 		0.5.2; 22 August 2014
 */

namespace INS;

class File
{		
	/**
	 * Filename without extension
	 *
	 * @var		boolean
	 */
	public $removeExtension = FALSE;
	
	/**
	 * The type of File -> Avatar, Cover, etc
	 *
	 * @var		string
	 */
	public $uploadFileType = '';
	
	/**
	 * The keys to various directories
	 *
	 * @var		array
	 */
	public $upload_directories = array(
		'avatar' => 'avatars',
		'cover' => 'covers',
	);	
	
	/**
	 * The dir where file will be moved after upload
	 *
	 * @var		string
	 */
	public $uploadsDir = INS_UPLOADS_DIR;
	
	/**
	 * Maximum file size allowed for the file being uploaded
	 *
	 * @var		integer
	 */
	public $maxFileSize = 0;
	
	/**
	 * We have marked PHP, HTML, HTM, etc as dangerous files
	 * This variable will make sure that these dangerous files are parsed and converted to a safe file
	 *
	 * @var		integer
	 */
	public $parse_dangerous_scripts	= 1;
	
	/**
	 * If you wish to force a custom extension on file
	 * @usage $force_custom_extension = 'ins'		This will make: upload.zip => upload.ins
	 *
	 * @var		string
	 */
	public $force_custom_extension = '';
	
	/**
	 * What file extensions do we allow?
	 *
	 * @var		array
	 */
	public $allowedExtensions = array();
	
	/**
	 * Should we check the file extension against our own allowed extensions?
	 *
	 * @var		boolean
	 */
	public $checkExtensions = true;
	
	/**
	 * Content to check inside a file
	 *
	 * @var		boolean
	 */
	private $contentMatch = '#(<script|<html|<head|<title|<body|<pre|<table|<a\s+href|<img|<plaintext|<cross\-domain\-policy)(\s|=|>)#si';
	
	/**
	 * Image extensions allowed
	 *
	 * @var		array
	 */
	public $imageExtensions	= array('gif', 'jpeg', 'jpg', 'jpe', 'png');
	
	/**
	 * Checks if the uploaded image is actually an image
	 *
	 * @var		boolean
	 */
	public $checkValidImage	= true;
	
	/**
	 * Current file extension
	 *
	 * @var		string
	 */
	public $fileExtension = '';
	
	/**
	 * The location where the uploaded file must be moved
	 *
	 * @var		string
	 */
	public $uploadFileLocation = '';
	
	/**
	 * Error messages
	 *
	 * @var		array
	 */
	public $errors = array(	
        1 						=> 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        2 						=> 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
        3 						=> 'The uploaded file was only partially uploaded',
        4 						=> 'No file was uploaded',
        6 						=> 'Missing a temporary folder',
        7 						=> 'Failed to write file to disk',
        8 						=> 'A PHP extension stopped the file upload',
		'IMAGE_NOT_VALID' 		=> 'Image is not valid, Possible XSS threat detected',
		'FILE_NOT_VALID' 		=> 'Uploaded file is invalid/corrupt', 
		'EXTENSION_DISALLOWED' 	=> 'This extension is disallowed',
		'FILE_HAS_NO_EXT' 		=> 'The file you are trying to upload, has no extension',
        'FILE_MOVE_FAIL' 		=> 'There was an error moving the file',
        'MAX_FILE_SIZE' 		=> 'File is too big',
		'DANGEROUS_CONTENTS' 	=> 'File contains dangerous contents',
        'MIN_FILE_SIZE' 		=> 'File is too small',
        'MAX_WIDTH' 			=> 'Image exceeds maximum width',
        'MIN_WIDTH' 			=> 'Image requires a minimum width',
        'MAX_HEIGHT' 			=> 'Image exceeds maximum height',
        'MIN_HEIGHT' 			=> 'Image requires a minimum height'
	);
	
	/**
	 * Stores the error encountered
	 *
	 * @var		string
	 */
	public $errorString = '';
	
	/**
	 * Is the uploaded file an image?
	 *
	 * @var		boolean
	 */
	public $isImage	= 0;
	
	/**
	 * Filename of file actually uploaded by user
	 *
	 * @var		string
	 */
	public $suppliedFileName = "";
	
	/**
	 * Parsed filename
	 *
	 * @var		string
	 */
	public $parsedFileName = "";
	
	/**
	 * Final file name
	 *
	 * @var		string
	 */
	public $finalFileNameAndDir = "";
	
	/**
	 * Was the upload box even touched?
	 *
	 * @var		boolean
	 */
	public $uploadBox = true;

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
     * Constructor of class.
     *
     * @return      void
     */
    public function __construct()
    {
        \INS\Core::checkState( __CLASS__ ); 
    }
	
	/**
	 * The main UploadHandler
	 *
	 * @return	boolean
	 */
	public function Execute()
	{
		if(empty($_FILES[$this->uploadFormField]['name'])) 
		{		
			$this->uploadBox = FALSE;
			return;
		}
		$this->removeSlashes();
		
		$__filename = \INS\Filter::i()->filter( $_FILES[$this->uploadFormField]['name'] );
		$__filesize = isset($_FILES[$this->uploadFormField]['size']) ? $_FILES[$this->uploadFormField]['size'] : '';
		$__filetype = isset($_FILES[$this->uploadFormField]['type']) ? $_FILES[$this->uploadFormField]['type'] : '';		
		$__filetype = preg_replace("/^(.+?);.*$/", "\\1", $__filetype);
		
		/**
		 *	Check for errors
		 */ 
		if(array_key_exists($_FILES[$this->uploadFormField]['error'], $this->errors))
		{
			$this->errorString = $this->errors[$_FILES[$this->uploadFormField]['error']];
			return FALSE;
		}	
		
		if(!is_uploaded_file($_FILES[$this->uploadFormField]['tmp_name']))
		{
			$this->errorString = $this->errors[4];
			return FALSE;
		}
			
		if($this->checkExtensions)
		{
			$this->allowedExtensions = array_map('strtolower', $this->allowedExtensions);
			/**
			 * Lets grab the extension!
			 */	
			$this->fileExtension = $this->getExtension($__filename);

			if(!$this->fileExtension)
			{
				$this->errorString = $this->errors['FILE_HAS_NO_EXT'];
				return FALSE;
			}
			if(!in_array($this->fileExtension, $this->allowedExtensions))
			{
				$this->errorString = $this->errors['EXTENSION_DISALLOWED'];
				return FALSE;
			}
		}
				
		$this->real_fileExtension = $this->fileExtension;
		
		/**
		 * If maxFileSize is set, the file size will be checked against maxFileSize
		 */
		if(($this->maxFileSize) && ($__filesize > $this->maxFileSize))
		{
			$KB = $this->maxFileSize/1024;
			$this->errorString = $this->errors['MAX_FILE_SIZE'].": Allowed: {$KB}KB";
			return FALSE;
		}
		
		$this->suppliedFileName = $__filename;
		
		/**
		 * If useRandomName is set to yes, we generate a random string for name otherwise, use the supplied name
		 */
		if($this->useRandomName === true)
		{
			$__filename = md5( time() * uniqid() ).".".$this->fileExtension;
		}
		else
		{
			/**
			 * Replaces all non-word characters with an '_' 
			 */
			$__filename = preg_replace('/[^\w\.]/', "_", $__filename);
		}
		
		/**
		 * Remove file extension?	
		 */
		if($this->removeExtension === true)
		{
			$this->parsedFileName = str_replace('.'.$this->fileExtension, "", $__filename);
		}
		else
		{
			$this->parsedFileName = str_replace('.'.$this->fileExtension, "_{$this->fileExtension}", $__filename);
		}
		
		/**
		 * Checks for dangerous scripts & converts them to a .txt file
		 */
		$renamed = 0;
		
		if($this->parse_dangerous_scripts)
		{
			if(preg_match('/\.(cgi|pl|js|asp|php|html|htm|jsp|jar)(\.|$)/i', $__filename))
			{
				$__filetype                 = 'text/plain';
				$this->fileExtension        = 'txt';
				$this->parsedFileName	    = preg_replace('/\.(cgi|pl|js|asp|php|html|htm|jsp|jar)(\.|$)/i', "$2", $this->parsedFileName);
				
				$renamed = 1;
			}
		}

		/**
		 * Is it an image?
		 */
		if(in_array($this->real_fileExtension, $this->imageExtensions))
		{
			$this->isImage = 1;
		}

		/**
		 * If we are to force an extension to a file...
		 */
		if($this->force_custom_extension && !$this->isImage)
		{
			$this->fileExtension = str_replace(".", "", $this->force_custom_extension); 
		}	
		$this->parsedFileName = $this->parsedFileName.'.'.$this->fileExtension;
		
		/**
		 * Move the uploaded file!
		 */
		if($this->uploadFileLocation == '')
		{	
			if( empty( $this->uploadFileType ) )
			{
				$this->finalFileNameAndDir = $this->uploadsDir . DIRECTORY_SEPARATOR . $this->parsedFileName;
			}
			else
			{
				foreach($this->upload_directories AS $dir => $location)
				{
					if($this->uploadFileType == $dir)
					{
						$this->finalFileNameAndDir = $this->uploadsDir . DIRECTORY_SEPARATOR . $location . DIRECTORY_SEPARATOR . $this->parsedFileName;
					}	
				}
			}
		}
		else
		{
			$this->finalFileNameAndDir = $this->uploadsDir . DIRECTORY_SEPARATOR . $this->uploadFileLocation . DIRECTORY_SEPARATOR . $this->parsedFileName;
		}	
		if(!@move_uploaded_file($_FILES[$this->uploadFormField]['tmp_name'], $this->finalFileNameAndDir))
		{
			$this->errorString = $this->errors['FILE_MOVE_FAIL'];
			return FALSE;
		}
		else
		{
			@chmod($this->finalFileNameAndDir, INS_FILE_PERMISSION);
		}
		
		/**
		 * One final file check
		 */
		if(!$renamed && $this->fileExtension != 'txt')
		{
			if(!$this->checkDangerousContents())
			{
				$this->errorString = $this->errors['DANGEROUS_CONTENTS'];
				return FALSE;
			}
		}
		if( $this->getFileSize($this->finalFileNameAndDir) != $_FILES[$this->uploadFormField]['size'] )
		{
			@unlink($this->finalFileNameAndDir);
			$this->errorString = $this->errors['FILE_NOT_VALID'];
			return FALSE;
		}
		
		/**
		 *	If we don't have 'getimagesize' we can't check the image for validity 
		 */
		if(!function_exists('getimagesize'))
			$this->checkValidImage = 0;

		/**
		 *	The file is an image
		 */	
		if($this->isImage)
		{	
			if($this->checkValidImage)
			{
				list($width, $height, $type, $attr) = @getimagesize($this->finalFileNameAndDir);
		
				if (!$width || !$height) 
				{
					@unlink($this->finalFileNameAndDir);
					$this->errorString = $this->errors['IMAGE_NOT_VALID'];
					return FALSE;
				}
				else if($type == 1 && ($this->fileExtension == 'jpg' || $this->fileExtension == 'jpeg'))
				{
					@unlink($this->finalFileNameAndDir);
					$this->errorString = $this->errors['IMAGE_NOT_VALID'];
					return FALSE;
				}
			}
		}
	}
	
	/**
	 * Removes trailing slashes
	 *
	 * @return	void
	 */
	private function removeSlashes()
	{
		$this->uploadsDir = rtrim($this->uploadsDir, '/');
	}

   /**
	* Check for dangerous stuff inside a file
	*
	* @return	boolean
	*/
	protected function checkDangerousContents()
	{
		$fh = fopen($this->finalFileNameAndDir, 'rb');
		$file = fread($fh, 1024); 
		fclose($fh); 	
		if(!$file)
		{
			@unlink($this->finalFileNameAndDir);
			return FALSE;
		}
		elseif(preg_match($this->contentMatch, $file))
		{
			@unlink($this->finalFileNameAndDir);
			return FALSE;
		}
		return true;
	}

	/**
	 * A function to get Extension
	 *
	 * @param	string	
	 * @return	string
	 */
	static public function getExtension($file)
	{
		if(!mb_strstr($file, '.'))		return FALSE;

		/**
		 * The UNIX-like filesystems use a different model without the segregated extension metadata. 
		 * The dot character is just another character in the main filename, 
		 * and filenames can have multiple extensions, usually representing nested transformations, 
		 * such as files.tar.gz	
		 */ 
		if(mb_substr_count($file, '.') > 1)
		{
			$ext = ltrim(mb_strstr($file, '.'), '.');
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
				$ext = mb_strtolower(str_replace(".", "", mb_substr($file, mb_strrpos($file, '.'))));
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
        return static::fixIntegerOverflow(filesize($file));
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
}
?>