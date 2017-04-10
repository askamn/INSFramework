<?php
defined("INS_ROOT") or define("INS_ROOT", dirname(dirname(__FILE__)));
define('IN_INS', 1);
define('IN_INSTALL', 1);

require_once INS_ROOT."/init.php";

if(file_exists(INS_ROOT.'/core/installer_lock.php'))
{
	die("App already installed.");
}

$lang->load('installer');

require_once INS_ROOT."/installer/install.class.php";
$installer = new Installer;

$installer->header();

/* Step 1. Show Introduction */
if(!isset($ins->input['action']) || $ins->input['action'] == "intro")
{
	$installer->intro();
}
/* Step 2. License Agreement */
elseif($ins->input['action'] == "license")
{
	$installer->license();
}
/* Step 3. Requirements Check */
elseif($ins->input['action'] == "requirements")
{
	$installer->requirements();
}
/* Step 4. Database */
elseif($ins->input['action'] == "database")
{
	$installer->database();
}
/* Step 5. Creating tables */
elseif($ins->input['action'] == "tables")
{
	$installer->tables();
}
/* Step 6. Injecting some necessary rows */
elseif($ins->input['action'] == "data")
{
	$installer->data();
}
/* Step 7. Create templates */
elseif($ins->input['action'] == "templates")
{
	$installer->templates();
}
/* Step 8. Website Configuration */
elseif($ins->input['action'] == "config")
{
	$installer->config();
}
/* Step 9. Creating the administrator */
elseif($ins->input['action'] == "admin")
{
	$installer->admin();
}
/* Step 10. Final */
elseif($ins->input['action'] == "finish")
{
	$installer->done();
}

$installer->footer();
?>