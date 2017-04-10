<?php
/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.5.0
 * Dashboard Class
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

class overview_dashboard 
{
	/**
	 * Output holder
	 *
	 * @var 		string
	 * @access 		protected
	 */
	protected $html;
	
    /**
     * Constructor
     *
     * @return 		void
     */
    public function __construct()
    {
    	$this->title = \INS\Language::i()->strings['admin_modules_dashboard_title'];
    }

	/** 
	 * Main Entry Point
	 *
	 * @return 		void
	 * @access 		public
	 */
	public function Execute() 
	{
		switch ( \INS\Http::i()->request )
		{
			case 'ajaxrequesthandler': $this->_ajax();
			break;
			default: $this->_default();
			break;
		}
	}

	/** 
	 * @brief 	The default request
	 */
	protected function _default()
	{
		$points = $this->loadStats();	
		$row = \INS\Db::i()->fetch( '`adminmessage`, `visitstoday`', 'misc' );
		$index_visitstoday = $row['visitstoday'];
		$index_adminmessage = $row['adminmessage'];

		eval( "\$this->html = \"" . \INS\Template::i()->getAcp( "index" ) . "\";" );
		\INS\Template::i()->output( $this->html, $this->title );
	}

	/** 
	 * @brief 	Ajax request
	 */
	protected function _ajax()
	{
		if( $this->ins->request_method == 'post' )	
		{	
			$message = \INS\Db::i()->escape_string( \INS\Http::i()->message );
			$this->Db->update( 'misc', array( "adminmessage" => '?' ), '', '', array( 1=> $message ) );		
			JSON::_print( $message );
		}
	}

	/** 
	 * Loads Stats
	 *
	 * @return 		string 		The collection of points to plot on graph
	 * @access 		public
	 */
	protected function loadStats()
	{
		$date = date( 'm' );
		$rows = \INS\Db::i()->fetchALL( '*', 'stats', "`datem` < {$date} LIMIT 0,8" );
		$rows = array_reverse( $rows );
		if( !empty( $rows ) )
		{
			$comma = '';
			foreach( $rows as $row )
			{
				switch( $row['datem'] )
				{
					case '01': $month .= $comma.'"Jan"';
					break;
					case '02': $month .= $comma.'"Feb"';
					break;
					case '03': $month .= $comma.'"Mar"';
					break;
					case '04': $month .= $comma.'"Apr"';
					break;
					case '05': $month .= $comma.'"May"';
					break;
					case '06': $month .= $comma.'"Jun"';
					break;
					case '07': $month .= $comma.'"Jul"';
					break;
					case '08': $month .= $comma.'"Aug"';
					break;
					case '09': $month .= $comma.'"Sep"';
					break;
					case '10': $month .= $comma.'"Oct"';
					break;
					case '11': $month .= $comma.'"Nov"';
					break;
					case '12': $month .= $comma.'"Dec"';
					break;
				}
				$points .= $comma.$row['users'];
				$comma = ', ';
			}
		}	
		return $points;
	}
}
?>