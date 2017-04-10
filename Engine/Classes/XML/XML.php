<?php
/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.5.0
 * XML Parser
 * Last Updated: $Date: 2014-10-28 5:35 (Tue, 26 Oct 2014) $
 * </pre>
 * 
 * @author      $Author: AskAmn$
 * @package     IN.CMS
 * @copyright   (c) 2014 Infusion Network Solutions
 * @license     http://www.infusionnetwork/licenses/license.php?view=main&version=2014  Revision License 0.1.1
 * @since       0.3.0
 * @version     Revision: 0600
 *
 *
 * <code>
 * Usage:
 * File:  Tobeparsed.xml
 * Contents: 
 * <?xml version="1.0" encoding="UTF-8"?>
 *	<info>
 *		<data>
 *			<name>INS Test Addon</name>
 *			<author>AskAmn</author>
 *			<description>Test build for testing INS Applications/Hooks Pages</description>
 *		</data>
 *	</info>
 * 
 * $xml = new XML('Tobeparsed.xml');
 * $xml->parse();
 * print_r($xml->parsedXML);
 *
 * Output:
 *  Array
 *	(
 *		[info] => Array
 *			(
 *				[tag] => info
 *				[value] => 
 *				[data] => Array
 *					(
 *						[tag] => data
 *						[value] => 		
 *						[name] => Array
 *							(
 *								[tag] => name
 *								[value] => INS Test Addon
 *							)
 *						[author] => Array
 *							(
 *								[tag] => author
 *								[value] => AskAmn
 *							)
 *						[description] => Array
 *							(
 *								[tag] => description
 *								[value] => Test build for testing INS Applications/Hooks Pages
 *							)
 *					)
 *			)
 *	)
 *
 *
 */
 
namespace INS;	

class XML
{
	/**
	 * The XML
	 *
	 * @var 	string
	 */
	public $xml;
	
    /**
	 * Parser Object
	 *
	 * @var 	object
	 */
	public $parser;
	
	/**
	 * Document Charset, FALSE will force XML_PARSER_CREATE to use a default charset
	 *
	 * @var 	mixed
	 */
	public $charset = FALSE;
	
	/**
	 * Charsets
	 *
	 * @var 	array
	 */
	private $charsets = array(
		'utf-8',
	);
	
	/**
	 * The Parsed XML
	 *
	 * @var 	array
	 */
	public $parsedXML;
	
	/**
	 * Index numerically flag
	 *
	 * @var 	integer
	 */
	public $indexNumeric = 0;
	
	/**
     * Collapse duplicate tags?
     *
     * @var 	boolean
     */	 
	public $collapseDuplicates = TRUE;
	
	/**
     * Crashed?
     *
     * @var 	boolean
     */	 
	public $crashed;	
	
	/**
     * Crash Reason
     *
     * @var 	string
     */	 
	public $crashReason = '';
	
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
     * Constructs the Object with Charset
     *
     * @param 	string/boolean		The Charset of the document to be parsed
	 * @param	boolean				Automatically parse the XML or perform explicit parse
	 * @return	void	 
     */	 
	function __construct( $xmlFile = '', $charset = FALSE, $init = TRUE )
	{
		\INS\Core::checkState( __CLASS__ );

		if(is_file($xmlFile))
		{
			$this->xml = @file_get_contents($xmlFile);
			if($this->xml == '')
			{
				$this->crashed = TRUE;
				$this->crashReason = 'Failed to load XML File';
			}
		}

		if($charset == FALSE)
			$this->charset = FALSE;
		else
		{
			if(in_array($charset, $this->charsets))
			{
				$this->charset = $charset;
			}
			else
			{
				$this->charset = FALSE;
			}
		}	

		if($init === TRUE)
			$this->parse();	
	}

	/**
	 * Adds a document to the Xml parser
	 *
	 * @param		string		Document location
	 * @return 		boolean
	 */
	public function addToParser( $file )
	{
		if( is_file( $file ) )
		{
			$this->xml = @file_get_contents( $file );
			if($this->xml == '')
			{
				$this->crashed = TRUE;
				$this->crashReason = 'Failed to load XML File';
			}
		}
	}
	
	/**
	 * Parses an XML document
	 *
	 * @param		string		Document to be parsed
	 * @return 		boolean
	 */
	public function parse()
	{
		$i = -1;
		
		if($this->charset == FALSE)
		{
			$this->parser = xml_parser_create();
		}
		else
		{
			$this->parser = xml_parser_create($this->charset);
		}
		
		xml_parser_set_option($this->parser, XML_OPTION_SKIP_WHITE, 0);
		xml_parser_set_option($this->parser,XML_OPTION_CASE_FOLDING,0);
		if(!xml_parse_into_struct($this->parser, $this->xml, $vals))
		{
			return FALSE;
		}
		xml_parser_free($this->parser);
		
		$this->parsedXML = $this->getChildren($vals, $i);
		$this->parsedXML = $this->cleanXML($this->parsedXML);
		
		/**
		 * Free off unnecessary vars
		 */
		unset($vals);
		unset($this->xml);
	}	
	
