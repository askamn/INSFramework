<?php

$hooks[] = array(
	'hook_key' => 'index_posts',
	'hook_load_position' => 'index_start',
	'file' => 'hooks'
);

$hooks[] = array(
	'hook_key' => 'members_posts',
	'hook_load_position' => 'members_start',
	'file' => 'hooks'
);

$hooks[] = array(
	'hook_key' => 'blogapp',
	'hook_load_position' => 'admin_header_apps',
	'file' => 'hooks'
);

?>