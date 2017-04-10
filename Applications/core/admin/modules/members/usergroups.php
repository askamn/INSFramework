<?php
/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.5.0
 * Usergroups
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

class members_usergroups Extends \INS\Admin
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
		$this->title = \INS\Language::i()->strings['admin_members_usergroups_title'];
		$this->link = \INS\Http::i();
		\INS\Template::i()->addToNavigation( \INS\Language::i()->strings['admin_members_usergroups_navtitle'], $this->link );
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
			case 'delete':
				$this->_delete();
				break;
			case 'edit':
				$this->_edit();
				break;
			case 'list':
				\INS\Template::i()->addToNavigation( \INS\Language::i()->strings['admin_members_usergroups_list_navtitle'], $this->link . '&request=list' );
				$this->_edit();
				break;
			default: 	
				$this->_default();
				break;
		}
    } 
	
	/**
	 * @brief 	Default request
	 */
	public function _default()
	{	
		if( isset( \INS\Http::i()->show ) )
		{
			switch ( \INS\Http::i()->show )
			{
				case 'groupDeleted':
					\INS\Template::i()->globalNotification =  \INS\Language::i()->strings['admin_members_usergroups_groupdeleted'];
					break;
			}
		}

		$usergroups_build_list = $this->builduserGroupsList();
		eval("\$this->html = \"".\INS\Template::i()->getAcp("usergroups_overview")."\";");   
		\INS\Template::i()->output( $this->html, $this->title );
	}

	/**
	 * @brief 	Delete request
	 */
	public function _delete()
	{
		\INS\Db::i()->delete("usergroups", "`gid` = ?", [ 1 => intval( \INS\Http::i()->gid ) ] );
		\INS\Http::i()->redirect( $this->link . '&show=groupDeleted' ); 
	}	
	
	/**
	 * @brief 	Edit request
	 */
	public function _edit()
	{
		\INS\Http::i()->gid = intval(\INS\Http::i()->gid);
		
		if($this->ins->request_method == "post" && isset(\INS\Http::i()->submit))
		{
			$array = array(
				'name' => \INS\Db::i()->escape_string(\INS\Http::i()->name),
				'description' => \INS\Db::i()->escape_string(\INS\Http::i()->description)
			);
			\INS\Db::i()->update("usergroups", $array, "`gid`='{\INS\Http::i()->gid}'");
			\INS\Template::i()->globalNotification = \INS\Language::i()->strings['admin_usergroup_updated'];	
		}
		
		$row = \INS\Db::i()->fetch( '*', 'usergroups', "`gid` = ?", '', [ 1 => \INS\Http::i()->gid ] );
		
		/* Navigation */
		\INS\Template::i()->addToNavigation( \INS\Language::i()->strings( 'admin_members_usergroups_edit_navtitle' )->parse( $row['name'], $row['gid'] ), \INS\Http::i()->url()->append( [ 'request' => 'edit', 'gid' => $row['gid'] ] ) );

		\INS\Language::i()->strings['admin_usergroups_editing'] = \INS\Language::i()->parse( \INS\Language::i()->strings['admin_usergroups_editing'], $row['name'] );
		
		eval("\$this->html = \"".\INS\Template::i()->getAcp("usergroups_edit")."\";"); 
		\INS\Template::i()->output( $this->html, \INS\Language::i()->strings['admin_members_usergroups_edit_title'] );
	}	
	
	/**
	 * Builds current list of usergroups
	 *
	 * @return		string
	 */
	public function builduserGroupsList() 
	{
		$page = !isset(\INS\Http::i()->page) ? 1 : intval(\INS\Http::i()->page);
	   
		$limit = $page*30;
		$_limit = $limit-30;
		
		$rows = \INS\Db::i()->fetchAll( '*', 'usergroups', '', "{$_limit}, {$limit}");
	   
		foreach($rows AS $row)
		{
			eval("\$usergroup_list .= \"".\INS\Template::i()->getAcp("usergroups_build_list")."\";");
		}	

		return $usergroup_list;
	}
	
	/**
	 * Builds current list of usergroups
	 *
	 * @return		string
	 */
	public function buildUsersUnderUserGroup() 
	{
		$gid = intval(\INS\Http::i()->gid);
		$page = !isset(\INS\Http::i()->page) ? 1 : intval(\INS\Http::i()->page);
	   
		$limit = $page*30;
		$_limit = $limit-30;

		$rows = \INS\Db::i()->fetchAll("*",'users', "`group` = ?", "{$_limit}, {$limit}", [ 1 => $gid ] );

	    if( empty( $rows ) OR !is_array( $rows ) ) 
		{	
			\INS\Template::i()->globalNotification = \INS\Language::i()->strings['admin_usergroups_groupempty'];
		}

		foreach($rows AS $row)
		{
			if( $row['activation_state'] == '0' )
				eval("\$activate = \"".\INS\Template::i()->getAcp("members_list_activateuser")."\";");
			else
				$activate = '';

			if($row['uid'] != 1)
			{
				/* User is banned */
				if($row['group'] == 1)
					eval("\$userban = \"".\INS\Template::i()->getAcp("members_list_banneduser")."\";");
				else
					eval("\$userban = \"".\INS\Template::i()->getAcp("members_list_banuser")."\";");
			}
			else
			{
				eval("\$userban = \"".\INS\Template::i()->getAcp("members_list_usercannotbebanned")."\";");
			}	

			eval("\$user_list .= \"".\INS\Template::i()->getAcp("users_build_list")."\";");
		}	

		return $user_list;
	}
}
?>