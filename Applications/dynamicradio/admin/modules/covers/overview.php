<?php
/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.5.0
 * Cover System For Dynamic Radio
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

class covers_overview Extends \INS\Admin
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
		$this->title = \INS\Language::i()->strings['admin_dynamicradio_covers_title'];
		$this->link = \INS\Core::$settings['site']['url'] . '/' . \INS\Core::$config['admin']['dir'] . '/index.php?app=' . INS_APP . '&module=' . INS_MODULE . '&section=' . INS_SECTION;
		\INS\Template::i()->addToNavigation( \INS\Language::i()->strings['admin_dynamicradio_covers_navtitle'], $this->link );
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
			default: 	
				$this->_default();
		}
    } 
	
	/**
	 * Overview
	 *
	 * @return 		void
	 */
	public function _default()
	{
		if( isset( \INS\Http::i()->do ) AND isset( \INS\Http::i()->cid ) )
		{
			$do = \INS\Http::i()->do;
			if( $do == "approve" )
			{
				\INS\Db::i()->update( 'drcovers', array('status' => '1'), "`cid` = ?", '', [ 1 => \INS\Http::i()->cid ] );
			}
			elseif( $do == 'deny' )
			{
				\INS\Db::i()->update('drcovers', array('status' => '2'), "`cid` = ?", '', [ 1 => \INS\Http::i()->cid ]);
			}
			
			\INS\Template::i()->globalNotification = \INS\Language::i()->strings['admin_dynamicradio_covers_requestmodifed'];
		}

		$page = ( !isset( \INS\Http::i()->page ) ) ? 1 : intval(\INS\Http::i()->page);
	   
		$limit = $page * \INS\Core::$settings['admin']['resultsperpage'];
		$_limit = $limit - \INS\Core::$settings['admin']['resultsperpage'];
		
		$num = \INS\Db::i()->rowCount('drcovers');
		$pagination = \INS\Template::i()->buildPagination( \INS\Http::i()->page, \INS\Core::$settings['admin']['resultsperpage'], $num, $this->link );
		
		$rows = \INS\Db::i()->fetchAll('*', 'drcovers', '1=1', "{$_limit}, {$limit}");
		
		if( empty( $rows ) OR !is_array( $rows ) )
		{
			$coverslist = \INS\Language::i()->strings['admin_dynamicradio_covers_nocoverrequests'];
		}
		else
		{
			foreach($rows as $row)
			{
				$time = $row['start'] . ' - ' . $row['finish'];
				$daydate = $row['date'] . ' (' . $row['day'] . ')';
				$username = \INS\Users::i()->getUsernameByUID($row['uid']);
				
				switch( intval( $row['status'] ) )
				{
					case 0: 	
						$status = '- N/A -';
						$options = "<span class=\"btn-group\">
										<a href=\"{$this->link}&do=approve&amp;cid={$row['cid']}\" class=\"btn t\" title=\"Approve\"><i class=\"fa fa-check\"></i></a>
										<a href=\"{$this->link}&do=deny&amp;cid={$row['cid']}\" class=\"btn t\" title=\"Deny\"><i class=\"fa fa-times\"></i></a>";
								   "</span>";	
					
					break;
					case 1: 	
						$status = 'Approved';
						$options = "<a href=\"{$this->link}&do=deny&amp;cid={$row['cid']}\" class=\"btn t\" title=\"Deny\"><i class=\"fa fa-times\"></i></a>";
									
					break;
					case 2: 	
						$status = 'Denied';
						$options = "<a href=\"{$this->link}&do=approve&amp;cid={$row['cid']}\" class=\"btn t\" title=\"Approve\"><i class=\"fa fa-check\"></i></a>";

					break;
				}

				eval("\$coverslist .= \"".\INS\Template::i()->getAcp("dr_covers_list")."\";");
			}
		}

		eval("\$this->html = \"".\INS\Template::i()->getAcp("dr_covers_overview")."\";");
		\INS\Template::i()->output( $this->html, $this->title );
	}	
}	