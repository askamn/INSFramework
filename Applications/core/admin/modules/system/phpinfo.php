<?php
/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.5.0
 * PHPInfo
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

class system_phpinfo Extends \INS\Admin
{
	/**
	 * The complete link to this module
	 *
	 * @var		string	
	 */
	public $link;
	
	/**
	 * Constructor
	 *s
	 * @return 		void
	 */
	public function __construct()
	{
		$this->title = \INS\Language::i()->strings['admin_modules_system_phpinfo_title'];
		$this->link = \INS\Http::i();
		\INS\Template::i()->addToNavigation( \INS\Language::i()->strings['admin_modules_system_phpinfo_title'], $this->link );
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
			case 'phpinfo':
				$this->_phpinfo();
			default:
				$this->_default();
				break;
		}
    } 
	
	/**
	 * Selects which page to display
	 *
	 * @return		void
	 */
	public function _default()
	{	
		eval("\$this->html = \"" . \INS\Template::i()->getAcp("server_phpinfo") . "\";");
		\INS\Template::i()->output( $this->html, $this->title );
    } 
	
	/**
	 * Displays PHP Info
	 *
	 * @return		void
	 */
	public function _phpinfo()
	{
		die( phpinfo( ) );
	}	
}
?>