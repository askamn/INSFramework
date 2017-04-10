<?php
/** 
 * <pre>
 * 	Infusion Network Services
 * 	IN.CMS v2.0.0
 * 	Global file to do all the basic work & include generalized templates like header, footer, etc.
 * 	Last Updated: $Date: Monday 12 June 2014 4:00 $
 * </pre>
 *
 * @author 		$Author: AmNX $
 * @copyright	(c) 2014 Infusion Network Services
 * @license		{%LICENSE%}
 * @package		INS.CMS
 * @link		
 * @since		Monday 16th April 2014 13:00
 * @version		$100 $		
 */

/* Run Start Hooks */ 
$addons->runHooks('global_start');

/* Section.Start: CSS Files */	
$defaulttheme = $templates->getthemenamebygid($ins->settings['theme']['tid']);

require_once INS_ROOT."/core/classes/css.class.php";
$css = new CSS;
$getfiles = $css->getCssFiles($ins->settings['theme']['tid']);

foreach($getfiles AS $file)
{
	$stylesheets .= "<link media=\"all\" rel=\"stylesheet\" type=\"text/css\" ";
	$stylesheets .= "href=\"{$ins->settings['site']['url']}/theme/{$defaulttheme}/css/{$file}\">";
}	
/* Section.End: CSS Files */	

/* Section.Start: FontAwesome Support */	
if($ins->settings['theme']['fontawesome'] == 1)
	$fontawesome = "<link href=\"//netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.css\" rel=\"stylesheet\">";
else
	$fontawesome = "";
/* Section.End: FontAwesome Support */	

/* Section.Start: The basic Templates */	
eval("\$headerinclude = \"".$templates->get("headerinclude")."\";");
eval("\$header = \"".$templates->get("header")."\";");
eval("\$footer = \"".$templates->get("footer")."\";"); 
/* Section.End: The basic Templates */	

/* Section.Start: User Banned */	
if($session->get('key'))
{
	if($user->banned())
	{
		error($lang->account_banned);
	}
} 
/* Section.End: User Banner */	

/* Run End Hooks */ 
$addons->runHooks('global_end');
?>