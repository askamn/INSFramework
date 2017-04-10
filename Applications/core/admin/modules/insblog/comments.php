<?php
/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.5.0
 * IN.Blog Comments
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

class insblog_comments Extends \INS\Admin
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
		$this->title = \INS\Language::i()->strings['admin_core_insblog_title'] . ' ' . \INS\Language::i()->strings['admin_core_insblog_comments_title'];
		$this->link = \INS\Core::$settings['site']['url'] . '/' . \INS\Core::$config['admin']['dir'] . '/index.php?app=' . INS_APP . '&module=' . INS_MODULE . '&section=' . INS_SECTION;
		\INS\Template::i()->addToNavigation( \INS\Language::i()->strings['admin_core_insblog_comments_navtitle'], $this->link );
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
			case 'edit':
				\INS\Template::i()->addToNavigation( \INS\Language::i()->strings['admin_core_insblog_comments_edit_navtitle'], $this->link );
				$this->_edit();
				break;
			default: 	
				$this->_default();
				break;
		}
    } 

    /**
	 * Comments Page
	 *
	 * @return 		void
	 */
	public function _default()
	{
		$page = !isset( \INS\Http::i()->page ) ? 1 : intval(\INS\Http::i()->page);
		
		$limit = $page*\INS\Core::$settings['admin']['resultsperpage'];
		$_limit = $limit-\INS\Core::$settings['admin']['resultsperpage'];
		
		$rows = \INS\Db::i()->fetchAll('*', 'blogcomments', '1=1 ORDER BY `time`', "{$_limit}, {$limit}");
		
		if(!empty($rows))
		{
			foreach($rows as $row)
			{
				$comment = $row['comment'];
				/* Shorten the comment */
				if(strlen($comment) > 50)
					$comment = substr( $comment, 0, 50 ) . '...';
				$comment = htmlentities($comment);
				eval("\$commentlist .= \"".\INS\Template::i()->getAcp("blog_overview_comment")."\";");
			}
		}
		else
		{
			$commentlist = \INS\Language::i()->strings['insblog_no_comments_to_display'];
		}	
		
		if(isset(\INS\Http::i()->do) && \INS\Http::i()->do = 'delete')
		{
			if( isset(\INS\Http::i()->cid) )
			{
				$cid = intval(\INS\Http::i()->cid);
				$pid = intval(\INS\Http::i()->pid);
				
				\INS\Db::i()->delete( 'blogcomments', '`cid` = ?', [ 1 => intval(\INS\Http::i()->cid) ] );
				\INS\Db::i()->update( 'blogposts', [ 'comments' => 'comments - 1' ], 'pid = ?', '', [ 1 => intval(\INS\Http::i()->pid) ] );
			}

			\INS\Template::i()->globalNotification = \INS\Language::i()->strings['insblog_admin_postdeletedsuccess'];
		}
		
		$num = \INS\Db::i()->rowCount('blogposts');
		$pagination = \INS\Template::i()->buildPagination($page, \INS\Core::$settings['admin']['resultsperpage'], $num, 'index.php?app=insblog&module=blog&section=overview');

		eval("\$this->html = \"".\INS\Template::i()->getAcp("blog_comments_overview")."\";");
		\INS\Template::i()->output( $this->html, $this->title );
	}	

	/**
	 * @brief 	Edit Request
	 */
	public function _edit()
	{
		$cid = intval( \INS\Http::i()->cid );
		
		if(isset(\INS\Http::i()->submit) && $this->ins->request_method == 'post')
		{	
			\INS\Db::i()->update('blogcomments', [ 'comment' => '?' ], '`cid` = ?', '', [ 1 => \INS\Http::i()->comment, 2 => intval( \INS\Http::i()->cid )  ] );
			\INS\Template::i()->globalNotification = \INS\Language::i()->strings['insblog_admin_commentteditedsuccess'];
		}

		$row = \INS\Db::i()->fetch( '*', 'blogcomments', '`cid` = ?', '', [ 1 => $cid ] );
		eval("\$this->html = \"".\INS\Template::i()->getAcp("blog_edit_comment")."\";");
		\INS\Template::i()->output( $this->html, \INS\Language::i()->strings['admin_core_insblog_comments_editingcomment'] );
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