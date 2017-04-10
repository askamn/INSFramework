<?php
if (!defined("IN_INS"))
{
    die("<html><head><title>Failure: ins::Loader() Cannot Be Loaded</title></head><body><center style=\"color: #DE0C0C; font-family: monospace\">ins::Loader() Cannot Be Loaded</center></body></html>");
}

class blog_edit extends INSAdmin
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
			case 'editpost':
				$this->postEdit();
				break;	
			case 'delete':
				$this->postDelete();
				break;	
			case 'open':
				$this->postOpen();
				break;	
			case 'close':
				$this->postClose();
				break;			
			default: 	
				$this->postEdit();
				break;
		}
    } 
	
	/**
	 * Main page
	 *
	 * @return 		void
	 */
	public function postEdit()
	{
		global $db, $this->html, $this->templates->, $header, $footer, $headerinclude, $lang, $ins, $this->templates->globalNotification;
		
		$lang->load('insblog');
		$pid = intval($this->input['pid']);
		
		if(isset($this->input['submit']) && $this->ins->request_method == 'post')
		{	
			$array = array(
				'subject' => $this->Db->escape_string($this->input['subject']),
				'post' => $this->Db->escape_string($this->input['post']),
			);
			$this->Db->update('blogposts', $array, '`pid` = \''.$pid.'\'');
			
			$this->templates->globalNotification = $lang->insblog_admin_posteditedsuccess;
		}
		$row = $this->Db->fetch('*', 'blogposts', '`pid` = \''.$pid.'\'');
		
		if(strlen($row['subject']) > 80)
		{
			$subject = substr($row['subject'], 0, 80)."...";	
		}
		else
		{
			$subject = $row['subject'];
		}
		$lang->insblog_editing_post = $lang->parse($lang->insblog_editing_post, $subject);
		eval("\$this->html = \"".$this->templates->getacp("blog_edit_post")."\";");
	}	
	
	/**
	 * Main page
	 *
	 * @return 		void
	 */
	public function postDelete()
	{
		global $db, $this->html, $this->templates->, $header, $footer, $headerinclude, $lang, $ins;
		
		$this->Db->delete('blogposts', '`pid`=\''.$this->input['pid'].'\'');
		$this->Db->delete('blogcomments', '`pid`=\''.$this->input['pid'].'\'');
		
		redirect("{$this->ins->settings['site']['url']}/{$this->ins->config['admin']['admin_dir']}/index.php?action=insblog&module=blog&section=overview&do=showdeletesuccess");
	}	

	/**
	 * Close Post
	 *
	 * @return 		void
	 */
	public function postClose()
	{
		global $db, $ins;
		
		$pid = intval($this->input['pid']);
		$this->Db->update('blogposts', array('closed' => '1'), "`pid` = '{$pid}'");
		
		redirect("{$this->ins->settings['site']['url']}/{$this->ins->config['admin']['admin_dir']}/index.php?app=insblog&module=blog&section=overview&do=showclosesuccess");
	}		
	
	/**
	 * Open Post
	 *
	 * @return 		void
	 */
	public function postOpen()
	{
		global $db, $ins;
		
		$pid = intval($this->input['pid']);
		$this->Db->update('blogposts', array('closed' => '2'), "`pid` = '{$pid}'");
		
		redirect("{$this->ins->settings['site']['url']}/{$this->ins->config['admin']['admin_dir']}/index.php?app=insblog&module=blog&section=overview&do=showopensuccess");
	}		
}	
?>	