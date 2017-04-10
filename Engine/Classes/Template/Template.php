<?php
/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.5.0
 * Template Class
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
 
class Template 
{
    /**
	 * GID of the theme
	 * 
	 * @var		integer
	 */
	protected $gid;

	/**
	 * Holds the last template we pulled from the db/file
	 * 
	 * @var		string
	 */
	public $lastRenderedTemplate;

	/**
	 * Global Notification Type
	 * 
	 * @var		string
	 */
	public $globalNotificationType = 'general';

	/**
	 * Global Notification Types
	 * 
	 * @var		array
	 */
	public $globalNotificationTypes = [
		'general' => '',
		'error'   => 'notification-error',
		'warning' => 'notification-warning'
	];

	/**
	 * Holds the HTML Data
	 * 
	 * @var		integer
	 */
	protected $html;

	/**
	 * JS Libraries to load
	 * 
	 * @var		array
	 * @since   0.5.3
	 * @access  protected
	 */
	protected $jsLibraries = [
		'admin' => [
			['global' 		, 'jQuery/jQuery'], /* Must be loaded first */
			['global' 		, 'Ins/Ins'], /* Our core library */
			['global' 		, 'Ajax/Ajax'],
			['login' 		, 'Login/Login'],
			['system' 		, 'Sql/Sql'],
			['customize' 	, [ 'templates' => 'Customize/Templates', 'css' => 'Customize/Css' ]],
			['applications' , [ 'install' => 'Applications/Install' ]]
		],
		'front' => [
			['global' 		, 'jQuery/jQuery'], /* Must be loaded first */
			['global' 		, 'Ins/Ins'], /* Our core library */
			['global' 		, 'Core/Core', TRUE],
		]
	];

	/**
	 * Search Locations Array Map
	 * 
	 * @var		array
	 * @since   0.6.0
	 * @access  protected
	 */
	protected $searchLocations = [
		'admin' => [
			
		],
		'front' => [
			'members' 	=> 'Members',
		]
	];

	/**
	 * Template groups we use
	 * 
	 * @var		array
	 * @since   0.5.3
	 * @access  protected
	 */
	protected $templateGroups = [ 'admin', 'main' ];

	/**
	 * Global notification
	 * 
	 * @var		string
	 * @since   0.3.8
	 * @access  protected
	 */
	public $globalNotification = '';

	/**
	 * Array of fetched templates
	 * 
	 * @var		string
	 * @since   0.6.0
	 * @access  public
	 */
	public $fetchedTemplates = [];
   
    /** 
     * Loads the instance of this class
     *
     * @return      resource
     * @access 		public
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
	 * @param 		integer
	 * @return 		void
	 */
	public function __construct( $id = NULL )
	{
		\INS\Core::checkState( __CLASS__ ); 
		$this->gid = $id;
	}
   
    /**
	 * Gets a template from database
	 * 
	 * @param		string
	 * @param		integer
	 * @return		string
	 */
    public function get( $name, $gid = NULL ) 
    {
    	$gid = ( $gid === NULL ) ? \INS\Core::$settings['theme']['tid'] : $gid;

    	/* Present in Cache? */
    	if( !array_key_exists( $name . '_' . $gid, $this->fetchedTemplates ) )
    	{
    		$template = \INS\Db::i()->fetch
			(
	  			'`template_data`', 'templates', "`name`= ? AND `group`='main' AND (`gid`= ? OR `gid`='-1')", 
	  			'', /* !Limit */
	  			[ 1 => $name, 2 => $gid ]
			);

			$this->fetchedTemplates[ $name . '_' . $gid ] = $template['template_data'];
    	}
    	else
    	{
    		$template['template_data'] = $this->fetchedTemplates[ $name . '_' . $gid ];
    	}

	    return str_replace("\'", "'", addslashes( $template['template_data'] )); 
    }
   
