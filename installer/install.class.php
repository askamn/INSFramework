<?php

/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.5.0
 * Installer Class - Handles IN.Cms installation.
 * Last Updated: $Date: 2014-10-26 22:40:30 (Sun, 26 Oct 2014) $
 * </pre>
 * 
 * @author 		$Author: AskAmn$
 * @copyright	(c) 2014 Infusion Network Solutions
 * @license		http://www.infusionnetwork/licenses/license.php?view=main&version=2014
 * @package		IN.CMS
 * @since 		0.3; 22 August 2014
 * @version 	$Revision: 	01224 $
 */

class Installer {
	/** 
	 * The header of our installer
	 * 
	 * @params -do- 
	 */
	function header()
	{
		global $ins, $lang;
		
		/* Title */
		switch($ins->input['action'])
		{
			case "": 	$title = $lang->installer_title;
						$step = "1";
						setcookie('step', $step);	
			break;
			case "intro": 	$title = $lang->installer_title;
							$step = "1";		
							setcookie('step', $step);	
			break;
			case "license": $title = $lang->installer_title_license;
						    $step = "2";
							if(!isset($ins->cookies['step']))
								redirect("index.php?action=intro");	
							else
							{
								if($ins->cookies['step'] == $step-1) /* Is the 'step' cookie equal to previous step? */
									setcookie('step', $step);
								else
									die("Please complete the previous steps first in order to proceed.");
							}	
			break;
			case "requirements": 	$title = $lang->installer_title_requirements;
									$step = "3";
									if(!isset($ins->cookies['step']))
									redirect("index.php?action=intro");
									else
									{
										if($ins->cookies['step'] == $step-1) /* Is the 'step' cookie equal to previous step? */
											setcookie('step', $step);
										else
											die("Please complete the previous steps first in order to proceed.");
									}	
			break;
			case "database": 	$title = $lang->installer_title_database;
								$step = "4";
								if(!isset($ins->cookies['step']))
									redirect("index.php?action=intro");
								else
								{
									if(!isset($ins->input['submit']))
									{
										if($ins->cookies['step'] == $step-1) /* Is the 'step' cookie equal to previous step? */
											setcookie('step', $step);
										else
											die("Please complete the previous steps first in order to proceed.");
									}		
								}	
			break;
			case "tables": 	$title = $lang->installer_title_tables;
							$step = "5";
							if(!isset($ins->cookies['step']))
								redirect("index.php?action=intro");
							else
							{
								if($ins->cookies['step'] == $step-1) /* Is the 'step' cookie equal to previous step? */
									setcookie('step', $step);
								else
									die("Please complete the previous steps first in order to proceed.");
							}		
			break;
			case "data": 	$title = $lang->installer_title_rows;
							$step = "6";
							if(!isset($ins->cookies['step']))
								redirect("index.php?action=intro");
							else
							{
								if($ins->cookies['step'] == $step-1) /* Is the 'step' cookie equal to previous step? */
									setcookie('step', $step);
								else
									die("Please complete the previous steps first in order to proceed.");
							}	
			break;
			case "templates": 	$title = $lang->installer_title_templates;
								$step = "7";
								if(!isset($ins->cookies['step']))
									redirect("index.php?action=intro");
								else
								{
									if($ins->cookies['step'] == $step-1) /* Is the 'step' cookie equal to previous step? */
										setcookie('step', $step);
									else
										die("Please complete the previous steps first in order to proceed.");
								}	
			break;
			case "config":  $title = $lang->installer_title_config;
						    $step = "8";
						    if(!isset($ins->cookies['step']))
								redirect("index.php?action=intro");
						    else
							{
								if(!isset($ins->input['submit']))
								{
									if($ins->cookies['step'] == $step-1) /* Is the 'step' cookie equal to previous step? */
										setcookie('step', $step);
									else
										die("Please complete the previous steps first in order to proceed.");
								}	
							}		
			break;
			case "admin":   $title = $lang->installer_title_admin;
						    $step = "9";
						    if(!isset($ins->cookies['step']))
								redirect("index.php?action=intro");
							else
							{
								if(!isset($ins->input['submit']))
								{
									if($ins->cookies['step'] == $step-1) /* Is the 'step' cookie equal to previous step? */
										setcookie('step', $step);
									else
										die("Please complete the previous steps first in order to proceed.");
								}	
							}	
			break;
			case "finish":  $title = $lang->installer_title_done;
						    $step = "10";
						    if(!isset($ins->cookies['step']))
								redirect("index.php?action=intro");
							else
							{
								if($ins->cookies['step'] == $step-1) /* Is the 'step' cookie equal to previous step? */
									setcookie('step', $step);
								else
									die("Please complete the previous steps first in order to proceed.");
							}	
			break;
		}	
		
		$output .=  "<html>
						<head>
							<title>{$title} - {$lang->installer_apptitle}</title>
							<link href=\"style.css\" type=\"text/css\" rel=\"stylesheet\" media=\"all\">
							<link href=\"//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css\" rel=\"stylesheet\">
						</head>
						<body>
							<div class=\"container\">
								<div class=\"header\">Step {$step}: {$title}<div class=\"mascot\"></div></div>
								<div class=\"main\">
					";
		echo $output;			
	}
	
