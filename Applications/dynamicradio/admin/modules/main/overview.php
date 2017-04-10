<?php
if (!defined("IN_INS"))
{
    die("<html><head><title>Failure: ins::Loader() Cannot Be Loaded</title></head><body><center style=\"color: #DE0C0C; font-family: monospace\">ins::Loader() Cannot Be Loaded</center></body></html>");
}

class main_overview extends INSAdmin
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
		$this->link = $this->ins->settings['site']['url'].'/admin/index.php?app='.$this->input['app'].'&module='.$this->input['module'].'&section='.$this->input['section'];
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
			case 'overview':
				$this->drOverview();
				break;	
			default: 	
				$this->drOverview();
				break;
		}
    } 
	
	/**
	 * Overview
	 *
	 * @return 		void
	 */
	public function drOverview()
	{
		global $db, $this->html, $this->templates->, $header, $footer, $headerinclude, $lang, $ins;
		
		/**
		 * Cover Requests
		 * 
		 * 0 -> No Action Taken
		 * 1 -> Approved
		 * 2 -> Denied
		 */
		$coverrequests = intval($this->Db->rowCount('drcovers', '`status` = \'0\''));
		eval("\$this->html = \"".$this->templates->getacp("dr_overview")."\";");
	}	
}	