    /**
	 * For Admin $this->input
	 * 
	 * @param		string		Name of template
	 * @param		integer		Theme GID
	 * @return		string
	 */
    public function getAcp( $name, $gid = NULL ) 
    {
    	$gid = ( $gid === NULL ) ? \INS\Core::$settings['admin']['theme'] : $gid;

    	/* Present in Cache? */
    	if( !array_key_exists( $name . '_' . $gid, $this->fetchedTemplates ) )
    	{
			$template = \INS\Db::i()->fetch(
				'`template_data`', 'templates_admin', "`name`= ? AND `group`='admin' AND `gid`= ?", 
				'', /* !Limit */
				[ 1 => $name, 2 => $gid ]
			);
			
			$this->fetchedTemplates[ $name . '_' . $gid ] = $template['template_data'];
		}
    	else
    	{
    		$template['template_data'] = $this->fetchedTemplates[ $name . '_' . $gid ];
    	}

	    return str_replace("\'", "'", addslashes( $template['template_data'] )); 
    }

    /**
	 * Outputs a template
	 * 
	 * @param		string		Template data to output
	 * @return		void
	 */
    public function output( $html, $title = "", $forced = FALSE )
    {
    	$this->html = $html;

		if( !defined('IN_ACP') )
		{	
			/* Don't do anything if it is an AJAX request */
			if( IS_AJAX === TRUE AND $forced === FALSE )
			{
				echo $this->html;
				return;
			}

			$row = \INS\Db::i()->fetch('`lastupdatedate`', 'misc');

			if( $row['lastupdatedate'] == date('d') )
			{
				\INS\Db::i()->update( 'misc', [ 'visitstoday' => 'visitstoday + 1' ] ); 	
			}
			else
			{
				\INS\Db::i()->update( 'misc', [ 'visitstoday' => '0', 'lastupdatedate' => date('d') ] );
			}

			eval( "\$headerinclude = \"".$this->get( "headerinclude" )."\";" );
			eval( "\$sidebar = \"".$this->get( "sidebar" )."\";" );
			eval( "\$header = \"".$this->get( "header" )."\";" );	
			eval( "\$footer = \"".$this->get( "footer" )."\";" );

			$this->loadDocType();
  			$this->parseExtraVariables( 'first' );
			$this->html = str_replace( '{%HEADERINCLUDE%}', $headerinclude, $this->html );
			$this->html = str_replace( '{%HEADER%}', $header, $this->html );
			$this->html = str_replace( '{%TITLE%}', $title, $this->html );
			$this->html = str_replace( '{%FOOTER%}', $footer, $this->html );
			$this->html = str_replace( '{%NOTIFICATION%}', $this->parseNotification(), $this->html );
			$this->html = str_replace( '{%NAVIGATION%}', $this->navigation, $this->html );
			$this->html = str_replace( '{admin}', \INS\Core::$config['admin']['dir'], $this->html );
			$this->html = str_replace( '{crumb}', \INS\Http::i()->crumb, $this->html );
			$this->loadInternalAssets();
			$this->loadJSLibraries( 'front' );
			$this->parseSearchData( 'front' );
			$this->parseSettings();
			$this->parseUserData();
			$this->html = \INS\Language::i()->parseOutput( $this->html );
			$this->html = $this->parseFunctions();
			$this->parseExtraVariables( 'last' );
		}
		else
		{
			$sidebar_links = $this->buildSidebarLinks();

			eval( "\$headerinclude = \"".$this->getAcp( "headerinclude" )."\";" );
			eval( "\$sidebar = \"".$this->getAcp( "sidebar" )."\";" );
			eval( "\$header = \"".$this->getAcp( "header" )."\";" );
			eval( "\$footer = \"".$this->getAcp( "footer" )."\";" );
  			
   			$this->buildNavigation();
  			$this->loadDocType();
  			$this->parseExtraVariables( 'first' );
			$this->html = str_replace( '{%HEADERINCLUDE%}', $headerinclude, $this->html );
			$this->html = str_replace( '{%HEADER%}', $header, $this->html );
			$this->html = str_replace( '{%TITLE%}', $title, $this->html );
			$this->html = str_replace( '{%NOTIFICATION%}', $this->parseNotification(), $this->html );
			$this->html = str_replace( '{%NAVIGATION%}', $this->navigation, $this->html );
			$this->html = str_replace( '{%FOOTER%}', $footer, $this->html );
			$this->html = str_replace( '{admin}', \INS\Core::$config['admin']['dir'], $this->html );
			$this->html = str_replace( '{crumb}', \INS\Http::i()->generateCrumb(), $this->html );
			$this->loadInternalAssets();
			$this->loadJSLibraries();
			$this->parseSettings();
			$this->parseUserData();
			$this->html = \INS\Language::i()->parseOutput( $this->html );
			$this->html = $this->parseFunctions();

			/* Loading Time; Must to it here, because it gets parsed in the template */
			$this->html = str_replace( '{%LOADINGTIME%}', \INS\Language::i()->strings( 'ins_loadingTime' )->parse( round( microtime( TIME ) - \INS\Init::$time['start'], 4 )*1000, \INS\Db::i()->totalQueries ), $this->html );
			$this->parseExtraVariables( 'last' );
		}

        echo $this->html;
        \INS\Core::runFinalDestructs();
    }

