<?php
if (!defined("IN_INS"))
{
    die("<html><head><title>Failure: ins::Loader() Cannot Be Loaded</title></head><body><center style=\"color: #DE0C0C; font-family: monospace\">ins::Loader() Cannot Be Loaded</center></body></html>");
}

class blog_create extends INSAdmin
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
			case 'createpost':
				$this->postCreate();
				break;	
			default: 	
				$this->postCreate();
				break;
		}
    } 
	
	/**
	 * Main page
	 *
	 * @return 		void
	 */
	public function postCreate()
	{
		global $db, $this->html, $this->templates->, $header, $footer, $headerinclude, $lang, $ins, $user;
		
		$lang->load('insblog');
		
		if(isset($this->input['submit']) && $this->ins->request_method == 'post')
		{	
			$array = array(
				'subject' => $this->Db->escape_string($this->input['subject']),
				'post' => $this->Db->escape_string($this->input['post']),
				'date' => DATE('Y-m-d'),
				'time' => time(),
				'author' => $user->username,
				'comments' => '0'
			);
			$this->Db->insert('blogposts', $array, '`pid` = \''.$pid.'\'');
		}

		eval("\$this->html = \"".$this->templates->getacp("blog_create_post")."\";");
	}	
}	
?>	