	/** 
	 * The footer of our installer
	 * 
	 * @params -do- 
	 */
	function footer()
	{
		global $ins, $lang, $copy_year;
		
		$output .=  "
							<div class=\"footer\">	
								<div class=\"copyright\">&copy; {$copy_year}, {$lang->installer_footer_text}	
							</div>		
						</div><!-- Main -->
					</body>
					</html>	
					";
		echo $output;			
	}
	
	/** 
	 * License Step
	 * 
	 * @params -do- 
	 */ 
	function license()
	{
		global $lang;
		$buttons = '<a class="button" href="index.php?action=requirements">Next &raquo;</a>';
		$output = "<div class=\"sidebar\">
									<ul>
										<li class=\"sidebar_item\">{$lang->installer_intro}</li>
										<li class=\"sidebar_item current\">{$lang->installer_license_agreement}</li>
										<li class=\"sidebar_item\">{$lang->installer_requirements}</li>
										<li class=\"sidebar_item\">{$lang->installer_database}</li>
										<li class=\"sidebar_item\">{$lang->installer_table}</li>
										<li class=\"sidebar_item\">{$lang->installer_rows}</li>
										<li class=\"sidebar_item\">{$lang->installer_templates}</li>
										<li class=\"sidebar_item\">{$lang->installer_config}</li>
										<li class=\"sidebar_item\">{$lang->installer_admin}</li>
										<li class=\"sidebar_item\">{$lang->installer_finalize}</li>
									</ul>	
					</div>				
					";
		$output .= "<div class=\"content\">
						{$lang->installer_license_text}
					</div>	
					<div class=\"button_container\">
							{$buttons}
					</div>
					";
		echo $output;
	}
	
	/** 
	 * Intro Step
	 * 
	 * @params -do- 
	 */ 
	function intro()
	{
		global $lang;
		$buttons = '<a class="button" href="index.php?action=license">Next &raquo;</a>';
		$output = "<div class=\"sidebar\">
									<ul>
										<li class=\"sidebar_item current\">{$lang->installer_intro}</li>
										<li class=\"sidebar_item\">{$lang->installer_license_agreement}</li>
										<li class=\"sidebar_item\">{$lang->installer_requirements}</li>
										<li class=\"sidebar_item\">{$lang->installer_database}</li>
										<li class=\"sidebar_item\">{$lang->installer_table}</li>
										<li class=\"sidebar_item\">{$lang->installer_rows}</li>
										<li class=\"sidebar_item\">{$lang->installer_templates}</li>
										<li class=\"sidebar_item\">{$lang->installer_config}</li>
										<li class=\"sidebar_item\">{$lang->installer_admin}</li>
										<li class=\"sidebar_item\">{$lang->installer_finalize}</li>
									</ul>	
					</div>				
					";
		$lang->installer_intro_text = $lang->parse($lang->installer_intro_text, $lang->installer_apptitle);			
		$output .= "<div class=\"content\">
						{$lang->installer_intro_text}
					</div>	
					<div class=\"button_container\">
							{$buttons}
					</div>
					";
		echo $output;
	}
	
