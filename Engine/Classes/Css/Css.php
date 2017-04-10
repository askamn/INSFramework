<?php
/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.5.0
 * Handles Stylesheets
 * Last Updated: $Date: 2014-10-28 5:35 (Tue, 26 Oct 2014) $
 * </pre>
 * 
 * @author      $Author: AskAmn$
 * @package     IN.CMS
 * @copyright   (c) 2014 Infusion Network Solutions
 * @license     http://www.infusionnetwork/licenses/license.php?view=main&version=2014  Revision License 0.1.1
 * @since       0.3.0
 * @version     Revision: 0530
 */

namespace INS;

class Css
{	
	/** 
     * Loads the instance of this class
     *
     * @return      resource
     */
    public static function i()
    {
        $arguments = func_get_args();

        if( !empty( $arguments ) )
            return \INS\Core::getInstance( __CLASS__, $arguments);
        else
            return \INS\Core::getInstance( __CLASS__ );
    }

    /**
     * Constructor of class.
     *
     * @return      void
     */
    public function __construct()
    {
        \INS\Core::checkState( __CLASS__ ); 
    }

	/**
	 * Get CSS Files
	 *
	 * @return		array
	 */
	public function get( $gid = NULL )
	{
		if( $gid === NULL )
			$row = \INS\Db::i()->f('`name`', 'themes', "`default` = '1'")->get();
		else
			$row = \INS\Db::i()->f( '`name`', 'themes', "`gid` = ?", '', [ 1 => intval( $gid ) ] )->get();

		$files = \INS\File::i()->readDir( INS_SYSTEM_DIR . "/FrontEnd/Theme/" . $row['name'] . "/css" );

		foreach($files AS $file)
				if(\INS\File::i()->getExtension( $file ) == "css")
					$name[] = $file;

		return $name;
	}
	
	/**
	 * Get Default Theme
	 *
	 * @return		string
	 */
	public function getThemeName( $gid = NULL )
	{
		return ( is_null( $gid ) ) ? \INS\Db::i()->f( '`name`', 'themes', '`default` = 1' )->get( 'name' ) : \INS\Db::i()->f( '`name`', 'themes', "`gid` = ?", '', [ 1 => intval( $gid ) ] )->get( 'name' ) ;
	}
}
?>