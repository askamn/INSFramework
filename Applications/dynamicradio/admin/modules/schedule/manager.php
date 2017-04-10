<?php
/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.5.0
 * Schedule Manager For Dynamic Radio
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

class schedule_manager Extends \INS\Admin
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
		$this->title = \INS\Language::i()->strings['admin_dynamicradio_schedule_overview_title'];
		$this->link = \INS\Core::$settings['site']['url'] . '/' . \INS\Core::$config['admin']['dir'] . '/index.php?app=' . INS_APP . '&module=' . INS_MODULE . '&section=' . INS_SECTION;
		\INS\Template::i()->addToNavigation( \INS\Language::i()->strings['admin_dynamicradio_schedule_navtitle'], $this->link );
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
			case 'add':
				\INS\Template::i()->addToNavigation( \INS\Language::i()->strings['admin_dynamicradio_schedule_add_navtitle'], $this->link . '&request=' . \INS\Http::i()->request );
				$this->_add();
				break;
			case 'edit':
				\INS\Template::i()->addToNavigation( \INS\Language::i()->strings['admin_dynamicradio_schedule_edit_navtitle'], $this->link . '&request=' . \INS\Http::i()->request );
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
		$rows = \INS\Db::i()->fetchAll('*', 'drschedule');
		
		if( empty($rows) OR !is_array($rows) )
		{
			$schedulelist = \INS\Language::i()->strings['admin_dynamicradio_schedule_empty'];
		}
		else
		{
			foreach($rows as $row)
			{
				$row['time'] = $row['start'] . '-' . $row['finish'];
				$row['presenter'] = \INS\Users::i()->getUsernameByUID($row['presenter']);
				eval("\$schedulelist .= \"".\INS\Template::i()->getAcp("dr_schedule_list")."\";");
			}
		}
			
		eval("\$this->html = \"".\INS\Template::i()->getAcp("dr_schedule_overview")."\";");
		\INS\Template::i()->output( $this->html, $this->title );
	}	

	/**
	 * Edit Event
	 *
	 * @return 		void
	 */
	public function _edit()
	{
		$days = array(
			'Monday', 'Tuesday', 'Wednesday',
			'Thursday', 'Friday', 'Saturday',
			'Sunday'
		);

		$times = array(
			"12AM", "1AM", "2AM", "3AM", "4AM", "5AM", "6AM", "7AM",
			"8AM", "9AM", "10AM", "11AM", "12PM", "1PM", "2PM", "3PM",
			"4PM", "5PM", "6PM", "7PM", "8PM", "9PM", "10PM", "11PM",
		);
		
		if( isset(\INS\Http::i()->submit) AND \INS\Http::i()->request_method == 'post' )
		{
			$fields = array('name', 'description', 'day', 'start', 'finish', 'presenter');

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
			\INS\File::i()->uploadFileLocation = 'schedule'; 
			\INS\File::i()->Execute();
			
			if( \INS\File::i()->errorString != '' )
			{
				$crash = TRUE;
			}
			
			if( $crash === TRUE )
			{
				if($upload->errorString != '')
				{
					\INS\Template::i()->globalNotification = \INS\File::i()->errorString;
				}
			}		
			else	
			{
				if( \INS\File::i()->uploadBox != FALSE )
				{
					$array['banner'] = '?';
					$binds[ $i++ ] = \INS\File::i()->parsedFileName;
				}	
			}	

			$binds[ $i ] = \INS\Http::i()->sid;
			\INS\Db::i()->update('drschedule', $array, 'sid = ?', '', $binds);

			if( $crash !== TRUE )
			{
				\INS\Template::i()->globalNotification = \INS\Language::i()->strings['admin_dynamicradio_schedule_updatedsuccessfully'];
			}	
		}
		
		$row = \INS\Db::i()->fetch('*', 'drschedule', 'sid = ?', '', [ 1 => \INS\Http::i()->sid ] );
		
		$editdays = '<select name="day"><option> - Day - </option>';

		foreach( $days AS $day )
		{
			$selected = "";
			if($day == $row['day'])
			{
				$selected = 'selected="true"';
			}
			$editdays .= '<option value="'.$day.'" ' .$selected. '>' .$day. '</option>';
		}

		$editdays .= '</select>';
		$editstarttime = '<select name="start"><option> - Time - </option>';

		foreach( $times AS $starttime )
		{
			$selected = "";
			if($starttime == $row['start'])
			{
				$selected = 'selected="true"';
			}
			$editstarttime .= '<option value="'.$starttime.'" ' .$selected. '>' .$starttime. '</option>';
		}

		$editstarttime .= '</select>';
		$editendtime = '<select name="finish"><option> - Time - </option>';

		foreach($times AS $endtime)
		{
			$selected = "";

			if($endtime == $row['finish'])
			{
				$selected = 'selected="true"';
			}

			$editendtime .= '<option value="'.$endtime.'" ' .$selected. '>' .$endtime. '</option>';
		}

		$editendtime .= '</select>';
		$presenters = \INS\Db::i()->fetchAll('`username`, `uid`', 'users', '`group`=\'2\' OR `group`=\'4\'');
		
		foreach($presenters as $presenter)
		{
			$selected = "";
			if($row['presenter'] == $presenter['uid'])
			{
				$selected = 'selected="true"';
			}
			$presenterlist .= "<option value=\"{$presenter['uid']}\" {$selected}>{$presenter['username']}</option>";
		}

		if(strlen($row['banner']) > 0)
		{
			$bannerlink = "<a href=\"btn ins-btn\" onclick=\"window.open('" . \INS\Core::$settings['site']['url'] . "/Uploads/{$row['banner']}', 'newwindow', 'width=300, height=250'); return false;\">Current Banner</a>";
		}
		else
		{
			$bannerlink = "<label style=\"padding-left: 15px; display: inline-block\">No banner has been uploaded for this show.</label>";
		}
		
		eval("\$this->html = \"".\INS\Template::i()->getAcp("dr_schedule_edit")."\";");
		\INS\Template::i()->output( $this->html, \INS\Language::i()->strings['admin_dynamicradio_schedule_edit'] );
	}
	
	/**
	 * Delete Event
	 *
	 * @return 		void
	 */
	public function _delete()
	{
		\INS\Db::i()->delete('drschedule', "`sid` = ?", [ 1 => \INS\Http::i()->sid ] );
		\INS\Http::i()->redirect( $this->link );
	}		

	/**
	 * Overview
	 *
	 * @return 		void
	 */
	public function _add()
	{
		$presenters = \INS\Db::i()->fetchAll('`username`, `uid`', 'users', '`group`=\'2\' OR `group`=\'4\'');
		$presenterlist = '';

		foreach( $presenters AS $presenter )
		{
			$presenterlist .= "<option value=\"{$presenter['uid']}\">{$presenter['username']}</option>";
		}
		
		if( isset(\INS\Http::i()->submit) AND \INS\Http::i()->request_method == 'post' )
		{
			$fields = array('name', 'description', 'day', 'start', 'finish', 'presenter');
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
			\INS\File::i()->uploadFileLocation = 'schedule'; 
			\INS\File::i()->Execute();
			
			if( \INS\File::i()->errorString != '' )
			{
				$crash = TRUE;
			}
			
			if( $crash === TRUE )
			{
				if($upload->errorString != '')
				{
					\INS\Template::i()->globalNotification = \INS\File::i()->errorString;
				}
			}		
			else	
			{
				if( \INS\File::i()->uploadBox != FALSE )
				{
					$array['banner'] = '?';
					$binds[ $i++ ] = \INS\File::i()->parsedFileName;
				}	

				\INS\Db::i()->insert('drschedule', $array, $binds);
				\INS\Template::i()->globalNotification = \INS\Language::i()->strings['admin_dynamicradio_schedule_addsuccessfull'];
			}	
		}

		eval("\$this->html = \"".\INS\Template::i()->getAcp("dr_schedule_add")."\";");
		\INS\Template::i()->output( $this->html, \INS\Language::i()->strings['admin_dynamicradio_schedule_add'] );
	}	
}	