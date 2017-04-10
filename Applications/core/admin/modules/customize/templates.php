<?php
/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.5.0
 * Templates
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

class customize_templates Extends \INS\Admin
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
		$this->title = \INS\Language::i()->strings['admin_modules_system_phpinfo_title'];
		$this->link = \INS\Http::i()->link();
		\INS\Template::i()->addToNavigation( \INS\Language::i()->strings['admin_modules_customize_templates_navtitle'], $this->link );
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
			case 'overview':
				$this->_default();
			break;
			case 'list':
				\INS\Template::i()->addToNavigation( \INS\Template::i()->getThemeNameByGid( intval( \INS\Http::i()->gid ) ), 
					$this->link . '&request=list&gid=' . intval( \INS\Http::i()->gid ) . '&group=' . \INS\Http::i()->group
				);
				\INS\Template::i()->addToNavigation( \INS\Language::i()->strings['admin_modules_customize_templates_list_navtitle'] );
				$this->_list();
			break;
			case 'new':
				\INS\Template::i()->addToNavigation( \INS\Template::i()->getThemeNameByGid( intval( \INS\Http::i()->gid ) ), 
					$this->link . '&request=list&gid=' . intval( \INS\Http::i()->gid ) . '&group=' . \INS\Http::i()->group
				);
				\INS\Template::i()->addToNavigation( \INS\Language::i()->strings['admin_modules_customize_templates_new_navtitle'], 
					$this->link . '&request=new&gid=' . intval( \INS\Http::i()->gid ) . '&group=' . \INS\Http::i()->group 
				);
				$this->_new();
			break;
			case 'edit':
				\INS\Template::i()->addToNavigation( \INS\Template::i()->getThemeNameByGid( intval( \INS\Http::i()->gid ) ), 
					$this->link . '&request=list&gid=' . intval( \INS\Http::i()->gid ) . '&group=' . \INS\Http::i()->group
				);
				\INS\Template::i()->addToNavigation( 
					\INS\Language::i()->strings['admin_modules_customize_templates_edit_navtitle'] . \INS\Http::i()->name, 
					$this->link . '&request=edit&gid=' . intval( \INS\Http::i()->gid ) . '&group=' . \INS\Http::i()->group . '&name=' . \INS\Http::i()->name
				);
				$this->_edit();
			break;
			case 'delete':
				$this->_delete();
			break;
			case 'ajax':
				$this->_ajax();
			break;
			default:
				$this->_default();
		}
    } 
	
	/**
	 * @brief 	Default Request
	 */
	public function _default()
	{
		$default = intval( \INS\Http::i()->set_default );
		
		if( !empty( $default ) )
		{
			\INS\Db::i()->update('settings', [ 'value' => '?' ], "name='tid'", '', [ 1 => $default ]);	
			\INS\Core::rebuildSettings();
			\INS\Http::i()->redirect( $this->link );
		}
		
		$theme_list = $this->generateThemeList();
		eval( "\$this->html = \"".\INS\Template::i()->getAcp("themes")."\";" );
		\INS\Template::i()->output( $this->html, \INS\Language::i()->strings['admin_modules_templates_overview_title'] );
	}
	
	/**
	 * @brief 	List Of Templates
	 */
	public function _list()
	{
		$page = !isset( \INS\Http::i()->page ) ? 1 : intval(\INS\Http::i()->page);
		$group = \INS\Db::i()->escapeString( \INS\Http::i()->group );
		$gid = intval( \INS\Http::i()->gid );

		if( \INS\Http::i()->group == "admin")
		{
			if( INS_DEV_MODE === TRUE )	
			{
				$generate_tpl_list = $this->buildAdminTemplatesList( intval( \INS\Http::i()->gid ) );
				$num = \INS\Db::i()->rowCount('templates_admin');
			}
			else
			{	
				header("{$_SERVER['SERVER_PROTOCOL']} 404 Not Found"); 
				die( \INS\Language::i()->strings['send_404_header'] );
			}
		}
		elseif( \INS\Http::i()->group == "main" )
		{
			$generate_tpl_list = $this->buildWebsiteTemplatesList( intval( \INS\Http::i()->gid ) );		
			$num = \INS\Db::i()->rowCount( 'templates', '`gid` = \'' .  intval( \INS\Http::i()->gid ) . '\'');
		}
		else
		{
			die( 'That group ain\'t ours.' );
		}
		
		$pagination = \INS\Template::i()->buildPagination($page, \INS\Core::$settings['admin']['resultsperpage'], $num, $this->link . '&request=list&gid=' . intval( \INS\Http::i()->gid ) . '&group=' . \INS\Http::i()->group);

		eval("\$this->html = \"".\INS\Template::i()->getAcp("themes_templates")."\";");	
		\INS\Template::i()->output( $this->html, \INS\Language::i()->strings['admin_modules_templates_list_title'] );	  
	}
	
	/**
	 * @brief 	Delete Request
	 */
	public function _delete()
	{
		\INS\Template::i()->globalNotification = \INS\Language::i()->strings['admin_globalnotification_customize_templatedeleted'];
		\INS\Template::i()->delete( \INS\Db::i()->escapeString( \INS\Http::i()->name ), \INS\Http::i()->group );
		
		if(\INS\Http::i()->group == "admin")
		{
			if(INS_DEV_MODE === TRUE)	
			{
				$generate_tpl_list = $this->buildAdminTemplatesList( intval( \INS\Http::i()->gid ) );
			}
			else
			{
				header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found"); 
				die(\INS\Language::i()->strings['send_404_header']);
			}
		}
		elseif( \INS\Http::i()->group == "main")
		{
			$generate_tpl_list = $this->buildWebsiteTemplatesList( intval( \INS\Http::i()->gid ) );		
		}
		else
		{
			die( 'That group ain\'t ours.' );
		}
		
		eval("\$this->html = \"".\INS\Template::i()->getAcp("themes_templates")."\";");	
		\INS\Template::i()->output( $this->html, \INS\Language::i()->strings['admin_modules_templates_list_title'] );	  
	}
	
	/**
	 * @brief 	Create Request
	 */
	public function _new()
	{
		$gid = intval( \INS\Http::i()->gid );
		$group = \INS\Db::i()->escapeString( \INS\Http::i()->group );
		
		if( \INS\Http::i()->request_method == 'post' && isset( \INS\Http::i()->submit ) )	
		{  
			if( !mb_strlen( \INS\Http::i()->templatename ) )
			{
				\INS\Template::i()->globalNotification = \INS\Language::i()->strings['admin_modules_templates_nameblank'];
				
				$template_data = \INS\Http::i()->template_data;
				eval( "\$this->html = \"".\INS\Template::i()->getAcp("template_create")."\";" );
				\INS\Template::i()->output( $this->html, \INS\Language::i()->strings['admin_modules_templates_create_title'] );	
			}

			$template_name = \INS\Http::i()->templatename;
			$template_data = \INS\Http::i()->template_data;

			$group = ($gid == 1) ? 'admin' : 'main';

			\INS\Template::i()->insert( $template_name, $template_data, $group, $gid );
			\INS\Template::i()->globalNotification = \INS\Language::i()->strings['admin_globalnotification_customize_templatecreated'];
		}

		eval("\$this->html = \"".\INS\Template::i()->getAcp("templates_create")."\";");	
		\INS\Template::i()->output( $this->html, \INS\Language::i()->strings['admin_modules_templates_create_title'] );	  
	}
	
	/**
	 * @brief 	Edit Request
	 */
	public function _edit()
	{
		$gid = intval( \INS\Http::i()->gid );
		$name = \INS\Db::i()->escapeString( \INS\Http::i()->name );
		$group = \INS\Db::i()->escapeString( \INS\Http::i()->group );
		
		if( \INS\Http::i()->group == "admin" )
		{
			if(defined("INS_DEV_MODE"))	
			{
				$template_data = stripslashes(htmlentities(\INS\Template::i()->getAcp($name, $gid)));
				$template_name = $name;
			}
			else
			{
				header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found"); 
				die( \INS\Language::i()->strings['send_404_header'] );
			}
		}
		elseif( \INS\Http::i()->group == "main" )
		{
			$template_data = stripslashes(htmlentities(\INS\Template::i()->get($name, $gid)));
			$template_name = $name;			
		}
		else
		{
			die( 'That group ain\'t ours.' );
		}

		if ( \INS\Http::i()->request_method == 'post' AND isset( \INS\Http::i()->submit ) )
		{
			\INS\Template::i()->globalNotification = \INS\Language::i()->strings['admin_globalnotification_customize_templateupdated'];
			$template_data = \INS\Http::i()->template_data;

			/* Strip slashes, because template from database is escaped */
			$template_data = stripslashes($template_data);
			
			\INS\Template::i()->update($name, $template_data, $group);
			\INS\Http::i()->redirect( $this->link . "&request=edit&name={$name}&group={$group}&gid={$gid}" );
		}					

		\INS\Template::i()->addToParser( '{%TEMPLATEDATA%}', $template_data, 'last' );
		eval("\$this->html = \"".\INS\Template::i()->getAcp("templates_edit")."\";");	
		\INS\Template::i()->output( $this->html, \INS\Language::i()->strings['admin_modules_templates_edit_title'] . ': ' . $template_name );	  
	}
	
	/**
	 * Build ACP Templates List
	 * 
	 * @param 		integer
	 * @return		string
	 */
    public function buildAdminTemplatesList( $gid )
    {
		$page = !isset( \INS\Http::i()->page ) ? 1 : intval( \INS\Http::i()->page );

		$max = \INS\Core::$settings['admin']['resultsperpage'];
		$limit = $page*\INS\Core::$settings['admin']['resultsperpage'];
		$_limit = $limit-\INS\Core::$settings['admin']['resultsperpage'];
			
		$rows = \INS\Db::i()->fetchAll('*', 'templates_admin', '`group`=\'admin\' AND `gid` = ? ORDER BY `name`', "{$_limit}, {$max}", [ 1 => $gid ]);

		foreach($rows AS $row)
		{
			eval("\$themes_admin_templates .= \"".\INS\Template::i()->getAcp("themes_admin_templates")."\";");
		}

		return $themes_admin_templates;
    }
	
	/**
	 * List of templates associated with our main website
	 * 
	 * @param		integer
	 * @return		string
	 */
    public function buildWebsiteTemplatesList( $gid )
    {	   
		$page = !isset( \INS\Http::i()->page ) ? 1 : intval( \INS\Http::i()->page );
		
		$max = \INS\Core::$settings['admin']['resultsperpage'];
	    $limit = $page * \INS\Core::$settings['admin']['resultsperpage'];
		$_limit = $limit-\INS\Core::$settings['admin']['resultsperpage'];
		
		$rows = \INS\Db::i()->fetchAll('*', 'templates', '`group`=\'main\' AND `gid` = ? ORDER BY `name`', "{$_limit}, {$max}", [ 1 => $gid ]);
	   
		foreach($rows AS $row)
	    {
			eval("\$themes_admin_templates .= \"".\INS\Template::i()->getAcp("themes_admin_templates")."\";");
		}

		return $themes_admin_templates;
    }
	
	/**
	 * Theme list
	 * 
	 * @return		string
	 */ 
	public function generateThemeList()
	{
		$rows = \INS\Db::i()->fetchAll('*', 'themes');
		
		foreach ($rows AS $row)
		{ 
			if($row['group'] === 'admin')
			{
				if(!defined("INS_DEV_MODE"))	
				{
					continue;
				}
			}
		    if( $row['gid'] == \INS\Core::$settings['theme']['tid'] )
			{
				$defaulttext = \INS\Language::i()->strings['admin_modules_templates_defaultwebsitetheme'];
			    eval("\$default = \"".\INS\Template::i()->getAcp("templates_isdefault")."\";");
			}
			elseif( $row['gid'] == 1 )
			{
				$defaulttext = \INS\Language::i()->strings['admin_modules_templates_defaultadmintheme'];
				eval("\$default = \"".\INS\Template::i()->getAcp("templates_isdefault")."\";");
			}	
			else 
			{
				eval("\$default = \"".\INS\Template::i()->getAcp("templates_makedefault")."\";");
			}
			eval("\$themes_list .= \"".\INS\Template::i()->getAcp("themes_list")."\";");
		}
		
		return $themes_list;
	}
	
	/**
	 * AJAX Request Handler
	 *
	 * @return 	void
	 */
	public function _ajax()
	{
		if( \INS\Http::i()->verifyAjaxRequest() )
		{
			if( \INS\Http::i()->request_method == 'post' )	
			{	
				if( \INS\Http::i()->ajaxrequest == 'search' )
				{
					$name = \INS\Http::i()->templatename;
					$gid = intval( \INS\Http::i()->gid );
					$group = \INS\Db::i()->escapeString( \INS\Http::i()->group );

					if($group == 'admin')
					{
						$rows = \INS\Db::i()->fetchAll('`name`, `gid`, `group`', 'templates_admin', '`name` LIKE \'%'.$name.'%\'');

						if(!empty($rows))
						{
							$string .= "<table class=\"table\">";
							$string .= "<thead>";
							$string .= "<th>Name</th>";
							$string .= "<th>Group</th>";
							$string .= "<th>Actions</th>";
							$string .= "</thead>";
							
							$string .= "<tbody>";
							foreach($rows as $row)
							{
								eval("\$string .= \"".\INS\Template::i()->getAcp("themes_admin_templates")."\";");
							}
							$string .= "</tbody>";
							$string .= "</table>";

							\INS\Json::_print( \INS\Language::i()->parseOutput( $string ) );
						}
						else
						{
							\INS\Json::error( \INS\Language::i()->strings['admin_modules_templates_ajax_nothingfound'] );
						}	
					}	

					if($group == 'main')
					{
						$rows = \INS\Db::i()->fetchAll('`name`, `gid`, `group`', 'templates', '`name` LIKE ? AND `gid` = ?', '' , [ 1 => '%' . $name . '%', 2 => $gid ]);

						if(!empty($rows))
						{
							$string .= "<table class=\"table\">";
							$string .= "<thead>";
							$string .= "<th>Name</th>";
							$string .= "<th>Group</th>";
							$string .= "<th>Actions</th>";
							$string .= "</thead>";
							
							$string .= "<tbody>";
							foreach($rows as $row)
							{
								eval("\$string .= \"".\INS\Template::i()->getAcp("themes_admin_templates")."\";");
							}
							$string .= "</tbody>";
							$string .= "</table>";
							\INS\Json::_print( \INS\Language::i()->parseOutput( $string ) );
						}
						else
						{
							\INS\Json::error( \INS\Language::i()->strings['admin_modules_templates_ajax_nothingfound'] );
						}	
					}	
				}
				elseif( \INS\Http::i()->ajaxrequest == 'edit' )
				{
					$data 	= \INS\Http::i()->templatedata;
					$gid 	= intval( \INS\Http::i()->gid );
					$name 	= \INS\Db::i()->escapeString( \INS\Http::i()->name );
					$group 	= \INS\Db::i()->escapeString( \INS\Http::i()->group );
					$data 	= \stripslashes($data);
					
					\INS\Template::i()->update($name, $data, $group);
					\INS\Json::_print( \INS\Language::i()->strings['admin_globalnotification_customize_templateupdated'] );
				}
			}
		}
		else
		{
			\INS\Json::_error( 'Invalid Request.' );
			exit();
		}
	}	
}	
?>