    /**
     * Parses Functions inside templates
     *
     * @return 		string 		Parsed HTML
     * @access 		public
     */    
    public function error( $error=NULL, $title=NULL )
    {
    	/* An ajax request? */
    	if( IS_AJAX )
    	{
    		\INS\Json::_error( $error );
    	}

    	$error = ( $error === NULL ) ? \INS\Language::i()->strings[ 'ins_errors_unknown_error' ] : $error;
    	$title = ( $title === NULL ) ? \INS\Language::i()->strings[ 'ins_errorpage_title' ] : $title;

    	$template = $this->getTemplate( 'core', 'error' )->sprintf( $error );

    	$this->output( $template, $title );
    	exit;
    }

	/**
     * Parses Functions inside templates
     *
     * @return 		string 		Parsed HTML
     * @access 		public
     */    
    public function parseFunctions()
    {
    	/* Match all functions */
    	preg_match_all( '#\{\%([\s]+)?function_(.*?)\((.*?)\)([\s]+)?\%\}#', $this->html, $matches );

    	$i = 0;
    	$done = [];
    	foreach( $matches[0] AS $match )
    	{
    		/* Don't waste time on already completed elements */
    		if( in_array( $match, $done ) )
    		{
    			continue;
    		}

    		try {
    			$bits = explode( '_', $matches[2][$i] );
		    	$class = $bits[0];
		    	$function = $bits[1];

		    	$args = array_map( 'trim', explode( ',', str_replace( '\'', '', $matches[3][$i] ) ) );
		    	$call = sprintf( '\\INS\\%s', $class );
		    	
		    	$instance = $call::i();

		    	$this->html = str_replace( $match, call_user_func_array( array( $instance, $function ), $args ), $this->html );

		    	$i++;
		    	$done[] = $match;
    		}
    		catch (\Exception $e)
    		{
    			echo $e->getMessage();
    		}	
    	}

    	return $this->html;
    }

