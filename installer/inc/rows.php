<?php
$rows[] = 'INSERT INTO `settings` VALUES(1, \'site\', \'name\', \'Website Name\', \'The name of the website.\', \'Dynamic\', 1, \'text\', 0)';
$rows[] = 'INSERT INTO `settings` VALUES(7, \'site\', \'url\', \'Website URL\', \'URL of your website.\', \'http://127.0.0.1/framework\', 1, \'text\', 0)';
$rows[] = 'INSERT INTO `settings` VALUES(8, \'theme\', \'tid\', \'Theme GID\', \'Input the gid of your theme here. The templates will be pulled using this gid.\', \'4\', 2, \'text\', 0)';
$rows[] = 'INSERT INTO `settings` VALUES(9, \'admin\', \'editor_theme\', \'Editor Theme\', \'Here you can put the name of the Editor\'\'s theme you want to use.\', \'ambiance\', 3, \'text\', 0)';
$rows[] = 'INSERT INTO `settings` VALUES(10, \'email\', \'smtp_enabled\', \'Use SMTP\', \'Do you wish to use External SMTP? If you are unsure of this setting please leave it as is. *Below settings will be employed only if you set SMTP enabled to YES.\', \'0\', 4, \'yesno\', 0)';
$rows[] = 'INSERT INTO `settings` VALUES(11, \'email\', \'smtp_username\', \'SMTP Username\', \'Your SMTP username. <i>(This field is optional if you have external SMTP disabled)</i>\', \'\', 4, \'text\', 10)';
$rows[] = 'INSERT INTO `settings` VALUES(12, \'email\', \'smtp_password\', \'SMTP Password\', \'Your SMTP Password. <i>(This field is optional if you have external SMTP disabled)</i>\', \'\', 4, \'text\', 10)';
$rows[] = 'INSERT INTO `settings` VALUES(13, \'email\', \'main\', \'From (Header)*\', \'This is the setting for your mail Header\'\'s FROM field. It is a required field.\', \'\', 4, \'text\', 0)';
$rows[] = 'INSERT INTO `settings` VALUES(14, \'email\', \'name\', \'CC (Header)\', \'Carbon Copy header.\', \'Dynamic\', 4, \'text\', 0)';
$rows[] = 'INSERT INTO `settings` VALUES(15, \'email\', \'bcc\', \'BCC (Header)\', \'Blind Carbon Copy Header.\', \'\', 4, \'text\', 0)';
$rows[] = 'INSERT INTO `settings` VALUES(17, \'admin\', \'editor_height\', \'Editor Height\', \'This will be set as the default height of the editor. (in pixels)\', \'486\', 3, \'text\', 0)';
$rows[] = 'INSERT INTO `settings` VALUES(2, \'site\', \'logo\', \'Logo URL\', \'Type here the URL of your logo. Then you can use {$settings[\'\'site\'\'][\'\'logo\'\']} to access it.\', \'\', 1, \'text\', 0)';
$rows[] = 'INSERT INTO `settings` VALUES(3, \'site\', \'logo_alt\', \'Logo AlternateText\', \'Alternate text to display in case the logo failed to load.\', \'Fr33dom\', 1, \'text\', 0)';
$rows[] = 'INSERT INTO `settings` VALUES(18, \'users\', \'instant_activation\', \'Instant Activation\', \'Users don\'\'t need to confirm their emails if you set this option to Yes.\', \'1\', 5, \'yesno\', 0)';
$rows[] = 'INSERT INTO `settings` VALUES(20, \'users\', \'login_redirect\', \'Login Redirection\', \'If you wish to redirect your users after login, just fill the URL in this box. Otherwise, leave it blank.\', \'\', 5, \'text\', 0)';
$rows[] = 'INSERT INTO `settings` VALUES(21, \'users\', \'force_login\', \'Force Login/Registration\', \'If you set this option to "yes" your users will have to Login/Register on your site before entering your site.\', \'0\', 5, \'yesno\', 0)';
$rows[] = 'INSERT INTO `settings` VALUES(22, \'admin\', \'theme\', \'Default Theme\', \'Default theme for admin control panel. (Only GID supported in your version of Pre-Processor)\', \'1\', 3, \'text\', 0)';
$rows[] = 'INSERT INTO `settings` VALUES(23, \'theme\', \'autoload_fonts\', \'Google Fonts\', \'Put the name of Google fonts you wanna embed in your theme. They will be anti-aliased and auto-loaded whenever the <head> section is loaded. <i>Separate the names by comma</i>.\', "\'Raleway\', \'Lato:300,400,700\', \'Sanchez:400,400italic\'", 2, \'text\', 0)';
$rows[] = 'INSERT INTO `settings` VALUES(24, \'theme\', \'fontawesome\', \'Use FontAwesome\', \'This settings enables you to use fontawesome in your theme. You need not to do anything else. Just add in the icon classes wherever you wish to see an icon.\', \'1\', 2, \'yesno\', 0)';
$rows[] = 'INSERT INTO `settings` VALUES(25, \'datetime\', \'date_format\', \'Date Format\', \'The date format to be used on the website. If you are unsure about this setting, leave it as is. All PHP Valid date formats are accepted.\', \'Y-m-d\', 6, \'text\', 0)';
$rows[] = 'INSERT INTO `settings` VALUES(26, \'datetime\', \'time_format\', \'Time Format\', \'The time format to be used on the website. If you are unsure about this setting, leave it as is. All PHP Valid time formats are accepted.\', \'H:i:s\', 6, \'text\', -1)';

