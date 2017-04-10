<?php
class Blog
{
	/**
	 * Post
	 *
	 * @var		string
	 */
	public $post;
	
	/**
	 * Post Author
	 *
	 * @var		string
	 */
	public $author;
	
	/**
	 * Post ID
	 *
	 * @var		integer
	 */
	public $pid;
	
	/**
	 * Comment
	 *
	 * @var		string
	 */
	public $comment;
	
	/**
	 * Class loaded?
	 *
	 * @var		boolean
	 */
	private $initiated = FALSE;

	/**
	 * Store instance
	 *
	 * @var		object	
	 */
	private $ins 	 = FALSE;
	
	/**
	 * Store instance
	 *
	 * @var		object	
	 */
	private $lang 	 = FALSE;
	
	/**
	 * Store instance
	 *
	 * @var		object	
	 */
	private $db		= FALSE;
	
	/**
	 * Loads the main required libraries
	 *
	 * @param		object
	 * @param		object
	 * @param		object
	 * @return		boolean
	 */
	public function load(INSController $ins, Language $lang, Database $db)
	{
		$this->ins = $ins;
		$this->lang = $lang;
		$this->db = $db;
	}	
	 
	/**
	 * Loads posts
	 *
	 * @param		integer	
	 * @return		string
	 */
	public function mainPagePosts($page = 1)
	{	
		global $templates, $user;
		
		$this->lang->load('insblog');
		$lang = $this->lang;
		
		$limit = $page*$this->ins->settings['insblog']['resultsperpage'];
		$_limit = $limit-$this->ins->settings['insblog']['resultsperpage'];
		
		if($user->INSsession())
		{
			if($user->group == '2')
			{
				$editopen = true;
			}
			else
			{
				$editopen = false;
			}
		}
		
		$rows = $this->db->fetchAll('*', 'blogposts', '1=1', "{$_limit}, {$limit}");
		$myposts = '';
		foreach($rows AS $row)
		{
			$postfulllink = $this->ins->settings['site']['url'].'/blog.php?pid='.$row['pid'];
			
			if($editopen === true)
			{
				$l = $this->ins->settings['site']['url'].'/'.$this->ins->config['admin']['admin_dir'].'/index.php?app=insblog&module=blog&section=edit&view=editpost&pid='.$row['pid'];
				$editlink = '<span style="float: right"><a href="'.$l.'"><i class="fa fa-pencil"></i></a></span>';
			}
			else
			{
				$editlink = '';
			}
			
			if(strlen($row['post'] > 200))
			{
				$shortpost = substr($row['post'], 0, 200)." <a href=\"{$postfulllink}\">[...]</a>";
			}
			else
			{
				$shortpost = $row['post']." <a href=\"{$postfulllink}\">[...]</a>";
			}	
			eval("\$myposts .= \"".$templates->get("blog_postlist")."\";");
		}
		
		return $myposts;
	}
	
	/**
	 * Gets a blog post
	 *
	 * @param		integer		The blog posts id
	 * @return		array
	 */
	public function getPost($pid)
	{	
		$row = $this->db->fetch('*', 'blogposts', "`pid` = {$pid}");	
		return $row;
	}
	
	/**
	 * Gets comments on a blog post
	 *
	 * @param		integer		The blog posts id
	 * @return		string
	 */
	public function getComments($pid)
	{
		global $templates;
		$lang = $this->lang;
		$rows = $this->db->smart_query("SELECT c.author, c.comment, c.date, u.username, u.email, u.uid, u.avatar FROM `blogposts` b, `blogcomments` c, `users` u WHERE b.pid = c.pid AND c.author = u.username", array("rows" => 2));
		
		$comments = '';
		$cuid = 1;
		foreach($rows AS $row)
		{
			$link = $this->ins->settings['site']['url'].'/members.php?action=profile&uid='.$row['uid'];
			$avatar = $this->ins->settings['site']['url'].'/uploads/avatars/'.$row['avatar'];
			$row['comment'] = htmlentities($row['comment']);
			
			eval("\$comments .= \"".$templates->get("blog_comments")."\";");
			$cuid++;
		}
		return $comments;
	}
	
	/**
	 * Inserts a post in database
	 *
	 * @param		integer		The blog posts id
	 * @return		void
	 */
	public function createPost()
	{
		$array = array(
			'post' => $this->db->escape_string($this->post),
			'comments' => '0',
			'author' => $this->db->escape_string($this->author),
			'date' => insDate(time()),
			'time' => time()
		);
		
		$this->db->insert('blogposts', $array);
	}
	
	/**
	 * Adds a comment to a blog post
	 *
	 * @param		string		Comment to insert
	 * @param		string		Comment author
	 * @param		string		Author's email
	 * @return		void
	 */
	public function addComment($comment, $author, $email = '')
	{
		$this->comment = INS::parseValue($this->comment);
		$this->comment = '<pre>'.htmlentities($this->comment).'</pre>';
		$pid = intval($this->ins->input['pid']);
		
		$array = array(
			'pid' => $pid,
			'comment' => $this->db->escape_string($comment),
			'author' => $this->db->escape_string($author),
			'date' => insDate(time()),
			'time' => time()
		);
		$this->db->insert('blogcomments', $array);
		$this->db->smart_query('UPDATE `blogposts` SET `comments`=`comments`+1 WHERE `pid`=\''.$pid.'\'');
	}
	
	/**
	 * Updates a post in database
	 *
	 * @param		integer		The blog posts id
	 * @return		void
	 */
	public function update($pid)
	{	
		$array = array(
			'post' => $this->post
		);
		$pid = intval($this->ins->input['pid']);
		$this->db->smart_query('blogposts', $array, "`pid` = '{$pid}'");
	}
}	
?>