    /**
     * Parses Search Box Data
     *
     * @param  		string 		Admin or Front
     * @return 		void
     * @access 		public
     */
    public function parseSearchData( $where = 'admin' )
    {
    	$i = 1;
    	$searchData = $this->searchLocations[$where];

    	foreach( $searchData AS $location => $string )
    	{
    		$search .= '<div class="ins-checkbox-wrapper"><input type="checkbox" name="' . $location . '" id="' . $location . $i . '" value="' . $location . '" /><label for="' . $location . $i . '">' . $string . '</label></div>';
    		$i++;
    	}	

    	$this->html = str_replace( '{%SEARCHLOCATIONS%}', $search, $this->html );
    }
    /**
	 * Parses extra variables
	 * 
	 * @param 		string      Positions
	 */
    public function parseExtraVariables( $position )
    {
    	switch( $position )
    	{
    		case 'first':
    			if( !empty( $this->dataToBeParsed[ 'first' ] ) )
    			{
    				foreach( $this->dataToBeParsed[ 'first' ] AS $array )
	    			{
	    				$this->html = str_replace( $array['variable'], $array['replacement'], $this->html );
	    			}
    			}
    			break;
    		case 'last':	
    			if( !empty( $this->dataToBeParsed[ 'last' ] ) )
    			{
	    			foreach( $this->dataToBeParsed[ 'last' ] AS $array )
	    			{
	    				$this->html = str_replace( $array['variable'], $array['replacement'], $this->html );
	    			}
	    		}
    			break;
    	}
    }

    /**
	 * Parses extra variables
	 * 
	 * @param 		string      Position
	 */
    public function parseNotification( )
    {
    	return (empty( $this->globalNotification ) ) ? '' : sprintf( '<div class="visible infusionMsgBoxContainer"><div class="infusionMsgBox %s">%s</div></div>', $this->globalNotificationTypes[$this->globalNotificationType], $this->globalNotification );
    }

    /**
	 * Adds a Notification
	 * 
	 * @param 		string      Position
	 */
    public function addNotification( $notification, $type = 'general' )
    {
    	$this->globalNotification = $notification;
    	$this->globalNotificationType = $type;
    }

    /**
	 * Adds extra things that need to be parsed
	 * 
	 * @param 		string 		To be parsed Variable
	 * @param       string      Replacement Data
	 * @param 		string      Position
	 */
    public function addToParser( $variable, $replacement, $position )
    {
    	$this->dataToBeParsed[ $position ][] = [ 'variable' => $variable, 'replacement' => $replacement ];
    }

    /**
	 * Parses settings inside a template
	 * 
	 * @param 		string 		Data to be parsed
	 * @return		string 		Parsed data
	 */
    public function parseSettings( $data = NULL )
    {
    	if( $data !== NULL )
    	{
    		foreach( \INS\Core::$settings AS $key => $array )
		    {
		    	foreach( $array AS $secondkey => $val )    
		    	{
		    		$data = str_replace( '{settings[\'' . $key . '\'][\'' . $secondkey . '\']}', $val, $data );
		    	}
		    }

		    return $data;
    	}

	    foreach( \INS\Core::$settings AS $key => $array )
	    {
	    	foreach( $array AS $secondkey => $val )    
	    	{
	    		$this->html = str_replace( '{settings[\'' . $key . '\'][\'' . $secondkey . '\']}', $val, $this->html );
	    	}
	    }
    }

    /**
	 * Parses user data
	 * 
	 * @param 		string 		Data to be parsed
	 * @return		string 		Parsed data
	 */
    public function parseUserData( $data = NULL )
    {
    	if( !is_array( \INS\Admin::$member->data ) )
    	{
    		return;
    	}

	    foreach( \INS\Admin::$member->data AS $key => $value )
	    {
	    	$this->html = str_replace( '{admin[\'' . $key . '\']}', $value, $this->html );
	    }
    }

