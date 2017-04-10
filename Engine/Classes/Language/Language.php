<?php
/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.5.0
 * Language Class
 * Last Updated: $Date: 2014-10-28 5:35 (Tue, 26 Oct 2014) $
 * </pre>
 * 
 * @author      $Author: AskAmn$
 * @package     IN.CMS
 * @copyright   (c) 2014 Infusion Network Solutions
 * @license     http://www.infusionnetwork/licenses/license.php?view=main&version=2014  Revision License 0.1.1
 * @since       0.1.0
 * @version     Revision: 0510
 */

namespace INS;

class Language {
	/**
	 * The path to the folder where languages are placed.
	 *
	 * @var string
	 */
	public $path;

	/**
	 * The language the system is using. (Default is ENG).
	 *
	 * @var string
	 */
	public $language;

	/**
	 * Some core files, that are needed for INS to function properly
	 *
	 * @var array
	 */
	public $coreFiles = array(	
		'apps' => 'recacheAppsLangFile'
	);

	/**
	 * Language variables
	 *
	 * @var array
	 */
	public $strings = array();

	/**
	 * To be parsed
	 *
	 * @var 	string
	 */
	public $toBeParsed = NULL;

	/**
	 * Language Map
	 *
	 * @var 	array
	 */
	public static $lang = array(
		'en-us'	=> 'English_United_States',
		'af'	=> 'Afrikaans',
		'ar-sa'	=> 'Arabic_Saudi_Arabia',
		'ar-eg'	=> 'Arabic_Egypt',
		'ar-dz'	=> 'Arabic_Algeria',
		'ar-tn'	=> 'Arabic_Tunisia',
		'ar-ye'	=> 'Arabic_Yemen',
		'ar-jo'	=> 'Arabic_Jordan',
		'ar-kw'	=> 'Arabic_Kuwait',
		'ar-bh'	=> 'Arabic_Bahrain',
		'eu'	=> 'Basque_Basque',
		'be'	=> 'Belarusian',
		'zh-tw'	=> 'Chinese_Taiwan',
		'zh-hk'	=> 'Chinese_Hong_Kong_SAR',
		'hr'	=> 'Croatian',
		'da'	=> 'Danish',
		'nl-be'	=> 'Dutch_Belgium',
		'en-au'	=> 'English_Australia',
		'en-nz'	=> 'English_New_Zealand',
		'en-za'	=> 'English_South_Africa',
		'en'	=> 'English',
		'en-tt'	=> 'English_Trinidad',
		'fo'	=> 'Faeroese',
		'fi'	=> 'Finnish',
		'fr-be'	=> 'French_Belgium',
		'fr-ch'	=> 'French_Switzerland',
		'gd'	=> 'Gaelic_Scotland',
		'de'	=> 'German_Standard',
		'de-at'	=> 'German_Austria',
		'de-li'	=> 'German_Liechtenstein',
		'he'	=> 'Hebrew',
		'hu'	=> 'Hungarian',
		'id'	=> 'Indonesian',
		'it-ch'	=> 'Italian_Switzerland',
		'ko'	=> 'Korean_Johab',
		'lv'	=> 'Latvian',
		'mk'	=> 'Macedonian_FYROM',
		'mt'	=> 'Maltese',
		'no'	=> 'Norwegian_Bokmal',
		'pt-br'	=> 'Portuguese_Brazil',
		'rm'	=> 'Rhaeto_Romanic',
		'ro-mo'	=> 'Romanian_Republic_of_Moldova',
		'ru-mo'	=> 'Russian_Republic_of_Moldova',
		'sr'	=> 'Serbian_Latin',
		'sk'	=> 'Slovak',
		'sb'	=> 'Sorbian',
		'es-mx'	=> 'Spanish_Mexico',
		'es-cr'	=> 'Spanish_Costa_Rica',
		'es-do'	=> 'Spanish_Dominican_Republic',
		'es-co'	=> 'Spanish_Colombia',
		'es-ar'	=> 'Spanish_Argentina',
		'es-cl'	=> 'Spanish_Chile',
		'es-py'	=> 'Spanish_Paraguay',
		'es-sv'	=> 'Spanish_El_Salvador',
		'es-ni'	=> 'Spanish_Nicaragua',
		'sx'	=> 'Sutu',
		'sv-fi'	=> 'Swedish_Finland',
		'ts'	=> 'Tsonga',
		'tr'	=> 'Turkish',
		'ur'	=> 'Urdu',
		'vi'	=> 'Vietnamese',
		'ji'	=> 'Yiddish',
		'sq'	=> 'Albanian',
		'ar-iq'	=> 'Arabic_Iraq',
		'ar-ly'	=> 'Arabic_Libya',
		'ar-ma'	=> 'Arabic_Morocco',
		'ar-om'	=> 'Arabic_Oman',
		'ar-sy'	=> 'Arabic_Syria',
		'ar-lb'	=> 'Arabic_Lebanon',
		'ar-ae'	=> 'Arabic_U.A.E.',
		'ar-qa'	=> 'Arabic_Qatar',
		'bg'	=> 'Bulgarian',
		'ca'	=> 'Catalan',
		'zh-cn'	=> 'Chinese_PRC',
		'zh-sg'	=> 'Chinese_Singapore',
		'cs'	=> 'Czech',
		'nl'	=> 'Dutch_Standard',
		'en-gb'	=> 'English_United_Kingdom',
		'en-ca'	=> 'English_Canada',
		'en-ie'	=> 'English_Ireland',
		'en-jm'	=> 'English_Jamaica',
		'en-bz'	=> 'English_Belize',
		'et'	=> 'Estonian',
		'fa'	=> 'Farsi',
		'fr'	=> 'French_Standard',
		'fr-ca'	=> 'French_Canada',
		'fr-lu'	=> 'French_Luxembourg',
		'ga'	=> 'Irish',
		'de-ch'	=> 'German_Switzerland',
		'de-lu'	=> 'German_Luxembourg',
		'el'	=> 'Greek',
		'hi'	=> 'Hindi',
		'is'	=> 'Icelandic',
		'it'	=> 'Italian_Standard',
		'ja'	=> 'Japanese',
		'lt'	=> 'Lithuanian',
		'ms'	=> 'Malaysian',
		'pl'	=> 'Polish',
		'pt'	=> 'Portuguese_Portugal',
		'ro'	=> 'Romanian',
		'ru'	=> 'Russian',
		'sz'	=> 'Sami_Lappish',
		'sl'	=> 'Slovenian',
		'es'	=> 'Spanish_Spain',
		'es-gt'	=> 'Spanish_Guatemala',
		'es-pa'	=> 'Spanish_Panama',
		'es-ve'	=> 'Spanish_Venezuela',
		'es-pe'	=> 'Spanish_Peru',
		'es-ec'	=> 'Spanish_Ecuador',
		'es-uy'	=> 'Spanish_Uruguay',
		'es-bo'	=> 'Spanish_Bolivia',
		'es-hn'	=> 'Spanish_Honduras',
		'es-pr'	=> 'Spanish_Puerto_Rico',
		'sv'	=> 'Swedish',
		'th'	=> 'Thai',
		'tn'	=> 'Tswana',
		'uk'	=> 'Ukrainian',
		've'	=> 'Venda',
		'xh'	=> 'Xhosa',
		'zu'	=> 'Zulu',
	);

