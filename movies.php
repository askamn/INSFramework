<?php
/**
 * <pre>
 *	Filename: movies.php
 *	Description: A movies section.
 *	Last Modified: 4 April 2014
 *  Author: AskAmn
 * </pre>
 *
 * @author AskAmn
 * @link 		
 */

define('THIS_SCRIPT', 'movies.php');
define('IN_PRE_PROCESSOR', 1);

require_once "./init.php"; 
require_once ROOT."/classes/movies.class.php";

$movies = new Movies;

/* Main */
if($core->input['action'] == "")
{
	if(!isset($core->input['page']))
		$page = 1;
	else
		$page = $core->input['page'];
		
	$list = $movies->movies_list($page);
	eval("\$content = \"".$templates->get("movies")."\";"); 	
}

/* A Post */
if($core->input['action'] == "post")
{
	if(!isset($core->input['pid']))
		redirect("{$settings['site']['url']}/blog.php");
		
	$post = $blog->get_post($core->input['pid']);
	$comments = $blog->get_comments($pid);
	
	eval("\$content = \"".$templates->get("movies_movie")."\";");	
}

$templates->output($content);
?>