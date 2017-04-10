<?php
/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.5.0
 * Event Manager For Dynamic Radio
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

class insblog_blog Extends \INS\Admin
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
		\INS\Language::i()->load( 'insblog' );
		$this->title = \INS\Language::i()->strings['admin_core_insblog_title'];
		$this->link = \INS\Core::$settings['site']['url'] . '/' . \INS\Core::$config['admin']['dir'] . '/index.php?app=' . INS_APP . '&module=' . INS_MODULE . '&section=' . INS_SECTION;
		\INS\Template::i()->addToNavigation( \INS\Language::i()->strings['admin_core_insblog_navtitle'], $this->link );
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
			case 'ajax':
				$this->_ajax();
				break;
			default: 	
				$this->_default();
				break;
		}
    } 
	
	/**
	 * Main page
	 *
	 * @return 		void
	 */
	public function _default()
	{	
		$page = !isset( \INS\Http::i()->page ) ? 1 : intval(\INS\Http::i()->page);
		
		$limit = $page*\INS\Core::$settings['admin']['resultsperpage'];
		$_limit = $limit-\INS\Core::$settings['admin']['resultsperpage'];
		
		$rows = \INS\Db::i()->fetchAll('*', 'blogposts', '', "{$_limit}, {$limit}");
		
		if(!empty($rows))
		{
			foreach($rows as $row)
			{
				if($row['closed'] == 2) /* Not closed */
					$openclose = "<a href=\"". \INS\Core::$settings['site']['url'] . '/' . \INS\Core::$config['admin']['dir'] . '/index.php?app=core&module=insblog&section=posts&request=close&pid=' . $row['pid'] . "\" class=\"btn ins-btn t\" title=\"" . \INS\Language::i()->strings['admin_blog_closethis'] . "\"><i class=\"fa fa-lock\"></i></a>";
				else
					$openclose = "<a href=\"". \INS\Core::$settings['site']['url'] . '/' . \INS\Core::$config['admin']['dir'] . '/index.php?app=core&module=insblog&section=posts&request=open&pid=' . $row['pid'] . "\" class=\"btn ins-btn t\" title=\"" . \INS\Language::i()->strings['admin_blog_openthis'] . "\"><i class=\"fa fa-unlock\"></i></a>";
				
				eval("\$postlist .= \"".\INS\Template::i()->getAcp("blog_overview_post")."\";");	
			}
		}
		else
		{
			$postlist = \INS\Language::i()->strings['insblog_no_posts_to_display'];
		}	
		
		if(isset(\INS\Http::i()->do))
		{
			if(\INS\Http::i()->do == 'showdeletesuccess')
			{
				\INS\Template::i()->globalNotification = \INS\Language::i()->strings['insblog_admin_postdeletedsuccess'];
			}
			elseif(\INS\Http::i()->do == 'showclosesuccess')	
			{
				\INS\Template::i()->globalNotification = \INS\Language::i()->strings['insblog_admin_postclosedsuccess'];
			}	
			elseif(\INS\Http::i()->do == 'showopensuccess')	
			{
				\INS\Template::i()->globalNotification = \INS\Language::i()->strings['insblog_admin_postopensuccess'];
			}				
		}
			
		$num = \INS\Db::i()->rowCount('blogposts');
		$pagination = \INS\Template::i()->buildPagination( $page, \INS\Core::$settings['admin']['resultsperpage'], $num, $this->link );
		eval("\$this->html = \"".\INS\Template::i()->getAcp("blog_overview")."\";");
		\INS\Template::i()->output( $this->html, $this->title );
	}	
	
	/**
	 * AJAX Request Handler
	 *
	 * @return 	void
	 */
	public function _ajax()
	{
		if(\INS\Http::i()->request_method == 'post')	
		{	
			if(isset(\INS\Http::i()->subject))
			{
				$name = str_replace('"', '', \INS\Http::i()->subject);
				$rows = \INS\Db::i()->fetchAll('*', 'blogposts', '`subject` LIKE \'%'.$name.'%\'');

				if(!empty($rows))
				{
					/* Begin: Table */
					$string .= '<table class="table">';
					
					/* Begin: Table Header */
					$string .= '<thead>';
					$string .= '<th>' . \INS\Language::i()->strings['insblog_admin_subject'] . '</th>';
					$string .= '<th>' . \INS\Language::i()->strings['insblog_admin_author'] . '</th>';
					$string .= '<th>' . \INS\Language::i()->strings['insblog_admin_date'] . '</th>';
					$string .= '<th>' . \INS\Language::i()->strings['insblog_admin_comments'] . '</th>';
					$string .= '<th>' . \INS\Language::i()->strings['insblog_admin_options'] . '</th>';
					$string .= '</thead>';
					/* End: Table Header */
					
					/* Begin: Table Body */
					$string .= "<tbody>";
					foreach($rows as $row)
					{
						eval("\$string .= \"".\INS\Template::i()->getAcp("blog_overview_post")."\";");
					}
					$string .= "</tbody>";
					/* End: Table Body */
					
					$string .= "</table>";
					/* End: Table */
					
					\INS\Json::printmessage($string);
				}
				else
				{
					\INS\Json::error( \INS\Language::i()->strings['insblog_admin_nopoststodisplay'] );
				}	
			}
			else if(isset(\INS\Http::i()->do))
			{
				/* Get today's posts */
				if(\INS\Http::i()->do == 'getpoststoday')
				{
					$date = date('Y-m-d');
					$rows = \INS\Db::i()->fetchAll('*', 'blogposts', "`date` = '{$date}'");

					if(!empty($rows))
					{
						/* Begin: Table */
						$string .= "<table class=\"table\">";
						
						/* Begin: Table Header */
						$string .= "<thead>";
						$string .= '<th>' . \INS\Language::i()->strings['insblog_admin_subject'] . '</th>';
						$string .= '<th>' . \INS\Language::i()->strings['insblog_admin_author'] . '</th>';
						$string .= '<th>' . \INS\Language::i()->strings['insblog_admin_date'] . '</th>';
						$string .= '<th>' . \INS\Language::i()->strings['insblog_admin_comments'] . '</th>';
						$string .= '<th>' . \INS\Language::i()->strings['insblog_admin_options'] . '</th>';
						$string .= "</thead>";
						/* End: Table Header */
						
						/* Begin: Table Body */
						$string .= "<tbody>";
						foreach($rows as $row)
						{
							eval("\$string .= \"".\INS\Template::i()->getAcp("blog_overview_post")."\";");
						}
						$string .= "</tbody>";
						/* End: Table Body */
						
						$string .= "</table>";
						/* End: Table */
						
						\INS\Json::printmessage($string);
					}
					else
					{
						\INS\Json::error(\INS\Language::i()->strings['insblog_admin_nopoststodisplay']);
					}	
				}
				
				/* Get posts by the logged in user */
				if(\INS\Http::i()->do == 'getpostsbyme')
				{
					$rows = \INS\Db::i()->fetchAll('*', 'blogposts', "`author` = ?", '', [ 1 => \INS\Users::i()->userData['username'] ]);

					if(!empty($rows))
					{
						/* Begin: Table */
						$string .= "<table class=\"table\">";
						
						/* Begin: Table Header */
						$string .= "<thead>";
						$string .= '<th>' . \INS\Language::i()->strings['insblog_admin_subject'] . '</th>';
						$string .= '<th>' . \INS\Language::i()->strings['insblog_admin_author'] . '</th>';
						$string .= '<th>' . \INS\Language::i()->strings['insblog_admin_date'] . '</th>';
						$string .= '<th>' . \INS\Language::i()->strings['insblog_admin_comments'] . '</th>';
						$string .= '<th>' . \INS\Language::i()->strings['insblog_admin_options'] . '</th>';
						$string .= "</thead>";
						/* End: Table Header */
						
						/* Begin: Table Body */
						$string .= "<tbody>";
						foreach($rows as $row)
						{
							eval("\$string .= \"".\INS\Template::i()->getAcp("blog_overview_post")."\";");
						}
						$string .= "</tbody>";
						/* End: Table Body */
						
						$string .= "</table>";
						/* End: Table */
						
						\INS\Json::printmessage($string);
					}
					else
					{
						\INS\Json::error(\INS\Language::i()->strings['insblog_admin_nopoststodisplay']);
					}	
				}
				
				/* Get todays comments */
				if(\INS\Http::i()->do == 'getcommentsstoday')
				{
					$date = date('Y-m-d');
					$rows = \INS\Db::i()->fetchAll('*', 'blogcomments', "`date` = '{$date}'");

					if(!empty($rows))
					{
						/* Begin: Table */
						$string .= "<table class=\"table\">";
						
						/* Begin: Table Header */
						$string .= "<thead>";
						$string .= '<th>' . \INS\Language::i()->strings['insblog_admin_subject'] . '</th>';
						$string .= '<th>' . \INS\Language::i()->strings['insblog_admin_author'] . '</th>';
						$string .= '<th>' . \INS\Language::i()->strings['insblog_admin_date'] . '</th>';
						$string .= '<th>' . \INS\Language::i()->strings['insblog_admin_post'] . '</th>';
						$string .= '<th>' . \INS\Language::i()->strings['insblog_admin_options'] . '</th>';
						$string .= "</thead>";
						/* End: Table Header */
						
						/* Begin: Table Body */
						$string .= "<tbody>";
						foreach($rows as $row)
						{
							$comment = $row['comment'];
							if(strlen($comment) > 50)
							{
								$comment = substr($comment, 0, 50).'...';
							}
							eval("\$string .= \"".\INS\Template::i()->getAcp("blog_overview_comment")."\";");
						}
						$string .= "</tbody>";
						/* End: Table Body */
						
						$string .= "</table>";
						/* End: Table */
						
						\INS\Json::printmessage($string);
					}
					else
					{
						\INS\Json::error(\INS\Language::i()->strings['insblog_no_comments_to_display']);
					}	
				}
				/* Delete all comments in a post */
				if(\INS\Http::i()->do == 'deleteallcommentswithpid')
				{
					$pid = intval(\INS\Http::i()->pid);
					\INS\Db::i()->delete('blogcomments', "`pid` = ?", [ 1 => intval(\INS\Http::i()->pid) ] );

					\INS\Json::printmessage(\INS\Language::i()->strings['insblog_admin_postsdeleted']);
				}				
			}	
		}
	}	
}	
?>	