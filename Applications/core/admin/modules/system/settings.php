<?php
/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.5.0
 * Settings Class
 * Last Updated: $Date: 2014-10-28 5:35 (Tue, 26 Oct 2014) $
 * </pre>
 * 
 * @author      $Author: AskAmn$
 * @package     IN.CMS
 * @copyright   (c) 2014 Infusion Network Solutions
 * @license     http://www.infusionnetwork/licenses/license.php?view=main&version=2014  Revision License 0.1.1
 * @since       0.5.0
 * @version     Release: 0510
 */

class system_settings Extends \INS\Admin
{
	/**
	 * The complete link to this module
	 *
	 * @var		string	
	 */
	public $link;
	
	/**
	 * Constructor
	 *
	 * @return 		void
	 */
	public function __construct()
	{
		$this->title = \INS\Language::i()->strings['admin_modules_system_settings_title'];
		$this->link = \INS\Http::i();
		\INS\Template::i()->addToNavigation( \INS\Language::i()->strings['admin_modules_system_settings_navtitle'], $this->link );
	}
	
	/**
	 * Selects which page to display
	 *
	 * @return		void
	 */
	public function Execute()
	{	
		switch( \INS\Http::i()->request )
		{
			case 'create': 
				$this->_create();
				break;
			default:
				$this->_default();
				break;
		}
    } 

    /**
	 * Create a setting/group
	 *
	 * @return 		void
	 */
    public function _create()
    {
    	switch( \INS\Http::i()->create )
		{
			case 'setting': 
				$this->_createSetting();
				break;
			case 'group': 
				$this->_createGroup();
				break;
			default:
				$this->_default();
				break;
		}
	}

	/**
	 * Creates new settings group
	 *
	 * @access      public
	 * @return 		void
	 */
	public function _createSetting()
	{
    	$this->title = \INS\Language::i()->strings['admin_settings_createsettings_setting'];
    	\INS\Template::i()->addToNavigation( \INS\Language::i()->strings['admin_settings_createsettings_setting'] );

    	if( \INS\Http::i()->request_method == 'post' )
    	{
    		$required = [ 'name', 'name_string', 'name_desc', 'type', 'identifier', 'gid' ];

    		try
    		{
    			$insert = [];
    			$i = 1;
	    		array_walk( $required, function( $v ) use( &$insert, &$i ){
	    			if( !mb_strlen( \INS\Http::i()->$v ) )
	    			{
	    				throw new \Exception( 'admin_settings_create_setting_fieldsempty' );
	    			}

	    			$insert[ $i++ ] = \INS\Http::i()->$v;
	    		} );

	    		if( mb_strlen( \INS\Http::i()->value ) )
	    		{
	    			$required[] = 'value';
	    			$insert[ $i ] = \INS\Http::i()->value;
	    		}

	    		\INS\Db::i()->insert( 'settings', array_fill_keys( $required, '?' ), $insert );
	    		\INS\Template::i()->addNotification( \INS\Language::i()->strings['admin_settings_create_setting_success'] );
	    		\INS\Core::rebuildSettings();
	    	}
	    	catch( \Exception $e )
	    	{
	    		\INS\Template::i()->addNotification( \INS\Language::i()->strings[ $e->getMessage() ], 'error' );
	    	}
    	}

    	$group = \INS\Http::i()->identifier;
    	$gid   = \INS\Http::i()->gid;

    	eval("\$this->html = \"".\INS\Template::i()->getAcp("settings_create_settingssetting")."\";");
		\INS\Template::i()->output( $this->html, $this->title );
    }

	/**
	 * Creates new settings group
	 *
	 * @access      public
	 * @return 		void
	 */
	public function _createGroup()
	{
    	$this->title = \INS\Language::i()->strings['admin_settings_createsettingsgroup'];
    	\INS\Template::i()->addToNavigation( \INS\Language::i()->strings['admin_settings_createsettingsgroup'] );

    	if( \INS\Http::i()->request_method == 'post' )
    	{
    		/* For the GID */
    		$settings = \INS\Db::i()->rowCount( 'settings_group' );
    		$required = [ 'identifier', 'group_title', 'group_desc' ];

    		try
    		{
    			$insert = [];

	    		array_walk( $required, function( $v ) use( &$insert ){
	    			if( !mb_strlen( \INS\Http::i()->$v ) )
	    			{
	    				throw new \Exception( 'admin_settings_create_' . $v . '_empty' );
	    			}

	    			$insert[ $v ] = \INS\Http::i()->$v;
	    		} );

	    		$insert['gid'] = $settings + 1;
	    		
	    		\INS\Db::i()->insert( 'settings_group', $insert );
	    		\INS\Template::i()->addNotification( \INS\Language::i()->strings['admin_settings_create_group_success'] );
	    	}
	    	catch( \Exception $e )
	    	{
	    		\INS\Template::i()->addNotification( \INS\Language::i()->strings[ $e->getMessage() ], 'error' );
	    	}
    	}

    	eval("\$this->html = \"".\INS\Template::i()->getAcp("settings_create_settingsgroup")."\";");
		\INS\Template::i()->output( $this->html, $this->title );
    }
	
