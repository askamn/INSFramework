<?php
class insblog_Upgrade
{
	/**
	 * Ins Blog Upgrade Class	
	 *
	 * @return		void
	 */
	public function run()
	{
		global $db, $ins;
		
		$hooks[] = array(
			'hook_key' => 'blogapp',
			'hook_load_position' => 'admin_header_apps'
		);
		
		foreach($hooks AS $hook)
		{
			$db->insert('inshooks', $hook);
		}	
		
		$sidebar = array(
			'link' => 'app=insblog&module=blog&section=overview',
			'icon' => 'pencil',
			'name' => 'INSBlog',
			'app' => 'insblog',
			'module' => 'overview'
		);
		$db->insert('sidebar', $sidebar);

		/* Upgrade Version Number! */
		$db->update('applications', array('version' => '1.0.1', 'version_code' => '101'), '`dir` = \'insblog\'');
		
		redirect("{$ins->settings['site']['url']}/admin/index.php?app=system&module=applications&section=applications");
	}
}
?>