	/** 
	 * Requirements Step
	 * 
	 * @params -do- 
	 */ 
	function requirements()
	{
		global $lang;
		
		/* PHP Version */
		$phpversion = phpversion();
		$proceed = true;
		
		/* Check PHP Version */
		if (phpversion() < '5.1.0') {
			$php = "<span class=\"error\">{$phpversion}</span>";
			$proceed = false;
		}
		else
			$php = "<span class=\"success\">{$phpversion}</span>";
		
		/* Is PDO Extension Present */	
		if (!extension_loaded('PDO')) {
			$pdo = "<span class=\"error\">Extension Missing</span>";
			$proceed = false;
		}
		else
			$pdo = "<span class=\"success\">Available</span>";
			
		/* Check config.php status */	
		if (!is_writable(INS_ROOT.'/core/config.php')) {
			$config = "<span class=\"error\">Not Writeable</span>";
			$proceed = false;
		}
		else
			$config = "<span class=\"success\">Writeable</span>";
		
		/* Check settings.php status */	
		if (!is_writable(INS_ROOT.'/core/settings.php')) {
			$settings = "<span class=\"error\">Not Writeable</span>";
			$proceed = false;
		}
		else
			$settings = "<span class=\"success\">Writeable</span>";
			
		// Check upload directory is writable
		$uploads = @fopen(INS_ROOT.'/uploads/t.w', 'w');
		if(!$uploads)
		{
			$uploadfolder = "<span class=\"error\">Not Writeable</span>";
			$proceed = false;
		}
		else
		{
			$uploadfolder = "<span class=\"success\">Writeable</span>";
			@fclose($uploads);
			@unlink(INS_ROOT.'/uploads/t.w');
		}	
		
		/* Are we clear to proceed? */	
		if($proceed === false)
		{
			$buttons = "";
			$buttons .= '<a class="button disabled">Next &raquo;</a>';
			$footertext = $lang->installer_requirements_check_failed;
			$status  = "error";
		}	
		else
		{
			$buttons = '<a class="button" href="index.php?action=database">Next &raquo;</a>';
			$footertext = $lang->installer_requirements_check_passed;
			$status  = "success";
		}
			
		$output = "<div class=\"sidebar\">
						<ul>
							<li class=\"sidebar_item\">{$lang->installer_intro}</li>
							<li class=\"sidebar_item\">{$lang->installer_license_agreement}</li>
							<li class=\"sidebar_item current\">{$lang->installer_requirements}</li>
							<li class=\"sidebar_item\">{$lang->installer_database}</li>
							<li class=\"sidebar_item\">{$lang->installer_table}</li>
							<li class=\"sidebar_item\">{$lang->installer_rows}</li>
							<li class=\"sidebar_item\">{$lang->installer_templates}</li>
							<li class=\"sidebar_item\">{$lang->installer_config}</li>
							<li class=\"sidebar_item\">{$lang->installer_admin}</li>
							<li class=\"sidebar_item\">{$lang->installer_finalize}</li>	
						</ul>	
					</div>		
				 ";
		$output .= "<div class=\"content\">
					<table class=\"table\" cellpadding=\"0\" cellspacing=\"0\">
						<thead>
							<tr>
								<td class=\"tlabel\">Entity</td>
								<td class=\"tlabel\">Status</td>
							</tr>	
						</thead>
						<tbody>
							<tr>
								<td class=\"trow\">PHP Version Check</td>
								<td class=\"trow\">{$php}</td>
							</tr>
							<tr>
								<td class=\"trow\">PDO Extension</td>
								<td class=\"trow\">{$pdo}</td>
							</tr>
							<tr>
								<td class=\"trow\">Config File Writeable</td>
								<td class=\"trow\">{$config}</td>
							</tr>
							<tr>
								<td class=\"trow\">Settings File Writeable</td>
								<td class=\"trow\">{$settings}</td>
							</tr>
							<tr>
								<td class=\"trow\">Uploads Folder Writeable</td>
								<td class=\"trow\">{$uploadfolder}</td>
							</tr>
						</tbody>	
						<tfoot>
							<tr>
								<td colspan=\"2\" class=\"tfoot tlabel {$status}\">{$footertext}</td>
							</tr>	
						</tfoot>
					</table>	
					</div>
					<div class=\"button_container\">
							{$buttons}
					</div>	
					";
		echo $output;
	}
	
