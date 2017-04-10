<?php
/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.5.0
 * ServerInfo
 * Last Updated: $Date: 2014-10-28 5:35 (Tue, 26 Oct 2014) $
 * </pre>
 * 
 * @author      $Author: AskAmn$
 * @package     IN.CMS
 * @copyright   (c) 2014 Infusion Network Solutions
 * @license     http://www.infusionnetwork/licenses/license.php?view=main&version=2014  Revision License 0.1.1
 * @since       0.5.0
 * @version     Release: 0510
 */

class system_serverinfo Extends \INS\Admin
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
		$this->title = \INS\Language::i()->strings['admin_modules_system_serverinfo_title'];
		$this->link = \INS\Http::i();
		\INS\Template::i()->addToNavigation( \INS\Language::i()->strings['admin_modules_system_serverinfo_navtitle'], $this->link );
	}
	
	/**
	 * Selects which page to display
	 *
	 * @return		void
	 */
	public function Execute()
	{	
		switch( \INS\Http::i()->request )
		{
			default:
				$this->_default();
				break;
		}
    } 
	
	/**
	 * @brief  Default request
	 */
	public function _default()
	{	
		$load = $this->getServerLoad( );
		$show_server_info = '<tr><td>{admin_modules_system_server_serverload}</td><td>' . $load['load'] . '</td></tr>';
		$show_server_info .= '<tr><td>{admin_modules_system_server_processors}</td><td>' . $load['procs'] . '</td></tr>';
	   
		foreach ($_SERVER AS $key => $value)
		{
		   $show_server_info .= "<tr>";
		   $show_server_info .= "<td>";
		   $show_server_info .= $key;
		   $show_server_info .= "</td>";
		   $show_server_info .= "<td>";
		   $show_server_info .= $value;
		   $show_server_info .= "</td>";
		   $show_server_info .= "</tr>";
		}

		eval("\$this->html = \"". \INS\Template::i()->getAcp("server_serverinfo")."\";");
		\INS\Template::i()->output( $this->html, $this->title );
    } 
	
	/**
	 * Gets server load
	 *
	 * @param 		boolean 		Windows operating system?
	 * @return 		array|boolean
	 * @access 		public
	 */
	public function getServerLoad( $windows = FALSE )
	{
		$os = strtolower(PHP_OS);
		
		if( strpos($os, 'win' ) === FALSE )
		{
			if(file_exists('/proc/loadavg'))
			{
				$load = file_get_contents('/proc/loadavg');
				$load = explode(' ', $load, 1);
				$load = $load[0];
			}
			elseif(function_exists('shell_exec'))
			{
				$load = explode(' ', `uptime`);
				$load = $load[count($load)-1];
			}
			else
			{
				return FALSE;
			}

			if(function_exists('shell_exec'))
			{
				$cpu_count = shell_exec('cat /proc/cpuinfo | grep processor | wc -l');        
			}	

			return array( 'load' => $load, 'procs' => $cpu_count );
		}
		else
		{
			if(class_exists('COM'))
			{
				$wmi  = new COM('WinMgmts:\\\\.');
				$cpus = $wmi->InstancesOf('Win32_Processor');
				$load = 0;
				$ncpu = 0;
				if( version_compare('4.50.0', PHP_VERSION) == 1 )
				{
					while($cpu = $cpus->Next())
					{
						$load += $cpu->LoadPercentage;
						$cpu_count++;
					}
				}
				else
				{
					foreach( $cpus as $cpu )
					{ 
						$load += $cpu->LoadPercentage;
						$ncpu++;
					}
				}

				return array('load' => $load, 'procs' => $ncpu);
			}

			return FALSE;
		}

		return FALSE;
	}
}
?>