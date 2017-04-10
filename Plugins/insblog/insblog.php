<?php
/**
 * General App class for INS Blog
 * 
 *
 *
 * @author		$AskAmn$
 * @since 		$16 May 2014$	
 * @lastupdate	$16 May 2014$
 * @version		100
 * @package		$IN.Cms$
 */
class insblog_controller
{
    /**
	 * Directory
	 *
	 * @var		string
	 */
	public $dir = 'insblog';
	
	/**
	 * Templates
	 *
	 * @var		array
	 */
	public $templates = array(
		'1NAME' => 'admin'
	);	
	 
	/**
	 * Cache
	 *
	 * @var		array
	 */
	public $cache = array(
		'insblog_numposts'
	);	
	  
	/**
	 *	Automatically initiated by installer
	 *
	 *	@return 		void
	 */
	public function run()
	{
		global $db, $cache;
		
		/**
		 * Remove hooks
		 */
		$db->delete("inshooks", "`dir` = '{$this->dir}'");
		
		/**
		 * Delete Templates
		 */
		foreach($templates as $t => $g)
		{
			if($g == 'admin')
			{
				$db->delete("templates_admin", "`dir` = '{$this->dir}'");
			}
			else
			{
				$db->delete("templates", "`name` = '{$t}' AND `gid` = -1");
			}	
		}
		
		/**
		 * Remove any database inserts
		 */
		
		/**
		 * Remove Cache
		 */	
		foreach($this->caches as $c) 
		{
			$cache->delete($c); 
		}	
	}
	
	/**
	 * Deactivate an application
	 *
	 * @return 		void
	 */
	public function deactivate()
	{
		global $db, $cache;
			
		/**
		 * Do any stuff, that does not delete data.... like remove cache, etc
		 */	
		return; 
	}	
}
?>