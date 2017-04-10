<?php
if (!defined("IN_INS"))
{
    die("<html><head><title>Failure: ins::Loader() Cannot Be Loaded</title></head><body><center style=\"color: #DE0C0C; font-family: monospace\">ins::Loader() Cannot Be Loaded</center></body></html>");
}

class talk_overview extends INSAdmin
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
		global $ins;
		
		$this->link = $this->ins->settings['site']['url'].'/'.$this->ins->config['admin']['admin_dir'].'/index.php?app='.$this->input['app'].'&module='.$this->input['module'].'&section='.$this->input['section'];
	}
	
	/**
	 * Selects which page to display
	 *
	 * @return		void
	 */
	public function Execute()
	{	
		global $ins;
		
		switch($this->input['view'])
		{			
			case 'ajaxrequesthandler':
				$this->ajaxRequestHandler();
				break;		
			case 'delete':	
				$this->talkMessageDelete();
				break;
			case 'view':
				$this->talkMessageView();
			default: 	
				$this->talkOverview();
				break;
		}
    } 
	
	/**
	 * Main page
	 *
	 * @return 		void 
	 */
	public function talkOverview()
	{
		global $db, $this->html, $this->templates->, $header, $footer, $headerinclude, $lang, $ins, $this->templates->globalNotification, $user;
		
		$lang->load('instalk');
		
		if(!isset($this->input['page']))
		{
			$page = 1;
		}
		else
		{
			$page = intval($this->input['page']);
		}
		
		$limit = $page*$this->ins->settings['admin']['resultsperpage'];
		$_limit = $limit-$this->ins->settings['admin']['resultsperpage'];
		
		$rows = $this->Db->fetchAll('*', 'instalk', "`toid` = {$user->uid}", "{$_limit}, {$limit}");
		
		if(!empty($rows))
		{
			foreach($rows as $row)
			{
				if($row['status'] == 0)
				{
					$row['subject'] = '<b>'.$row['subject'].'<br/>';
				}	
				if(strlen($row['subject']) > 70)
				{
					$row['subject'] = substr($row['subject'], 0, 70) . '...';
				}

				$row['author'] = @getUsernameByUID($row['fromid']);

				/* H:i Conversion*/
				$array = explode(":", $this->ins->settings['datetime']['time_format']);
				$timeFormat = $array[0] . ':' . $array[1];

				$row['stime'] = insDate($row['stime'], $this->ins->settings['datetime']['date_format'], 1) . ' @ ' . insDate($row['stime'], $timeFormat);
				eval("\$messagelist .= \"".$this->templates->getacp("talk_messages_list")."\";");	
			}
		}
		else
		{
			$messagelist = $lang->instalk_admin_messengerEmpty;
		}	
		
		if(isset($this->input['do']))
		{
			if($this->input['do'] == 'showdeletesuccess')
			{
				$this->templates->globalNotification = $lang->instalk_admin_messagedeletedsuccess;
			}
			elseif ($this->input['do'] == 'nomessageselected') 
			{
				$this->templates->globalNotification = $lang->instalk_admin_nomessageselected;
			}
		}
			
		$num = $this->Db->rowCount('instalk');
		
		$pagination = buildPagination($page, $this->ins->settings['admin']['resultsperpage'], $num, 'index.php?app=instalk&module=talk&section=overview');
		
		eval("\$this->html = \"".$this->templates->getacp("talk_overview")."\";");
	}	

	/**
	 * Deletes Message/s
	 *
	 * @return 	void
	 */
	public function talkMessageDelete()
	{
		global $ins, $db;

		if(isset($this->input['messagecheckboxarray']) && !isset($this->input['mid']))
		{
			$string = is_array($this->input['messagecheckboxarray']) ? implode(',', $this->input['messagecheckboxarray']) :  $this->input['messagecheckboxarray'];

			$this->Db->delete('instalk', '`mid` IN ('.$string.')');

			redirect($this->link.'&do=showdeletesuccess');
		}
		else if(isset($this->input['mid']))
		{
			$this->Db->delete('instalk', '`mid` = \''.$this->Db->escape_string($this->input['mid']) . '\'');
			redirect($this->link.'&do=showdeletesuccess');
		}
		else
		{
			redirect($this->link.'&do=nomessageselected');
		}
	}

	/**
	 * View a message
	 *
	 * @return 	void
	 */
	public function talkMessageView()
	{
		global $db, $this->html, $this->templates->, $header, $footer, $headerinclude, $lang, $ins, $this->templates->globalNotification, $user;
		
		$lang->load('instalk');

		$row = $this->Db->fetch('*', 'instalk', '`mid` = \'' . $this->Db->escape_string($this->input['mid']) . '\'');

		eval("\$this->html = \"".$this->templates->getacp("talk_message_view")."\";");	
	}

	/**
	 * AJAX Request Handler
	 *
	 * @return 	void
	 */
	public function ajaxRequestHandler()
	{
		global $ins, $db, $user, $lang, $this->templates->;
		
		/* Load Language */
		$lang->load('instalk');
		
		if($this->ins->request_method == 'post')	
		{	
			if(isset($this->input['subject']))
			{
				$name = str_replace('"', '', $this->input['subject']);
				$rows = $this->Db->fetchAll('*', 'instalk', '`subject` LIKE \'%'.$name.'%\'');

				if(!empty($rows))
				{
					/* Begin: Table */
					$string .= "<table class=\"table\">";
					
					/* Begin: Table Header */
					$string .= "<thead>";
					$string .= "<th>{$lang->instalk_admin_subject}</th>";
					$string .= "<th>{$lang->instalk_admin_author}</th>";
					$string .= "<th>{$lang->instalk_admin_date}</th>";
					$string .= "<th>{$lang->instalk_admin_options}</th>";
					$string .= "</thead>";
					/* End: Table Header */
					
					/* Begin: Table Body */
					$string .= "<tbody>";
					foreach($rows as $row)
					{
						eval("\$string .= \"".$this->templates->getacp("talk_messages_list")."\";");
					}
					$string .= "</tbody>";
					/* End: Table Body */
					
					$string .= "</table>";
					/* End: Table */
					
					JSON::printmessage($string);
				}
				else
				{
					JSON::error($lang->instalk_admin_noresult);
				}	
			}		
		}
	}	
}	
?>	