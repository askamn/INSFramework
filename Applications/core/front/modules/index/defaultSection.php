<?php
/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.5.0
 * Front Module: Index
 * Last Updated: $Date: 2014-10-28 5:35 (Tue, 26 Oct 2014) $
 * </pre>
 * 
 * @author 		$Author: AskAmn$
 * @copyright	(c) 2014 Infusion Network Solutions
 * @license		http://www.infusionnetwork/licenses/license.php?view=main&version=2014
 * @package		IN.CMS
 * @since 		0.6.0 
 */
namespace INS\core\front\modules\index;

class index_defaultSection
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
		$this->title = \INS\Core::$settings['site']['name'];
		$this->link = \INS\Http::i()->link();

		\INS\Template::i()->pushToCache( "'header', 'footer', 'sidebar', 'index', 'headerinclude'", \INS\Core::$settings['theme']['tid']);
		\INS\Template::i()->addToNavigation( \INS\Language::i()->strings['admin_modules_system_settings_navtitle'], $this->link );
	}

	/**
	 * Main Entry Point
	 *
	 * @return 		void
	 */
	public function Execute()
	{
		eval("\$this->html = \"".\INS\Template::i()->get("index")."\";");
		\INS\Template::i()->output( $this->html, $this->title );
	}
}
?>