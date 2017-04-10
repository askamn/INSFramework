<?php
/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.5.0
 * IN.Blog Posts
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

class insblog_posts Extends \INS\Admin
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
		$this->title = \INS\Language::i()->strings['admin_core_insblog_title'] . ' ' . \INS\Language::i()->strings['admin_core_insblog_posts_title'];
		$this->link = \INS\Core::$settings['site']['url'] . '/' . \INS\Core::$config['admin']['dir'] . '/index.php?app=' . INS_APP . '&module=' . INS_MODULE . '&section=' . INS_SECTION;
		\INS\Template::i()->addToNavigation( \INS\Language::i()->strings['admin_core_insblog_posts_navtitle'], $this->link );
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
				\INS\Template::i()->addToNavigation( \INS\Language::i()->strings['admin_core_insblog_posts_create_navtitle'], $this->link );
				$this->_create();
				break;
			case 'edit':
				\INS\Template::i()->addToNavigation( \INS\Language::i()->strings['admin_core_insblog_posts_edit_navtitle'], $this->link );
				$this->_edit();
				break;
			case 'close':
				$this->_close();
				break;
			case 'open':
				$this->_open();
				break;
			default: 	
				$this->_create();
				break;
		}
    } 

    /**
	 * Main page
	 *
	 * @return 		void
	 */
	public function _create()
	{
		if( isset(\INS\Http::i()->submit) && \INS\Http::i()->request_method == 'post' )
		{	
			$array = array(
				'subject' 	=> '?',
				'post' 		=> '?',
				'date' 		=> DATE('Y-m-d'),
				'time' 		=> time(),
				'author' 	=> \INS\Users::i()->userData['username'],
				'comments' 	=> '0'
			);

			$binds = [ 1 => \INS\Http::i()->subject, 2 => \INS\Http::i()->post ];
			\INS\Db::i()->insert( 'blogposts', $array, $binds );
		}

		eval("\$this->html = \"".\INS\Template::i()->getAcp("blog_create_post")."\";");
		\INS\Template::i()->output( $this->html, \INS\Language::i()->strings['admin_core_insblog_posts_create_title'] );
	}	

	/**
	 * Main page
	 *
	 * @return 		void
	 */
	public function _edit()
	{
		$pid = intval(\INS\Http::i()->pid);
		
		if(isset(\INS\Http::i()->submit) && \INS\Http::i()->request_method == 'post')
		{	
			\INS\Db::i()->update( 'blogposts', [ 'subject' => '?', 'post' => '?' ], '`pid` = ?', '', [ 1 => \INS\Http::i()->subject, 2 => \INS\Http::i()->post, 3 => $pid ] );
			\INS\Template::i()->globalNotification = \INS\Language::i()->strings['insblog_admin_posteditedsuccess'];
		}

		$row = \INS\Db::i()->fetch( '*', 'blogposts', '`pid` = ?', '', [ 1 => $pid ] );
		$subject = (strlen($row['subject']) > 80) ? substr( $row['subject'], 0, 80 ) . "..." : $row['subject'];

		\INS\Language::i()->strings['insblog_editing_post'] = \INS\Language::i()->parse( \INS\Language::i()->strings['insblog_editing_post'], $subject);
		eval("\$this->html = \"".\INS\Template::i()->getAcp("blog_edit_post")."\";");
		\INS\Template::i()->output( $this->html, \INS\Language::i()->strings['admin_core_insblog_posts_edit_title'] );
	}	
	
	/**
	 * @brief Delete Request
	 */
	public function _delete()
	{
		\INS\Db::i()->delete( 'blogposts', '`pid`= ?', [ 1 => \INS\Http::i()->pid ] );
		\INS\Db::i()->delete( 'blogcomments', '`pid`= ?', [ 1 => \INS\Http::i()->pid ] );
		\INS\Http::i()->redirect( $this->link . '&do=showdeletesuccess' );
	}	

	/**
	 * @brief Close Request
	 */
	public function _close()
	{
		\INS\Db::i()->update( 'blogposts', array('closed' => '1'), "`pid` = ?", '', [ 1 => intval(\INS\Http::i()->pid) ] );
		\INS\Http::i()->redirect( $this->link . '&do=showclosesuccess' );
	}		
	
	/**
	 * @brief Open Request
	 */
	public function _open()
	{
		\INS\Db::i()->update('blogposts', array('closed' => '2'), "`pid` = ?", '', [ 1 => intval(\INS\Http::i()->pid) ]);
		\INS\Http::i()->redirect( $this->link . '&do=showopensuccess');
	}	
}	