	/**
	 * Adaptation From Eric Pollman's code
	 *
	 * @param 	array 		
	 * @param 	int 		
	 * @return 	array		
	 */
	function getChildren($vals, &$i)
	{
		$children = array();

		if($i > -1 && isset($vals[$i]['value']))
		{
			$children['value'] = $vals[$i]['value'];
		}

		while(++$i < count($vals))
		{
			$type = $vals[$i]['type'];
			if($type == "cdata")
			{
				$children['value'] .= $vals[$i]['value'];
			}
			elseif($type == "complete" || $type == "open")
			{
				$tag = $this->build_tag($vals[$i], $vals, $i, $type);
				if($this->indexNumeric)
				{
					$tag['tag'] = $vals[$i]['tag'];
					$children[] = $tag;
				}
				else
				{
					$children[$tag['tag']][] = $tag;
				}
			}
			elseif($type == "close")
			{
				break;
			}
		}
		if($this->collapseDuplicates)
		{
			foreach($children as $key => $value)
			{
				if(is_array($value) && (count($value) == 1))
				{
					$children[$key] = $value[0];
				}
			}
		}
		return $children;
	}
	
	/**
	 * Adaptation from Eric Pollman's code
	 *
	 * @param 		array 		
	 * @param 		array 		
	 * @param 		int 		
	 * @param 		string 		
	 * @return 		array 	
	 */
	function build_tag($thisvals, $vals, &$i, $type)
	{
		$tag = array('tag' => $thisvals['tag']);

		if(isset($thisvals['attributes']))
		{
			$tag['attributes'] = $thisvals['attributes'];
		}

		if($type == "complete")
		{
			if(isset($thisvals['value']))
			{
				$tag['value'] = $thisvals['value'];
			}
		}
		else
		{
			$tag = array_merge($tag, $this->getChildren($vals, $i));
		}
		return $tag;
	}

	/**
	 * Encode XML attribute
	 *
	 * @param	string		Tag to work on
	 * @return	string		
	 */
	public function encodeTags($tag)
	{
		$tag = preg_replace("/&(?!#[0-9]+;)/s", '&amp;', $tag);
		$tag = str_replace("<", "&lt;"  , $tag);
		$tag = str_replace(">", "&gt;"  , $tag);
		$tag = str_replace('"', "&quot;", $tag);
		$tag = str_replace("'", '&#039;', $tag);
		
		return $tag;
	}

	/**
	 * Decode XML attribute
	 *
	 * @param	string		Tag to work on
	 * @return	string		
	 */	 
	public function decodeTags($tag)
	{
		$tag = str_replace("&amp;" , "&", $tag);
		$tag = str_replace("&lt;"  , "<", $tag);
		$tag = str_replace("&gt;"  , ">", $tag);
		$tag = str_replace("&quot;", '"', $tag);
		$tag = str_replace("&#039;", "'", $tag);
		
		return $tag;
	}
	
	/**
	 * Encode CDATA
	 *
	 * @param	string		Data to work on
	 * @return	string		
	 */
	public function encodeCDATA($data)
	{
		$data = str_replace("<![CDATA[", "<!#^#|CDATA|", $data);
		$data = str_replace("]]>"      , "|#^#]>"      , $data);
		
		return $data;
	}

	/**
	 * Decode CDATA
	 *
	 * @param	string		Data to work on
	 * @return	string	
	 */
	public function decodeCDATA($data)
	{
		$data = str_replace("<!#^#|CDATA|", "<![CDATA[", $data);
		$data = str_replace("|#^#]>"      , "]]>"      , $data);
		
		return $data;
	}
	
	/**
	 * The XML returned by self::getChildren is very badly parsed
	 * This function unsets any unnecessary XML tags
	 *
	 * @param 		array	
	 * @return		array
	 */
	public function cleanXML($xml)
	{
		foreach($xml as $key => $val)
		{
			if($key == "tag" || $key == "value")
			{
				unset($xml[$key]);
			}
			else if(is_array($val))
			{
				$xml[$key] = $this->cleanXML($val);
				if(count($xml[$key]) <= 0)
				{
					$xml[$key] = $val['value'];
				}
			}
		}
		return $xml;
	}
	
	/**
	 * Cleans off base tags from XML
	 *
	 * @param 		array	
	 * @return		array
	 */
	public function getXMLTag($xml)
	{
		$tag = $this->getXMLSubTag($xml);
		if(array_key_exists('last', $tag))
		{
			unset($tag['last']);
		}
		return $tag;
	}
	
	/**
	 * Accepts an XML tag array & removes un-necessary Base tags.
	 *
	 * @param 		array	
	 * @return		array
	 */
	public function getXMLSubTag($xml)
	{
		foreach($xml AS $tag => $array)
		{
			if(is_array($array))
			{
				$_array = $this->getXMLSubTag($array);
				if(array_key_exists('last', $_array))
				{
					return $_array;
				}
			}
			else
			{
				$myarray[$tag] = $array;
			}
		}
		if(is_array($myarray))
		{
			$myarray['last'] = 1;
			return $myarray;
		}	
	}
}
?>