    /**
	 * Loads Internal Javascripts
	 * 
	 * @return		void
	 */
    public function loadJSLibraries( $location = NULL )
    {
    	$scripts = '';
    	$jsArray = ( $location === 'front' ) ? $this->jsLibraries['front'] : $this->jsLibraries['admin'];

    	foreach( $jsArray AS $array )
    	{
    		$module   = $array[0];
    		$library  = $array[1];
    		$pushable = ( $array[2] === TRUE ? TRUE : FALSE );
    		
    		/* This library is pushable */
    		if( $pushable )
    		{
    			if( !file_exists( INS_ROOT . '/Engine/FrontEnd/Libraries/' . $library . '_pushed.js' ) OR $this->forceJsPush === TRUE )
    			{
    				$push = sprintf( "\n\njQuery(document).ready( function(){ %s });", $this->jsPushes );
    				$data = file_get_contents( INS_ROOT . '/Engine/FrontEnd/Libraries/' . $library . '.js' );
    				file_put_contents( INS_ROOT . '/Engine/FrontEnd/Libraries/' . $library . '_pushed.js', $data . $push );
    			}

    			$library = $library . '_pushed';
    		}

    		if( $module == 'global' )
    		{
    			$jspath   = \INS\Core::$settings['site']['url'] . '/Engine/FrontEnd/Libraries/' . $library . '.js';
    			$scripts .= '<script type="text/javascript" src="' . $jspath . '"></script>';
    		}
    		else
    		{
    			/* Is this library for this module? */
    			if( INS_MODULE == $module )
    			{
    				/* Do we need to load it inside a section? */
    				if( is_array( $library ) )
	    			{
	    				foreach( $library AS $section => $lib )
	    				{
	    					if( $section == INS_SECTION )
	    					{
	    						$jspath   = \INS\Core::$settings['site']['url'] . '/Engine/FrontEnd/Libraries/' . $lib . '.js';
    							$scripts .= '<script type="text/javascript" src="' . $jspath . '"></script>';
	    					}
	    				}
	    			}
	    			/* Perhaps a global library for this module */
	    			else
	    			{
	    				$jspath   = \INS\Core::$settings['site']['url'] . '/Engine/FrontEnd/Libraries/' . $library . '.js';
    					$scripts .= '<script type="text/javascript" src="' . $jspath . '"></script>';
	    			}
    			}
    		}
    	}

    	$this->html = str_replace( '{%JSLIBRARIES%}', $scripts, $this->html );
    }

    /**
     * Adds JS to be pushed
     *
     * @param 		string   The Js
     * @return 		void
     */
    public function pushJsToStack( $js, $forced=FALSE )
    {
    	if( $forced === TRUE )
    		$this->forceJsPush = TRUE;

    	$this->jsPushes .= $js;
    }

    /**
	 * Loads Internal Assets
	 * 
	 * @return		void
	 */
    public function loadInternalAssets()
    {
    	/* Loader */
    	$assets  = '<link href=\'' . \INS\Core::$settings['site']['url'] . '/Engine/FrontEnd/Assets/General/Assets.css\' rel=\'stylesheet\' type=\'text/css\' />';
    	/* FontAwesome */
    	$assets .= '<link href=\'' . \INS\Core::$settings['site']['url'] . '/Engine/FrontEnd/Assets/General/FontAwesome.css\' rel=\'stylesheet\' type=\'text/css\' />';

    	$this->html = str_replace( '{%ASSETS%}', $assets, $this->html );
    }

    /** 
     * Loads DocType
     *
     * @return 		void
     */
    public function loadDocType() 
    {
    	$this->html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . $this->html;
    }
      
    /**
	 * Updates a template
	 * 
	 * @param 		string 		Name of template
	 * @param 		string 		Content of template
	 * @param 		string 		Template group
	 * @return		void
	 */
    public function update($name, $c, $group)
    {
	    $g = ($group == "admin") ? "templates_admin" : "templates";
		\INS\Db::i()->update( $g, array('template_data' => '?'), '`name` = ? AND `group` = ?', '', array( 1 => $c, 2 => $name, 3 => $group ) );
    }
   
    /**
	 * Deletes a template
	 * 
	 * @param		string		Name of the template
	 * @param		string		Group of the template
	 * @return		void
	 */ 
    public function delete( $name, $group )
    {
    	if( !in_array( $group, $this->templateGroups ) )
    	{
    		return;
    	}

    	$templategroup = ( $group == "admin" ) ? "templates_admin" : "templates";  
	    \INS\Db::i()->delete( $templategroup, "name='{$name}' AND `group`='{$group}'" );
    }
   
