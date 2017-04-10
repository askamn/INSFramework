<?php
$l['installer_apptitle'] = "Pre Processor";
$l['installer_title'] = "Installing Pre-Processor";
$l['installer_footer_text'] = "Pre Processor Client Suite";

$l['installer_intro'] = "<i class=\"fa fa-sun-o\">&nbsp;</i> Welcome";
$l['installer_license_agreement'] = "<i class=\"fa fa-file-text\">&nbsp;</i> License Agreement";
$l['installer_requirements'] = "<i class=\"fa fa-sort-alpha-asc\">&nbsp;</i> Requirements Check";
$l['installer_database'] = "<i class=\"fa fa-linux\">&nbsp;</i> Database Information";
$l['installer_table'] = "<i class=\"fa fa-table\">&nbsp;</i> Table Creation";
$l['installer_rows'] = "<i class=\"fa fa-arrows-alt\">&nbsp;</i> Data Insertion";
$l['installer_templates'] = "<i class=\"fa fa-star\">&nbsp;</i> Theme Setup";
$l['installer_config'] = "<i class=\"fa fa-gears\">&nbsp;</i> Website Configuration";
$l['installer_admin'] = "<i class=\"fa fa-user\">&nbsp;</i> Admin Account Setup";
$l['installer_finalize'] = "<i class=\"fa fa-thumbs-up\">&nbsp;</i> Finish";

$l['installer_lock_success'] = "<br />We have locked the installer to prevent unauthorised access to the installer.";
$l['installer_lock_failed'] = "<br />There was an error locking your installer. Please create a file 'lock' in the installer directory to lock the installer.";

$l['installer_admin_config'] = "Setting Up An Administrator Account";
$l['installer_website_config'] = "Website Configuration";
$l['installer_db_config'] = "Database Configuration";
$l['installer_success'] = "[Success]";
$l['installer_fail'] = "[Failure]";

$l['installer_config_output'] = "Setting: <u><b>{1}</b></u> created in group <u><b>{2}</b></u>. <span style=\"float:right\">[Success]</span><br />";
$l['installer_data_insertion_output'] = "Database Result: <u><b>{1} row inserted.</b></u> <span style=\"float:right\">{2}</span><br />";

$l['installer_title_license'] = "License";
$l['installer_title_requirements'] = "Requirements";
$l['installer_title_database'] = "Database Details";
$l['installer_title_tables'] = "Creating Tables";
$l['installer_title_rows'] = "Creating Rows";
$l['installer_title_templates'] = "Creating Templates";
$l['installer_title_settings'] = "Creating Settings";
$l['installer_title_config'] = "Setting Up The Website URL And Name";
$l['installer_title_admin'] = "Creating Administrator Account";
$l['installer_title_done'] = "Installation Completed";

$l['installer_requirements_check_passed'] = "Your system meets all the requirements to run Pre Processor.";
$l['installer_requirements_check_failed'] = "Please make sure you read the installation instructions properly & try again.";

$l['installer_creating_tables_start'] = "<h1>Creating Tables</h1>";
$l['installer_creating_tables_end'] = "<span class=\"success\">Table creation complete.</span>";
$l['installer_insert_start'] = "<h1>Inserting Data into Database</h1>";
$l['installer_insert_end'] = "<span class=\"success\">Data inserted.</span>";
$l['installer_template_start'] = "<h1>Inserting Templates & Themes</h1>";
$l['installer_template_end'] = "<span class=\"success\">Themes & Templates inserted.</span>";
$l['installer_config_start'] = "<h1>Website Settings & Configuration</h1>";
$l['installer_config_end'] = "<span class=\"success\">Settings Creation Successful.</span>";

$l['installer_license_text'] = "<h1>License Agreement</h1>
								A test license.";
$l['installer_finish_text'] = "<h1>Installation Completed</h1>
								Your copy of {1} was successfully installed!
								{2}
								<br /><br />
								You can login to your <a href=\"{$settings['site']['url']}/admin/index.php\">Admin Panel</a> Or Visit your <a href=\"{$settings['site']['url']}/index.php\">Website</a>
								";								
$l['installer_intro_text'] = "<h1>Welcome to {1} Installation</h1>
						This wizard will guide you through the installation and configuration of {1} on your server.
						<br />
						Before proceeding, please make sure that you read the installation instructions. If you find any problem, you may reuest us to install {1} for you.";
?>