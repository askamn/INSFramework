<?php
/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.5.0
 * Event Manager For Dynamic Radio
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

class events_manager Extends \INS\Admin
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
		$this->title = \INS\Language::i()->strings['admin_dynamicradio_events_overview_title'];
		$this->link = \INS\Core::$settings['site']['url'] . '/' . \INS\Core::$config['admin']['dir'] . '/index.php?app=' . INS_APP . '&module=' . INS_MODULE . '&section=' . INS_SECTION;
		\INS\Template::i()->addToNavigation( \INS\Language::i()->strings['admin_dynamicradio_events_navtitle'], $this->link );
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
				\INS\Template::i()->addToNavigation( \INS\Language::i()->strings['admin_dynamicradio_events_create_navtitle'], $this->link . '&request=' . \INS\Http::i()->request );
				$this->_create();
				break;
			case 'edit':
				\INS\Template::i()->addToNavigation( \INS\Language::i()->strings['admin_dynamicradio_events_edit_navtitle'], $this->link . '&request=' . \INS\Http::i()->request );
				$this->_edit();
				break;
			case 'delete':
				$this->_delete();
				break;
			default: 	
				$this->_default();
				break;
		}
    } 
	
	/**
	 * Overview
	 *
	 * @return 		void
	 */
	public function _default()
	{
		$rows = \INS\Db::i()->fetchAll('*', 'drevents');
		
		if( empty( $rows ) OR !is_array( $rows ) )
		{
			$eventlist = \INS\Language::i()->strings['admin_dynamicradio_events_noeventtodisplay'];
		}
		else
		{
			foreach($rows as $row)
			{
				if($row['date'] < date('Y-m-d'))
				{
					$status = 'Expired';
				}
				else
				{
					$status = 'Open';
				}
				eval("\$eventlist .= \"".\INS\Template::i()->getAcp("dr_events_eventlist")."\";");
			}
		}
			
		eval("\$this->html = \"".\INS\Template::i()->getAcp("dr_events_overview")."\";");
		\INS\Template::i()->output( $this->html, $this->title );
	}

	/**
	 * @brief 	Create request
	 */
	public function _create()
	{
		if( isset(\INS\Http::i()->submit) && \INS\Http::i()->request_method == 'post' )
		{
			$fields = array('name', 'description', 'date', 'time', 'location', 'venue');
			$i = 1;
			foreach( $fields As $field )
			{
				if( property_exists( \INS\Http::i(), $field) )
				{
					$array[ $field ] = '?';
					$binds[  $i++ ] = \INS\Http::i()->$field;
				} 
			}

			$crash = FALSE;
			\INS\File::i()->uploadFormField = 'file';
			\INS\File::i()->maxFileSize = 1024*500;
			\INS\File::i()->useRandomName = TRUE;
			\INS\File::i()->parse_dangerous_scripts = 1;
			\INS\File::i()->allowedExtensions = array('gif', 'jpg', 'jpeg', 'png');
			\INS\File::i()->uploadFileLocation = 'events'; 
			\INS\File::i()->Execute();
			
			if( \INS\File::i()->errorString != '' )
			{
				$crash = TRUE;
			}
			
			if( $crash === TRUE )
			{
				\INS\Template::i()->globalNotification = \INS\File::i()->errorString;
			}		
			else	
			{
				if( \INS\File::i()->uploadBox != FALSE )
				{
					$array['image'] = '?';
					$binds[ $i++ ] = \INS\File::i()->parsedFileName;
				}	

				\INS\Db::i()->insert('drevents', $array, $binds);
				\INS\Template::i()->globalNotification = \INS\Language::i()->strings['admin_dynamicradio_events_eventcreatedsuccessfully'];
			}	
		}

		eval("\$this->html = \"".\INS\Template::i()->getAcp("dr_events_create")."\";");
		\INS\Template::i()->output( $this->html, \INS\Language::i()->strings['admin_dynamicradio_events_create_title'] );
	}		

	/**
	 * Edit Event
	 *
	 * @return 		void
	 */
	public function _edit()
	{
		if(isset(\INS\Http::i()->submit) && \INS\Http::i()->request_method == 'post')
		{
			$fields = array('name', 'description', 'date', 'time', 'location', 'venue');

			$i = 1;
			foreach($fields As $field)
			{
				if( property_exists( \INS\Http::i(), $field) )
				{
					$array[ $field ] = '?';
					$binds[  $i++ ] = \INS\Http::i()->$field;
				} 
			}

			$crash = false;
			\INS\File::i()->uploadFormField = 'file';
			\INS\File::i()->maxFileSize = 1024*500;
			\INS\File::i()->useRandomName = TRUE;
			\INS\File::i()->parse_dangerous_scripts = 1;
			\INS\File::i()->allowedExtensions = array('gif', 'jpg', 'jpeg', 'png');
			\INS\File::i()->uploadFileLocation = 'events'; 
			\INS\File::i()->Execute();
			
			if( \INS\File::i()->errorString != '' )
			{
				$crash = TRUE;
			}
			
			if( $crash === TRUE )
			{
				\INS\Template::i()->globalNotification = \INS\File::i()->errorString;
			}		
			else	
			{
				if( \INS\File::i()->uploadBox != FALSE )
				{
					$array['image'] = '?';
					$binds[ $i++ ] = \INS\File::i()->parsedFileName;
					$image = \INS\Db::i()->f( 'image', 'drevents', '`eid` = ?', '', [ 1 => intval( \INS\Http::i()->eid ) ] )->get( 'image' );
					/* Remove old image */
					unlink( INS_UPLOADS_DIR . DIRECTORY_SEPARATOR . 'events' . DIRECTORY_SEPARATOR . $image );
				}
			}	

			$binds[ $i ] = \INS\Http::i()->eid;
			\INS\Db::i()->update( 'drevents', $array, 'eid = ?', '', $binds );

			if( $crash !== TRUE )
			{
				\INS\Template::i()->globalNotification = \INS\Language::i()->strings['admin_dynamicradio_events_eventupdatedsuccessfully'];
			}	
		}
		
		$row = \INS\Db::i()->fetch( '*', 'drevents', '`eid` = ?', '', [ 1 => intval( \INS\Http::i()->eid ) ] );
		eval("\$this->html = \"".\INS\Template::i()->getAcp("dr_events_edit")."\";");
		\INS\Template::i()->output( $this->html, \INS\Language::i()->strings['admin_dynamicradio_events_edit_title'] );
	}
	
	/**
	 * @brief Delete Request
	 */
	public function _delete()
	{
		$image = \INS\Db::i()->f( 'image', 'drevents', '`eid` = ?', '', [ 1 => intval( \INS\Http::i()->eid ) ] )->get( 'image' );
		/* Remove old image */
		unlink( INS_UPLOADS_DIR . DIRECTORY_SEPARATOR . 'events' . DIRECTORY_SEPARATOR . $image );
		\INS\Db::i()->delete( 'drevents', "`eid` = ?", [ 1 => intval( \INS\Http::i()->eid ) ] );
		\INS\Http::i()->redirect( $this->link );
	}		
}	