    /**
	 * Inserts a new template in Db
	 * 
	 * @param		string		Name of the template
	 * @param		string		Content of the template
	 * @param		string		Group of the template	
	 * @param		integer		Gid of the theme
	 * @return		void
	 */
    public function insert( $name, $content, $group, $gid )
    {  
    	/* Parse Contents */		
		\INS\Db::i()->insert( 
			( $group === 'admin' ? 'templates_admin' : 'templates' ), 
			[
				'gid' => '?',
				'original_template_data' => '?',
				'template_data' => '?',
				'name' => '?',
				'group' => '?'
			],
			[
				1 => $gid,
				2 => $content,
				3 => $content,
				4 => $name,
				5 => $group
			] 
		);
    }
   
    /**
	 * Parses a template for output
	 * 
	 * @return		string
	 */
    public function parseContents( $content )
    {
		return addslashes( $content );
    }
   
    /**
	 * Reverts a template to default
	 * 
	 * @param		string		Name of the template
	 * @return		void
	 */
	public function revert( $name )
	{
		$query    = "SELECT `original_template_data` FROM templates WHERE name='".$name."' AND gid='".$this->id."'";
		$tofetch  = \INS\Db::i()->query( $query );
		$original = $tofetch->fetch_row();
		$original = $original['original_template_data'];
		$query    = "UPDATE `templates` SET `template_data` = '{$original}' WHERE name = '{$name}' AND gid = '{$this->tid}'";
		\INS\Db::i()->query($query);
	}
   
   /**
    * Gets a theme name by its GID
	* 
	* @param 	integer
	* @return 	string
	*/
	public function getThemeNameByGid( $gid )
	{
		return \INS\Db::i()->f( '`name`', 'themes', '`gid`=?', NULL, [ 1 => $gid ] )->get();
	}
	
   /**
    * Performs a replacement in a single template
	* 
	* @param 	string		Name of the template to work on
	* @param    integer		Gid of theme 
	* @param	string		What to replace?
	* @param	string		Replacement
	* @param	integer		Admin template or not
	* @return 	void
	*/
	public function replace($name, $gid, $toreplace, $replacewith, $admin = 0)
	{
		if($admin == 0)
		{
			$data = $this->get($name, $gid);
			$group = "admin";
		}	
		else
		{
			$data = $this->getAcp($name, $gid);
			$group = "main";
		}	
			
		$data = str_replace( $toreplace, $replacewith, $data);
		$this->update($name, $data, $group);
	}
	
   /**
    * Performs a replacement in all available template
	* 
	* @param 	string		What to replace?
	* @param    string		Replace
	* @param	integer		Is this an admin template?
	* @return 	void
	*/
	public function replaceAll($toreplace, $replacewith, $admin = 0)
	{
		if($admin == 0)
		{
			$rows = \INS\Db::i()->fetchALL('*', 'templates');
			foreach($rows AS $row)
			{
				$row['template_data'] = str_replace("\'", "'", addslashes($row['template_data'])); 
				$row['template_data'] = stripslashes($row['template_data']);
				$data = str_replace("{$toreplace}", "{$replacewith}", $row['template_data']);
				$this->update($row['name'], $data, "main");
			}
		}	
		else
		{
			$rows = \INS\Db::i()->fetchAll('*', 'templates_admin');
			foreach($rows AS $row)
			{
				$row['template_data'] = str_replace("\'", "'", addslashes($row['template_data'])); 
				$row['template_data'] = stripslashes($row['template_data']);
				$data = str_replace("{$toreplace}", "{$replacewith}", $row['template_data']);
				$this->update( $row['name'], $data, "admin" );
			}
		}	
	}