	/** 
	 * Loads the instance of this class
	 *
	 * @return 		resource
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
	 * Returns the language file name corresponding the given code
	 * http://msdn.microsoft.com/en-us/library/ms533052%28v=vs.85%29.aspx
	 *
	 * @param 	string 		The language code to be looked for
	 */
    public static function getLanguage( $langCode = 'en-us' )
    {
    	return static::$lang[ $langCode ];
    }

	/**
	 * Constructor
	 *
	 * @param 	string 		The path to the language folder
	 */
	function __construct($path = '', $langCode = 'en-us')
	{
		\INS\Core::checkState( __CLASS__ );

		if( !strlen( $path ) )		
			$this->path = INS_LANGLIB_DIR;
		else 	
			$this->path = $path;	
		
		$this->language = static::getLanguage( $langCode );
	}
	
	/**
	 * Alternately, we can set language folder explicitly using this function.
	 *
	 * @param string The path to the language folder.
	 */
	function path( $new_path )
	{
		$this->path = $new_path;
	}
	
	/**
	 * Load the language variables from a file.
	 *
	 * @param string The section whose language is requested
	 */
	public function load( $section )
	{	
		if( file_exists(INS_LANGLIB_DIR . DIRECTORY_SEPARATOR . $this->language . DIRECTORY_SEPARATOR . $section . '.' . INS_LANG_IDENTIFIER . ".php") )
		{
			require_once INS_LANGLIB_DIR . DIRECTORY_SEPARATOR . $this->language . DIRECTORY_SEPARATOR . $section . '.' . INS_LANG_IDENTIFIER . ".php";	
		}
		else
		{
			die( static::langLoadError($section, $this->language) );
		}

		$this->strings = array_merge( $this->strings, $l );
	}

	/**
	 * This function parses the VARIABLES in a language file with their actual values.
	 *
	 * @param 	string  The data to be parsed
	 * @return 	string  The parsed data
	 */
	public function parse( $string )
	{
		$arguments = func_get_args();
		$number = count($arguments);

		if( $this->toBeParsed !== NULL )
		{
			for($i = 0; $i < $number; $i++)
			{
				$this->toBeParsed = str_replace('{'.($i+1).'}', $arguments[$i], $this->toBeParsed);
			}

			$string = $this->toBeParsed;
			$toBeParsed = NULL;
		}
		else
		{
			for($i = 1; $i < $number; $i++)
			{
				$string = str_replace('{'.$i.'}', $arguments[$i], $string);
			}
		}	

		return $string;
	}	


	/**
	 * This function parses the html
	 *
	 * @param 	string  The data to be parsed
	 * @return 	string  The parsed data
	 */
	public function parseOutput( &$data )
	{ 
		foreach( $this->strings AS $k => $s )
		{
			$data = str_replace( '{'. $k . '}', $s, $data );
		}

		return $data;
	}	

	/**
	 * Error for Missing Language File/s
	 *
	 * @param 	string 		The section whose language was requested
	 * @param   string 		The language	
	 * @return 	string 		The error
	 */
	public static  function langLoadError( $section, $language )
	{
	 	if( INS_DEV_MODE )	
	 		return INS_LANGLIB_DIR . DIRECTORY_SEPARATOR . $language . DIRECTORY_SEPARATOR . $section . '.' . INS_LANG_IDENTIFIER . ".php does not exist." ;
	 	else 				
	 		return '"' . $section . "' section (language) does not exist!";
	}

	/**
	 * Loads a language string
	 *
	 * @param 	string 			The lang identifier
	 * @return 	\INS\Language	
	 */
	public function strings( $identifier, $prefix = NULL )
	{
		if( $prefix !== NULL )
		{
			$identifier = $prefix . '_' . $identifier;
		}

	 	if( mb_strlen( $identifier ) > 0 )
	 	{
	 		$this->toBeParsed = $this->strings[ $identifier ];	
	 		return $this;
	 	}
	}

	/**
	 * @return 		string
	 */
	public function __tostring()
	{
		return $this->toBeParsed;
	}
}

?>