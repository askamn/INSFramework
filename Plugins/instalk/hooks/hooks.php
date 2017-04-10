<?php
function instalk_instalkapp()
{
	global $ins_admin_header_extra_modules_header;
	
	$link = '<span class="section-name"><a href="index.php?app=instalk&module=talk&section=overview">IN.Talk</a></span>';
	
	$ins_admin_header_extra_modules_header .= $link;
}

?>