	/**
	 * Adds an item to navigation
	 *
	 * @param       string 		Nav name
	 * @param       string      Nav link
	 * @return 		string
	 * @access 		public
	 */ 
	public function addToNavigation( $name, $link = NULL )
	{	
		if( $link === NULL )
		{
			eval("\$data = \"".$this->getAcp("navigation_links_textonly")."\";");
		}
		else
		{
			eval("\$data = \"".$this->getAcp("navigation_links")."\";");
		}

		$this->navItems = $this->navItems . $data;
	}

	/**
	 * Builds the AdminCP navigation
	 *
	 * @return 		string
	 */ 
	public function buildNavigation()
	{	
		eval("\$this->navigation = \"".$this->getAcp("navigation")."\";");
	}

	/**
	 * Builds sidebar links
	 *
	 * @access 		public
	 * @since		0.3.0
	 */
	public function buildSidebarLinks()
	{	 
		$rows = \INS\Db::i()->buildQuery( 'select' )
		                    ->columns( 's.*, m.id, m.name AS parent, m.icon AS parenticon' )
		                    ->table( 'sidebar_sublinks s' )
		                    ->leftJoin( 'sidebar m', 's.id = m.id' )
		                    ->orderBy( 's.id' )
		                    ->complete();

		foreach( $rows AS $key => $value)
		{
			$_rows[ $value['id'] ][ $value['node'] ][] = $value;
		}

		foreach( $_rows AS $id => $nodalArray )
		{
			foreach( $nodalArray AS $node => $sidebarArray )
			{
				foreach( $sidebarArray AS $sidebar )
				{
					$link = \INS\Http::i()->link( $sidebar['app'], $sidebar['module'], $sidebar['section'] );
					eval("\$sublinks_link .= \"".$this->getAcp("sidebar_sublinks_link")."\";");
				}

				eval("\$sublinks .= \"".$this->getAcp("sidebar_sublinks")."\";");
				$sublinks_link = '';		
			}

			$sidebarItemLink = ( $sidebar['id'] == 1 ) ? \INS\Http::i()->link( 'core', 'overview', 'dashboard' ) : \INS\Http::i()->link( $sidebar['app'], $sidebar['module'], 'FALSE' );

			$sidebar['active'] = ( INS_MODULE == $sidebar['module'] ) ? "active" : '';
			eval("\$links .= \"".$this->getAcp("sidebar_links")."\";");
			$sublinks = '';
		}	

		return $links;
	}

	/**
	 * Build pagination for pages.
	 *
	 * @param 	int 		The current page we're on
	 * @param 	int 		The number of items per page
	 * @param 	int 		The total number of items in this collection
	 * @param 	string 		The URL for pagination of this collection
	 * @return 	string 		The built pagination
	 */
	static public function buildPagination( $page, $per_page, $total_items, $url )
	{
		if($total_items <= $per_page)
		{
			return;
		}

		$pages = ceil($total_items / $per_page);

		$pagination = "<div class=\"pagination\"><span class=\"pages\">" . \INS\Language::i()->strings['pages'] . ": </span>\n";

		if($page > 1)
		{
			$prev = $page-1;
			$prev_page = $url.'&page='.$prev;
			$pagination .= "<a href=\"{$prev_page}\" class=\"t pagination_previous\">&laquo; " . \INS\Language::i()->strings['previous'] . "</a> \n";
		}
		
		$max_links = \INS\Core::$settings['admin']['resultsperpage'];

		$from = $page-floor(\INS\Core::$settings['admin']['resultsperpage']/2);
		$to = $page+floor(\INS\Core::$settings['admin']['resultsperpage']/2);

		if($from <= 0)
		{
			$from = 1;
			$to = $from+$max_links-1;
		}

		if($to > $pages)
		{
			$to = $pages;
			$from = $pages-$max_links+1;
			if($from <= 0)
			{
				$from = 1;
			}
		}

		if($to == 0)
		{
			$to = $pages;
		}


		if($from > 2)
		{
			$first = $url.'&page=1';
			$pagination .= "<a href=\"{$first}\" title=\"" . \INS\Language::i()->strings['page'] . " 1\" class=\"t pagination_first\">1</a> ... ";
		}

		for($i = $from; $i <= $to; ++$i)
		{
			$page_url = $url.'&page='.$i;
			if($page == $i)
			{
				$pagination .= "<span class=\"pagination_current\">{$i}</span> \n";
			}
			else
			{
				$pagination .= "<a href=\"{$page_url}\" class=\"t\" title=\"" . \INS\Language::i()->strings['page'] . " {$i}\">{$i}</a> \n";
			}
		}

		if($to < $pages)
		{
			$last = $url.'&page='.$pages;
			$pagination .= "... <a href=\"{$last}\" title=\"" . \INS\Language::i()->strings['page'] . " {$pages}\" class=\"t pagination_last\">{$pages}</a>";
		}

		if($page < $pages)
		{
			$next = $page+1;
			$next_page = $url.'&page='.$next;
			$pagination .= " <a href=\"{$next_page}\" class=\"t pagination_next\"> " . \INS\Language::i()->strings['next'] . " &raquo;</a>\n";
		}

		$pagination .= "</div>\n";
		return $pagination;
	}

