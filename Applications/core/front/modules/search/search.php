<?php
/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.5.0
 * Search Module: Search
 * Last Updated: $Date: 2014-12-27 4:10:16 (Sun, 27 Dec 2014) $
 * </pre>
 * 
 * @author 		$Author: AskAmn$
 * @copyright	(c) 2014 Infusion Network Solutions
 * @license		http://www.infusionnetwork/licenses/license.php?view=main&version=2014
 * @package		IN.CMS
 * @since 		0.5.2; 22 August 2014
 */

class search_search
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
		$this->link = \INS\Core::$settings['site']['url'] . '/' . \INS\Core::$config['admin']['dir'] . '/index.php?app=' . INS_APP . '&module=' . INS_MODULE . '&section=' . INS_SECTION;
	}

	/**
	 * Main Entry Point
	 *
	 * @return 		void
	 * @access  	public
	 */
	public function Execute()
	{
		switch( \INS\Http::i()->request )
		{
			case 'ajax':	$this->_Ajax();
			break;
			default: $this->_Main();
		}
	}

	/**
	 * Ajax Requests
	 *
	 * @return 		void
	 * @access  	public
	 */
	public function _Ajax()
	{
		if( \INS\Http::i()->verifyAjaxRequest() )
		{

			$locs = empty( \INS\Http::i()->locations ) ? '' : explode( '|', \INS\Http::i()->locations );

			if( in_array( 'members', $locs ) OR empty($locs) )
			{	
				$escapedTerm = '%' . str_replace( array('+', '%', '_'), array('++', '+%', '+_'), \INS\Http::i()->term ) . '%';
				$data = \INS\Db::i()->fetchAll( 'avatar, username, uid', 'users', ' `username` LIKE ? ESCAPE \'+\'', '',[ 1 => $escapedTerm ] );
				$_data['data'] = $data;
				$_data['statuscode'] = 1;
				$_data['type'] = 'members';
				if( !empty( $_data['data'] ) )
					\INS\Json::echoArrayAsJson( $_data );
				else
					\INS\Json::_error( \INS\Language::i()->strings['search_ajax_nothingfound'] );
			}
		}
		else
		{
			die( "Invalid Request." );
		}
	}
}
?>