	/** 
	 * Database
	 * 
	 * @params -do- 
	 */ 
	function database()
	{
		global $lang, $ins;
		
		$output = "<div class=\"sidebar\">
						<ul>
							<li class=\"sidebar_item\">{$lang->installer_intro}</li>
							<li class=\"sidebar_item\">{$lang->installer_license_agreement}</li>
							<li class=\"sidebar_item\">{$lang->installer_requirements}</li>
							<li class=\"sidebar_item current\">{$lang->installer_database}</li>
							<li class=\"sidebar_item\">{$lang->installer_table}</li>
							<li class=\"sidebar_item\">{$lang->installer_rows}</li>
							<li class=\"sidebar_item\">{$lang->installer_templates}</li>
							<li class=\"sidebar_item\">{$lang->installer_config}</li>
							<li class=\"sidebar_item\">{$lang->installer_admin}</li>
							<li class=\"sidebar_item\">{$lang->installer_finalize}</li>	
						</ul>	
					</div>		
				 ";
		$output .= "<div class=\"content\">
						<form class=\"form\" name=\"database\" action=\"index.php?action=database\" method=\"post\">
							<div class=\"row_header\">{$lang->installer_db_config}</div>
							<div class=\"row_element\">
								<label>Host</label><input type=\"text\" name=\"host\" required=\"\" value=\"localhost\" />
							</div>
							<div class=\"row_element\">		
								<label>Database</label><input type=\"text\" name=\"database\" required=\"\" />
							</div>
							<div class=\"row_element\">
								<label>Username</label><input type=\"text\" name=\"username\" required=\"\" />
							</div>
							<div class=\"row_element\">
								<label>Password</label><input type=\"text\" name=\"password\" />
							</div>
							<div class=\"row_element\">	
								<input type=\"submit\" name=\"submit\" class=\"button\" value=\"Next &raquo;\"/>
							</div>			
						</form>
					</div>
					";
					
		/* Was our form submitted? */			
		if($ins->request_method == "post" && isset($ins->input['submit']))
		{
			global $cache;
			$username = $ins->input['username'];
			$password = $ins->input['password'];
			$database = $ins->input['database'];
			
			require_once INS_ROOT."/core/classes/database.class.php";
			$db = new Database($username, $password, $database);
			
			/* Success */
			if(is_object($db))
			{
				defined("PHP_EOL") or define("PHP_EOL", "\n");
				$n = PHP_EOL;
				$data = "<?php{$n}{$n}";
				$data .= "/**{$n}";
				$data .= " *  Database Configuration{$n}";
				$data .= "*/{$n}";
				
				$data .= '$config[\'database\'][\'name\'] = "'.$database.'";';
				$data .= $n;
				$data .= '$config[\'database\'][\'username\'] = "'.$username.'";';
				$data .= $n;
				$data .= '$config[\'database\'][\'password\'] = "'.$password.'";';
				$data .= $n;
				
				$data .= "/**{$n}";
				$data .= "* Data cache method to use{$n}";
				$data .= "* Default: {%DEFAULT%}{$n}";
				$data .= "*{$n}";
				$data .= "*  If you wish to use the file system (cache/ directory), MemCache, xcache, or eAccelerator{$n}";
				$data .= "*  you can change the value below to 'files', 'memcache', 'xcache' or 'eaccelerator'.{$n}";
				$data .= "*/{$n}";
				$data .= '$config[\'cache\'][\'method\'] = \'db\';';
				$data .= $n;
				$data .= '$config[\'cache\'][\'identifier\'] = \'\';';
				$data .= $n;
				
				$data .= "/**{$n}"; 
				$data .= " * If you are using Cache Layers other than DB, then:{$n}"; 
				$data .= " *{$n}";
				$data .= " * @val TRUE	If a fetch from External cache layer has failed, the normal Database Cache method will be used to fetch data{$n}";  		
				$data .= " * @val	FALSE	If a fetch from External cache layer has failed, no other attempt will be made to fetch data from database{$n}";
				$data .= "*/{$n}";
				$data .= '$config[\'cache\'][\'use_database_on_fail\'] = TRUE; ';
				$data .= $n;
				
				$data .= "/**{$n}";
				$data .= " * MemCache Configuration{$n}";
				$data .= " * Set the memcache Server/Port etc here{$n}";
				$data .= " * This config is not required if you use some other Cache layer{$n}";
				$data .= " * {$n}";
				$data .= " * Default is set to Server:Port = localhost:11211{$n}";
				$data .= " * Set the debug option to true if you wish Debug the Cache layer{$n}";
				$data .= " */{$n}";
				
				$data .= '$config[\'memcache\'][\'servers\'][] = array("';
				$data .= $n;
				$data .= '\'server\' => \'localhost\','; 
				$data .= $n;
				$data .= '\'port\' => 11211';
				$data .= $n;
				$data .= '");';
				$data .= $n;
				
				$data .= '$config[\'memcache\'][\'debug\'] = true;';
				$data .= $n;
				$data .= '$config[\'memcache\'][\'compress\'] = true;';
				$data .= $n;
				
				$data .= "/**{$n}";
				$data .= " * Admin Configuration{$n}";
				$data .= " */{$n}";
				$data .= '$config[\'admin\'][\'admin_dir\'] = "admin";';
				$data .= $n;
				$data .= "?>";
				
				file_put_contents(INS_ROOT."/core/config.php", $data);
				redirect("index.php?action=tables");
			}
			else
			{
				die("Unable to connect to database with the supplied information, please go back & try again.");
			}
			
			$cache->recache_version();
		}
		echo $output;
	}
	
