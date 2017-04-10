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

class system_sqltoolbox Extends \INS\Admin
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
		$this->title = \INS\Language::i()->strings['admin_modules_system_sqltoolbox_title'];
		$this->link = \INS\Http::i();
		\INS\Template::i()->addToNavigation( \INS\Language::i()->strings['admin_modules_system_sqltoolbox_navtitle'], $this->link );
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
	 * SQL Tool Box
	 *
	 * @return 	void
	 */
	public function _default()
	{
		eval("\$this->html = \"".\INS\Template::i()->getAcp("sqltoolbox")."\";");	
		\INS\Template::i()->output( $this->html, $this->title );
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
				$query = \INS\Http::i()->query;

				/* Undo \INS\Json.Stringify */
				$query = str_replace('"', '', $query);
				
				$result = \INS\Db::i()->acpQuery($query);
				if( is_array($result) && !empty($result) )
				{
					if(array_key_exists('error_statement', $result))
					{
						\INS\Json::error($result['error_statement']);
						return;
					}	
					\INS\Json::_print($result);
				}
				
				\INS\Json::_print($result);
			}
		}
		else
		{
			die( 'Invalid Request.' );
		}
	}	
}
?>