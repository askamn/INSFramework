<?php
/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.5.0
 * CSS
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

class members_create Extends \INS\Admin
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
		$this->title = \INS\Language::i()->strings['admin_modules_members_create_title'];
		$this->link = \INS\Http::i();
		\INS\Template::i()->addToNavigation( \INS\Language::i()->strings['admin_modules_members_create_navtitle'], $this->link );
	}

	/** 
	 * Main Entry Point
	 *
	 * @return 		void
	 * @access 		public
	 */
	public function Execute() 
	{
		switch( \INS\Http::i()->request )
		{
			default: 
				$this->_default();
				break;
		}
	}
	
	/**
	 * Create user page
	 *
	 * @return 		void
	 */
	public function _default()
	{
		$usergroups = $this->builduserGroupsList();
		if( \INS\Http::i()->request_method == 'post' AND \INS\Http::i()->submit )
		{
			\INS\Users::i()->data['username'] 			= \INS\Http::i()->username;
			\INS\Users::i()->data['password'] 			= \INS\Core::encode( \INS\Http::i()->password );
			\INS\Users::i()->data['email'] 	  			= \INS\Http::i()->email;
			\INS\Users::i()->data['activation_state'] 	= \INS\Http::i()->activation_state;
			\INS\Users::i()->data['userrole'] 			= \INS\Http::i()->userrole;

			if(
				\INS\Db::i()->hasDuplicates( \INS\Users::i()->data['username'], "username") == FALSE AND 
				\INS\Db::i()->hasDuplicates( \INS\Users::i()->data['email'], "email") == FALSE
			)
			{
				\INS\Template::i()->globalNotification = \INS\Language::i()->strings['admin_members_usercreated'];
				\INS\Users::i()->register();
			} 
			else
			{
				\INS\Template::i()->globalNotification = \INS\Language::i()->strings['admin_members_usernotcreated'];
			}
		}
		
		eval("\$this->html = \"".\INS\Template::i()->getacp("users_create")."\";");
		\INS\Template::i()->output( $this->html, $this->title );
	}

	/**
	 * Builds current list of usergroups
	 *
	 * @return		string
	 */
	public function builduserGroupsList() 
	{	
		$rows = \INS\Db::i()->fetchAll( '*', 'usergroups' );
	   	
	   	$list = '<select name="userrole">';
		foreach( $rows AS $row )
		{
			$checked = ( \INS\Core::$settings['users']['default_registered_users_group'] == $row['gid'] ) ? ' selected="selected"': '';
			$list .= sprintf( '<option value="%d"%s>%s</option>', $row['gid'], $checked, $row['name'] );
		}	
		$list .= '</select>';

		return $list;
	}
}	
?>