	/** 
	 * Table Creation
	 * 
	 * @params -do- 
	 */ 
	function tables()
	{
		global $lang, $ins, $db;
		
		$output = "<div class=\"sidebar\">
						<ul>
							<li class=\"sidebar_item\">{$lang->installer_intro}</li>
							<li class=\"sidebar_item\">{$lang->installer_license_agreement}</li>
							<li class=\"sidebar_item\">{$lang->installer_requirements}</li>
							<li class=\"sidebar_item\">{$lang->installer_database}</li>
							<li class=\"sidebar_item current\">{$lang->installer_table}</li>
							<li class=\"sidebar_item\">{$lang->installer_rows}</li>
							<li class=\"sidebar_item\">{$lang->installer_templates}</li>
							<li class=\"sidebar_item\">{$lang->installer_config}</li>
							<li class=\"sidebar_item\">{$lang->installer_admin}</li>
							<li class=\"sidebar_item\">{$lang->installer_finalize}</li>	
						</ul>	
					</div>		
				 ";
		$output .= "<div class=\"content\">
					{$lang->installer_creating_tables_start}
						<div class=\"box\">
							<pre>";
		require_once INS_ROOT."/installer/inc/tables.php";
		foreach($tables AS $table)
		{
			$db->smart_query($table);
		}
		foreach($tablenames AS $tablename)
		{
			$db->smart_query("TRUNCATE `{$tablename}`");
			$output .= "Creating Table: <u><b>{$tablename}</b></u> <span style=\"float:right\">[Done]</span><br />";
		}
		$buttons = '<a class="button" href="index.php?action=data">Next &raquo;</a>';
		$output .= "</pre></div>{$lang->installer_creating_tables_end}</div>
		<div class=\"button_container\">
							{$buttons}
					</div>	";		

		echo $output;
	}
	
