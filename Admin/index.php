<?php

/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.5.0
 * Admininstrator Panel Entry Point
 * Last Updated: $Date: 2014-10-28 5:35 (Tue, 26 Oct 2014) $
 * </pre>
 * 
 * @author      $Author: AskAmn$
 * @package     IN.CMS
 * @copyright   (c) 2014 Infusion Network Solutions
 * @license     http://www.infusionnetwork/licenses/license.php?view=main&version=2014  Revision License 0.1.1
 * @since       0.1.0
 * @version     Revision: 0510
 */

$directory = dirname( dirname( __FILE__ ) ); 
define( "THIS_SCRIPT", "index.php" );
define( "IN_ACP", TRUE );

require_once $directory . "/init.php";

//$lang->admin_footer_memory_usage = $lang->parse( $lang->admin_footer_memory_usage, INS_MEMORY_USAGE_MB, INS_MEMORY_USAGE_KB );

//\INS\Template::i()->replaceAll( '{$footer}', "{%FOOTER%}" , 1 ); 
//$templates->replaceAll( '{$headerinclude}', '{%HEADERINCLUDE%}', 1 );  

/*$rows = \INS\Db::i()->buildQuery( 'select' )
						->columns( 'a.aid, h.aid, h.hid, a.enabled, h.hook_load_position, h.hook_key, h.file, h.apptype, a.dir' )
						->table( 'inshooks h' )
						->leftJoin( 'applications a', 'a.aid=h.aid' )
						->where( 'a.enabled = \'1\'' )
						->complete();*/

						//die(print_r($rows));
//$addons = \INS\Cache::i()->read('appcache', true); 
//die( print_r( $addons ) );
//\INS\Cache::i()->recacheApps();
\INS\Admin::i()->Execute();
?>