	/**
	 * Gets a template from our db/files and returns an object
	 *
	 * @param 	string 		Name of the app where we should look for the template
	 * @param 	string 		The module
	 * @param 	string 		The template file name
	 * @param   object 		[Optional] The object that should be returned / Return Self
	 * @return  object      \INS\Template or $object
	 */
	public function getTemplate( $app, $module, $part = NULL, $object = NULL )
	{
		$name = ( $part === NULL ) ? sprintf( '%s_%s', $app, $module ) : sprintf( '%s_%s_%s', $app, $module, $part );

		/* Check if the template exists in cache */
		if( array_key_exists( $name, $this->fetchedTemplates ) )
		{
			$data = $this->fetchedTemplates[ $name ];
		}
		/* Nope, doesn't exist in cache, get the template */
		else
		{
			$data = $this->get( $name );
		}

		/* Add this to the last rendered templated for easier modification */
		$this->lastRenderedTemplate = str_replace( '\"', '"', $data );

		/* Return the object supplied as argument or a self instance */
		return ( $object !== NULL ) ? ( is_object( $object ) ? $object : static::i() ) : static::i();
	}

	/**
	 * Gets a template from our db/files and returns an object
	 *
	 * @return 		string
	 */
	public function sprintf()
	{
		$args = func_get_args();
		$numargs = func_num_args();

		for( $i = 0; $i < $numargs; $i++ )
		{
			$this->lastRenderedTemplate = str_replace( '{' . ($i+1) . '}', $args[ $i ], $this->lastRenderedTemplate  );
		}

		return $this->lastRenderedTemplate;
	}

	/**
	 * Pushes templates to cache
	 *
	 * @param 		string
	 * @return 		void
	 */
	public function pushToCache( $_templates, $gid=NULL )
	{
		$identifier = ( $gid === NULL ) ? '' : '_' . $gid;
		try 
		{	
			$templates = \INS\Db::i()->fetchAll( 
				'template_data, name', 
				( IN_ACP === TRUE ? 'templates_admin' : 'templates' ),  
				sprintf( "`name` IN(%s) AND `group`=? AND (`gid`= ? OR `gid`='-1')", $_templates ),
				'', /* !Limit */
	  			[ 
	  				1 => ( IN_ACP === TRUE ? 'admin' : 'main' ), 
	  				2 => ( $gid === NULL ) ? ( IN_ACP === TRUE ? \INS\Core::$settings['admin']['theme'] : \INS\Core::$settings['theme']['tid'] ) : $gid 
	  			]
			);

			foreach( $templates AS $template )
			{
				$this->fetchedTemplates[ $template['name'] . $identifier ] = $template['template_data'];
			}
		}
    	catch( \Exception $e )
    	{
    		echo $e->getMessage();
    	}
	}
} 
?>