	/** 
	 * Data Insertion
	 * 
	 * @params -do- 
	 */ 
	function data()
	{
		global $lang, $ins, $db;
		
		$output = "<div class=\"sidebar\">
						<ul>
							<li class=\"sidebar_item\">{$lang->installer_intro}</li>
							<li class=\"sidebar_item\">{$lang->installer_license_agreement}</li>
							<li class=\"sidebar_item\">{$lang->installer_requirements}</li>
							<li class=\"sidebar_item\">{$lang->installer_database}</li>
							<li class=\"sidebar_item\">{$lang->installer_table}</li>
							<li class=\"sidebar_item current\">{$lang->installer_rows}</li>
							<li class=\"sidebar_item\">{$lang->installer_templates}</li>
							<li class=\"sidebar_item\">{$lang->installer_config}</li>
							<li class=\"sidebar_item\">{$lang->installer_admin}</li>
							<li class=\"sidebar_item\">{$lang->installer_finalize}</li>	
						</ul>	
					</div>		
				 ";
		$output .= "<div class=\"content\">
					{$lang->installer_insert_start}
						<div class=\"box\">
							<pre>";
		require_once INS_ROOT."/installer/inc/rows.php";
		
		foreach($rows AS $row)
		{
			$db->smart_query($row);
			if($db->num_rows() != 1)
				$status = $lang->installer_fail;
			else
				$status = $lang->installer_success;
			$lang->installer_data_insertion_output = $lang->parse($lang->installer_data_insertion_output, $db->num_rows(), $status);	
			$output .= $lang->installer_data_insertion_output;
		}
		
		$buttons = '<a class="button" href="index.php?action=templates">Next &raquo;</a>';
		$output .= "</pre></div>{$lang->installer_insert_end}</div>
		<div class=\"button_container\">
							{$buttons}
		</div>	";		

		echo $output;
	}
	
	/** 
	 * Theme/Templates Insertion
	 * 
	 * @params -do- 
	 */ 
	function templates()
	{
		global $lang, $ins, $db;
		
		$output = "<div class=\"sidebar\">
						<ul>
							<li class=\"sidebar_item\">{$lang->installer_intro}</li>
							<li class=\"sidebar_item\">{$lang->installer_license_agreement}</li>
							<li class=\"sidebar_item\">{$lang->installer_requirements}</li>
							<li class=\"sidebar_item\">{$lang->installer_database}</li>
							<li class=\"sidebar_item\">{$lang->installer_table}</li>
							<li class=\"sidebar_item\">{$lang->installer_rows}</li>
							<li class=\"sidebar_item current\">{$lang->installer_templates}</li>
							<li class=\"sidebar_item\">{$lang->installer_config}</li>
							<li class=\"sidebar_item\">{$lang->installer_admin}</li>
							<li class=\"sidebar_item\">{$lang->installer_finalize}</li>	
						</ul>	
					</div>		
				 ";
		$output .= "<div class=\"content\">
					{$lang->installer_template_start}
						<div class=\"box\">
							<pre>";
		
		$content = file_get_contents(INS_ROOT.'/installer/inc/templates.sql');
		$rows = explode(';', $content);
		foreach($rows AS $row)
		{
			$db->smart_query($row);
			if($db->num_rows() != 1)
				$status = $lang->installer_fail;
			else
				$status = $lang->installer_success;
			$lang->installer_data_insertion_output = $lang->parse($lang->installer_data_insertion_output, $db->num_rows(), $status);	
			$output .= $lang->installer_data_insertion_output;
		}
		
		$buttons = '<a class="button" href="index.php?action=config">Next &raquo;</a>';
		$output .= "</pre></div>{$lang->installer_template_end}</div>
		<div class=\"button_container\">
							{$buttons}
		</div>	";		

		echo $output;
	}
	
