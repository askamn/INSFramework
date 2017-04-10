<?php
function insblog_blogapp()
{
	global $ins_admin_header_extra_modules_header;
	
	$link = '<span class="section-name"><a href="index.php?app=insblog&module=blog&section=overview">IN.Blog</a></span>';
	
	$ins_admin_header_extra_modules_header .= $link;
}

function insblog_indexlatestposts()
{
	global $blogwidget_latestposts, $db, $templates, $ins;
	
	$rows = $db->fetchAll('`subject`, `author`, `date`, `pid`', 'blogposts', '1=1 ORDER BY `time`', '0,3');
	
	if(!empty($rows))
	{
		foreach ($rows as $row)
		{
			$str = strlen($row['subject']);
			if($str > 70)
			{
				$row['subject'] = substr($row['subject'], 0, 67).'...';
			}
			
			eval("\$latestposts .= \"".$templates->get("blog_indexlatestposts")."\";");
		}
	}
	else
	{
		$latestposts = 'Nothing to display...';
	}	
	eval("\$blogwidget_latestposts .= \"".$templates->get("blog_indexlatestpostsbox")."\";");
}	
?>