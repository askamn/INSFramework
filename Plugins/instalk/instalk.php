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
class instalk_controller
{
    /**
	 * Directory
	 *
	 * @var		string
	 */
	public $dir = 'instalk';
	
	/**
	 * Templates
	 *
	 * @var		array
	 */
	public $templates = array(
		'instalk_main' => 'admin'
	);	
	 
	/**
	 * Cache
	 *
	 * @var		array
	 */
	public $cache = array(
		'insblog_nummessages'
	);	
	
	/**
	 * Deactivate an application
	 *
	 * @return 		void
	 */
	public function deactivate()
	{
		return; 
	}	

	/**
	 * Deactivate an application
	 *
	 * @return 		void
	 */
	public function activate()
	{
		return; 
	}	

	/**
	 * Uninstalls an application
	 *
	 * @return 		void
	 */
	public function uninstall()
	{
		/* Hook Removal */
		\INS\Db::i()->delete("inshooks", "`dir` = '{$this->dir}'");
		
		/* Template Removal */
		foreach( $templates AS $t => $g )
		{
			if($g == 'admin')
				\INS\Db::i()->delete("templates_admin", "`dir` = '{$this->dir}'");
			else
				\INS\Db::i()->delete("templates", "`name` = '{$t}' AND `gid` = -1");
		}
		
		/* Cache Removal */
		foreach( $this->caches as $c ) 
		{
			\INS\Cache::i()->delete($c); 
		}	
	}	
}
?>