	/** 
	 * Website Configuration
	 * 
	 * @return 		void 
	 */ 
	function config()
	{
		global $lang, $ins, $db;
		
		$output = "<div class=\"sidebar\">
						<ul>
							<li class=\"sidebar_item\">{$lang->installer_intro}</li>
							<li class=\"sidebar_item\">{$lang->installer_license_agreement}</li>
							<li class=\"sidebar_item\">{$lang->installer_requirements}</li>
							<li class=\"sidebar_item\">{$lang->installer_database}</li>
							<li class=\"sidebar_item\">{$lang->installer_table}</li>
							<li class=\"sidebar_item\">{$lang->installer_rows}</li>
							<li class=\"sidebar_item\">{$lang->installer_templates}</li>
							<li class=\"sidebar_item current\">{$lang->installer_config}</li>
							<li class=\"sidebar_item\">{$lang->installer_admin}</li>
							<li class=\"sidebar_item\">{$lang->installer_finalize}</li>	
						</ul>	
					</div>		
				 ";	 
							
	    if($ins->request_method == "post" && isset($ins->input['submit']))
		{
			$output .= "<div class=\"content\">
					{$lang->installer_config_start}
						<div class=\"box\">
							<pre>";
			$db->smart_query("UPDATE `settings` SET `value` = '{$ins->input['websitename']}' WHERE `group`='site' AND `name`='name'");
			$db->smart_query("UPDATE `settings` SET `value` = '{$ins->input['websiteurl']}' WHERE `group`='site' AND `name`='url'");
			$db->smart_query("UPDATE `settings` SET `value` = '' WHERE `group`='email' AND `name`='smtp_username'");
			$db->smart_query("UPDATE `settings` SET `value` = '' WHERE `group`='email' AND `name`='smtp_password'");
		
			$options = array(
					 'rows' => 3,
					);
		   
			$rows = $db->smart_query("SELECT * FROM settings", $options);
			$data = "<?php\n";
			foreach($rows AS $row)
			{ 
				if(is_int($row['value']))
				{
				  $value = $row['value'];
				} 
				else
				{
				  $value = '"'.$row['value'].'"';
				}
				$data .= '$settings[\''.$row['group'].'\'][\''.$row['name'].'\'] = '.$value.';';
				$data .= "\n";
				$lang->_installer_config_output = $lang->parse($lang->installer_config_output, $row['name'], $row['group']);
				$output .= $lang->_installer_config_output;
			}
			$data .= "?>";
			file_put_contents(INS_ROOT."/core/settings.php", $data);
			
			$buttons = '<a class="button" href="index.php?action=admin">Next &raquo;</a>';
			$output .= "</pre></div>{$lang->installer_config_end}</div>
			<div class=\"button_container\">
								{$buttons}
			</div>	";		
		}
		else
		{
			$output .= "<div class=\"content\">
						<form class=\"form\" name=\"database\" action=\"index.php?action=config\" method=\"post\">
							<div class=\"row_header\">{$lang->installer_website_config}</div>
							<div class=\"row_element\">
								<label>Website Name</label><input type=\"text\" name=\"websitename\" required=\"\" />
							</div>
							<div class=\"row_element\">		
								<label>Website Url</label><input type=\"text\" name=\"websiteurl\" required=\"\" />
							</div>
							<div class=\"row_element\">	
								<input type=\"submit\" name=\"submit\" class=\"button\" value=\"Next &raquo;\"/>
							</div>			
						</form>
					</div>
					";
		}
		echo $output;
	}
	