	/**
	 * View General Settings
	 *
	 * @return 		void
	 */
	public function _default()
	{
		$group = \INS\Db::i()->escapeString( \INS\Http::i()->group );
		$gid = intval( \INS\Http::i()->gid );

		/**
		 * No group selected
		 */	
		if( empty( $group ) && empty( $gid ) )
		{
			$generate_settings_list = $this->buildSettingsGroups();
			eval("\$this->html = \"".\INS\Template::i()->getAcp("settings")."\";");
			\INS\Template::i()->output( $this->html, $this->title );
		}
		
		/**
		 * We have a group
		 */
		elseif( mb_strlen( $group ) )
		{
			$returned = $this->buildSettingsGroup($group, $gid);
			$generate_settings_group = $returned['content'];
			\INS\Language::i()->strings['admin_settings_editsettingsforgroup'] = \INS\Language::i()->strings( 'admin_settings_editsettingsforgroup' )->parse( $returned['group'], $grouptitle );
			
			if( \INS\Http::i()->request_method == 'post' && isset(\INS\Http::i()->submit) )
			{
				\INS\Template::i()->globalNotification = \INS\Language::i()->strings['admin_settings_updated'];

				foreach( $returned['inputs'] AS $input )
				{  
					/* We need some additional Checks for these settings */
					if( $input === 'regqa' )
					{	
						$questions = count( array_filter( explode( PHP_EOL, \INS\Http::i()->$input ) ) );
					}
					
					if( $input === 'regqa_a' )
					{
						$answers = count( array_filter( explode( PHP_EOL, \INS\Http::i()->$input ) ) );

						if( $questions !== $answers )
						{
							\INS\Template::i()->globalNotification = \INS\Language::i()->strings['admin_settings_regqa_do_not_match'];
							break;
						}
					}

					\INS\Db::i()->update( 'settings', array( 'value' => '?' ), 'name = ?', '', array( 1 => \INS\Http::i()->$input, 2 => $input ) );
				}
 	
				\INS\Core::rebuildSettings();
				
				$returned = $this->buildSettingsGroup($group, $gid);
				$generate_settings_group = $returned['content'];
			}	
		  
		  	\INS\Template::i()->addToNavigation( \INS\Language::i()->strings['admin_modules_system_settings_settinggroup_navtitle'] . $returned['group'], $this->link . '&gid=' . $returned['gid'] );
			eval("\$this->html = \"".\INS\Template::i()->getAcp("settings_group")."\";");
			\INS\Template::i()->output( $this->html, $returned['group'] );
		}
	}
	
	/**
	 * Builds the list of available setting groups	
	 *
	 * @return		string
	 */
	function buildSettingsGroups() 
	{  
		$rows = \INS\Db::i()->fetchAll( '*', 'settings_group' );
	   
		$string = [];
		$string['content'] = "";
	   
		foreach( $rows AS $row )
		{
			$string['content'] .= "<tr>";                          
			$string['content'] .= "<td>";
			$string['content'] .= "<strong style=\"font-size: 17px;\">";
			$string['content'] .= '<a href="'. $this->link . "&group={$row['identifier']}&gid={$row['gid']}\" style=\"color: #000\">{$row['group_title']}</a>";
			$string['content'] .= "</strong>";
			$string['content'] .= "<br />";
			$string['content'] .= "{$row['group_desc']}";
			$string['content'] .= "</td>";
			$string['content'] .= "<td width=\"12%\">";
			$string['content'] .= "<a href=\"{$this->link}&request=create&create=setting&gid={$row['gid']}&identifier={$row['identifier']}\" class=\"btn ins-btn\">" . \INS\Language::i()->strings['admin_settings_createnewsetting'] . "</a>";
			$string['content'] .= "</td>";
			$string['content'] .= "</tr>";
		}
		 
		return $string['content'];
	} 
	