$rows[] = 'INSERT INTO `settings_group` VALUES(\'site\', \'Website Settings\', \'The configuration of the site resides here.\', 1)';
$rows[] = 'INSERT INTO `settings_group` VALUES(\'theme\', \'Theme Settings\', \'Here you can change your current themes.\', 2)';
$rows[] = 'INSERT INTO `settings_group` VALUES(\'admin\', \'Admin Panel Settings\', \'Various settings for admin panel.\', 3)';
$rows[] = 'INSERT INTO `settings_group` VALUES(\'email\', \'Email Settings\', \'Settings for email. Also includes settings for Email Headers.\', 4)';
$rows[] = 'INSERT INTO `settings_group` VALUES(\'users\', \'User Registration & Profile Options\', \'Various options that help you manage your users easily.\', 5)';

$rows[] = 'INSERT INTO `sidebar` VALUES(1, \'\', \'home\', \'Dashboard\', \'dashboard\', \'\')';
$rows[] = 'INSERT INTO `sidebar` VALUES(2, \'app=system&module=config&section=settings\', \'cogs\', \'Configuration\', \'system\', \'config\')';
$rows[] = 'INSERT INTO `sidebar` VALUES(3, \'app=members&module=overview&section=view\', \'user\', \'Users\', \'members\', \'overview\')';
$rows[] = 'INSERT INTO `sidebar` VALUES(4, \'app=lookfeel&module=templates\', \'code\', \'Templates\', \'lookfeel\', \'templates\')';
$rows[] = 'INSERT INTO `sidebar` VALUES(5, \'app=tools&module=server&section=serverinfo\', \'signal\', \'Server Information\', \'tools\', \'server\')';
$rows[] = 'INSERT INTO `sidebar` VALUES(6, \'app=modules&module=film\', \'film\', \'Movies\', \'modules\', \'film\')';
$rows[] = 'INSERT INTO `sidebar` VALUES(7, \'app=system&module=applications&section=applications\', \'bars\', \'Applications\', \'system\', \'applications\')';
$rows[] = 'INSERT INTO `sidebar` VALUES(8, \'app=lookfeel&module=css\', \'font\', \'CSS\', \'lookfeel\', \'css\')';
$rows[] = 'INSERT INTO `sidebar` VALUES(9, \'app=members&module=create&section=overview\', \'pencil\', \'Create Member\', \'members\', \'create\')';
$rows[] = 'INSERT INTO `sidebar` VALUES(11, \'app=tools&module=php&section=phpinfo\', \'bars\', \'PHP Information\', \'tools\', \'php\')';

$rows[] = 'INSERT INTO `sidebar_sublinks` VALUES(1, 3, \'view&view=list\', \'View Users\', \'members\')';
$rows[] = 'INSERT INTO `sidebar_sublinks` VALUES(3, 8, \'css\', \'CSS\', \'lookfeel\')';
$rows[] = 'INSERT INTO `sidebar_sublinks` VALUES(4, 4, \'templates\', \'Templates\', \'lookfeel\')';
$rows[] = 'INSERT INTO `sidebar_sublinks` VALUES(7, 6, \'view\', \'View Movies\', \'modules\')';
$rows[] = 'INSERT INTO `sidebar_sublinks` VALUES(8, 6, \'create\', \'Create New Movie\', \'modules\')';

?>