	/** 
	 * Administrator
	 * 
	 * @params -do- 
	 */ 
	function admin()
	{
		global $lang, $ins, $db;
		
		$output = "<div class=\"sidebar\">
						<ul>
							<li class=\"sidebar_item\">{$lang->installer_intro}</li>
							<li class=\"sidebar_item\">{$lang->installer_license_agreement}</li>
							<li class=\"sidebar_item\">{$lang->installer_requirements}</li>
							<li class=\"sidebar_item\">{$lang->installer_database}</li>
							<li class=\"sidebar_item\">{$lang->installer_table}</li>
							<li class=\"sidebar_item\">{$lang->installer_rows}</li>
							<li class=\"sidebar_item\">{$lang->installer_templates}</li>
							<li class=\"sidebar_item\">{$lang->installer_config}</li>
							<li class=\"sidebar_item current\">{$lang->installer_admin}</li>
							<li class=\"sidebar_item\">{$lang->installer_finalize}</li>	
						</ul>	
					</div>		
				 ";
		$output .= "<div class=\"content\">
						<form class=\"form\" name=\"database\" action=\"index.php?action=admin\" method=\"post\">
							<div class=\"row_header\">{$lang->installer_admin_config}</div>

							<div class=\"row_element\">		
								<label>Email</label><input type=\"text\" name=\"email\" required=\"\" />
							</div>
							<div class=\"row_element\">
								<label>Username</label><input type=\"text\" name=\"username\" required=\"\" />
							</div>
							<div class=\"row_element\">
								<label>Password</label><input type=\"text\" name=\"password\" />
							</div>
							<div class=\"row_element\">	
								<input type=\"submit\" name=\"submit\" class=\"button\" value=\"Next &raquo;\"/>
							</div>			
						</form>
					</div>
					";
					
		/* Was our form submitted? */			
		if($ins->request_method == "post" && isset($ins->input['submit']))
		{
			$username = $ins->input['username'];
			$password = md5(sha1(sha1(sha1(md5($ins->input['password'])))));
			$email = $ins->input['email'];
			$ip = $_SERVER['REMOTE_ADDR'];
			$date = date("Y-m-d H:i:s");
			$db->smart_query("INSERT INTO `users`(`uid`, `username`, `password`, `email`, `activation_state`, `activation_code`, `regip`, `joindate`, `group`) VALUES('1', '{$username}', '{$password}', '{$email}', '1', 'admin_activated', '{$ip}', '{$date}', '1')");
			
			redirect("index.php?action=finish");
		}
		echo $output;
	}
	
	/** 
	 * License Step
	 * 
	 * @params -do- 
	 */ 
	function done()
	{
		global $lang, $ins;
		$output = "<div class=\"sidebar\">
									<ul>
										<li class=\"sidebar_item\">{$lang->installer_intro}</li>
										<li class=\"sidebar_item\">{$lang->installer_license_agreement}</li>
										<li class=\"sidebar_item\">{$lang->installer_requirements}</li>
										<li class=\"sidebar_item\">{$lang->installer_database}</li>
										<li class=\"sidebar_item\">{$lang->installer_table}</li>
										<li class=\"sidebar_item\">{$lang->installer_rows}</li>
										<li class=\"sidebar_item\">{$lang->installer_templates}</li>
										<li class=\"sidebar_item\">{$lang->installer_config}</li>
										<li class=\"sidebar_item\">{$lang->installer_admin}</li>
										<li class=\"sidebar_item current\">{$lang->installer_finalize}</li>
									</ul>	
					</div>				
					";
		$written = 0;
		if(is_writable('./'))
		{
			$lock = @fopen(INS_ROOT.'/core/installer_lock.php', 'w');
			$written = @fwrite($lock, '1');
			@fclose($lock);
			
			if($written)
			{
				$lang->installer_finish_text = $lang->parse($lang->installer_finish_text, $lang->installer_apptitle, $lang->installer_lock_success);
			}
		}
		
		if(!$written)
		{
			$lang->installer_finish_text = $lang->parse($lang->installer_finish_text, $lang->installer_apptitle, $lang->installer_lock_failed);
		}	
					
		$output .= "<div class=\"content\">
						{$lang->installer_finish_text}
					</div>	
					";
		unset($ins->cookies['step']);			
		echo $output;
	}
}
?>