	/**
	 * Builds the settings under a particular group
	 *
	 * @return		string
	 */
	function buildSettingsGroup($group, $gid) 
	{
		/*$rows = \INS\Db::i()->query('
		SELECT s.*, g.group_title FROM <%P%>settings s 
		LEFT JOIN <%P%>settings_group g 
		ON (s.gid = g.gid) 
		WHERE s.identifier = ? AND s.gid = ?', 
		array( 'rows' => 2 ), array( 1 => $group, 2 => $gid ) );*/

		$rows = \INS\Db::i()->buildQuery( 'select' )
					->columns( 's.*, g.group_title' )
					->table( 'settings s' )
					->leftJoin( 'settings_group g', 's.gid = g.gid' )
					->where( 's.identifier = ? AND s.gid = ?' )
					->bind( array( 1 => $group, 2 => $gid ) )
					->complete();

		$string = [];
		$string['content'] = "<div class=\"form-inline\">";
		$i = 0;

		foreach( $rows AS $row )
		{ 
			/* We have some sub-members */
			if($row['parent'] != "")
			{
				$sub = \INS\Db::i()->fetch('`name`', 'settings', 'sid = ?', '', [ 1 => $row['parent'] ]);
				$data = "data-parent=\"{$sub['name']}\"";
			}
		
			$row['value'] = \INS\Filter::i()->filter( $row['value'] );
			/* 1. Text fields */
			if($row['type'] == "text")
			{
				$string['content'] .= "<div class=\"form-row bordered\">";
				$string['content'] .= "<label class=\"form-label\">{$row['name_string']}:</label>";
				$string['content'] .= "<div class=\"form-item\">";
				$string['content'] .= "<input type=\"{$row['type']}\" {$data} class=\"large\" name=\"{$row['name']}\" value=\"{$row['value']}\">";
				$string['content'] .= "<div class=\"form-row-desc\">{$row['name_desc']}</div>";
				$string['content'] .= "</div>";
				$string['content'] .= "</div>";
				$string['inputs'][] = $row['name'];
			}

			/* 2. Radios */
			elseif($row['type'] == "yesno")
			{
				if($row['value'] == 1)
				{	
					$yeschecked = 'checked=""';
					$nochecked  = "";
				}
				else
				{
					$yeschecked = "";
					$nochecked  = 'checked=""';
				}
				$radio = "<label class=\"yes\"><input type=\"radio\" class=\"yesno_yes\" name=\"{$row['name']}\" $yeschecked value=\"1\" />";
				$radio .= "<span style=\"vertical-align: middle\">&nbsp;Yes</span></label>";
				$radio .= "&nbsp;&nbsp;"; /* Space the YES/NO Option */
				$radio .= "<label class=\"no\"><input type=\"radio\" class=\"yesno_no\" value=\"0\" $nochecked name=\"{$row['name']}\" />";
				$radio .= "<span style=\"vertical-align: middle\">&nbsp;No</span></label>";
				
				$string['content'] .= "<div class=\"form-row bordered\">";
				$string['content'] .= "<label class=\"form-label\">{$row['name_string']}:</label>";
				$string['content'] .= "<div class=\"form-item\">";
				$string['content'] .= "{$radio}";
				$string['content'] .= "<div class=\"form-row-desc\">{$row['name_desc']}</div>";
				$string['content'] .= "</div>";
				$string['content'] .= "</div>";
				$string['inputs'][] = $row['name'];	
			}	

			/* 3. TextAreas */
			if($row['type'] == "textarea")
			{
				$string['content'] .= "<div class=\"form-row bordered\">";
				$string['content'] .= "<label class=\"form-label\">{$row['name_string']}:</label>";
				$string['content'] .= "<div class=\"form-item\">";
				$string['content'] .= "<textarea class=\"textarea large\" name=\"{$row['name']}\" style=\"height: 200px\">{$row['value']}</textarea>";
				$string['content'] .= "<div class=\"form-row-desc\">{$row['name_desc']}</div>";
				$string['content'] .= "</div>";
				$string['content'] .= "</div>";
				$string['inputs'][] = $row['name'];
			}
			$i++;
		}

		$string['content'] .= "</div>";
		$string['group'] = $row['group_title'];
		$string['gid'] = $row['gid'];
		return $string;
	}	
}	
?>