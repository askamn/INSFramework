<?php
/**
 * <pre>
 *	Filename: payments.php
 *	Description: Handles incoming payment requests.
 *	Last Modified: 4 April 2014
 *  Author: AskAmn
 * </pre>
 *
 * @author AskAmn
 * @link 		
 */

define('THIS_SCRIPT', 'blog.php');
define('IN_INS', 1);

require_once "./init.php"; 
require_once INS_ROOT."/core/global.php";
require_once INS_ROOT."/core/classes/blog.class.php";

/**
 * Force login check
 */
if($ins->settings['users']['force_login'])
{
	if(!$user->INSsession())
	{
		redirect($ins->settings['site']['url']."/members.php");
	}
}

$blog = new Blog;
$blog->load($ins, $lang, $db);
$lang->load('insblog');

/* Render Sidebar, Globally */
eval("\$sidebar = \"".$templates->get("sidebar")."\";"); 

/* Main */
if($ins->input['pid'] == "")
{
	if(!isset($ins->input['page']))
	{
		$page = 1;
	}	
	else
	{
		$page = intval($ins->input['page']);
	}	
		
	$postlist = $blog->mainPagePosts($page);
	eval("\$content = \"".$templates->get("blog")."\";"); 	
}

/* A Post */
if(isset($ins->input['pid']))
{
	$pid = intval($ins->input['pid']);
	
	if(isset($ins->input['submit']) && $ins->request_method == 'post')
	{
		$blog->addComment($ins->input['comment'], $user->username);
	}
	$post = $blog->getPost($pid);
	$postfulllink = $ins->settings['site']['url'].'/blog.php?pid='.$post['pid'];
	if($user->INSsession())
	{
		eval("\$commentbox = \"".$templates->get("blog_commentbox")."\";");	
		if($user->group == '2')
		{
			$editopen = true;
		}
		else
		{
			$editopen = false;
		}
	}
	else
	{
		$commentbox = '<h2>'.$lang->insblog_main_logintoreply.'</h2>';
	}
	
	if($editopen === true)
	{
		$l = $ins->settings['site']['url'].'/'.$ins->config['admin']['admin_dir'].'/index.php?app=insblog&module=blog&section=edit&view=editpost&pid='.$row['pid'];
		$editlink = '<span style="float: right"><a href="'.$l.'"><i class="fa fa-pencil"></i></a></span>';
	}
	else
	{
		$editlink = '';
	}
	$comments = $blog->getComments($pid);
	
	eval("\$content = \"".$templates->get("blog_post")."\";");	
}

$templates->output($content);
?>