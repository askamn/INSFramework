<?php
/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.5.0
 * Database Backups Section
 * Last Updated: $Date: 2014-10-28 5:35 (Tue, 26 Oct 2014) $
 * </pre>
 * 
 * @author      $Author: AskAmn$
 * @package     IN.CMS
 * @copyright   (c) 2014 - SVN_YYYY Infusion Network Solutions
 * @license     http://www.infusionnetwork/licenses/license.php?view=main&version=2014  Revision License 0.1.1
 * @since       0.5.0
 * @version     SVN_VERSION_NUMBER
 */

class system_databasebackups Extends \INS\Admin
{
	/**
	 * The complete link to this module
	 *
	 * @var		string	
	 */
	public $link;
	
	/**
	 * Constructor
	 *s
	 * @return 		void
	 */
	public function __construct()
	{
		$this->title = \INS\Language::i()->strings['admin_modules_system_server_databasebackup'];
		$this->link = \INS\Http::i()->link();
		\INS\Template::i()->addToNavigation( \INS\Language::i()->strings['admin_modules_system_server_databasebackup'], $this->link );
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
			case 'backupAll': 
				$this->_backup();
				break;
			default:
				$this->_default();
				break;
		}
    } 

    /**
	 * Main Entry Point
	 *
	 * @return		void
	 */
	public function _default()
	{	
		$dbtables = \INS\Db::i()->db->query( 'SHOW TABLES' )->fetchAll();

		foreach( $dbtables AS $table )
		{
			$row['name'] = $row['value'] = $table[0];
			eval( "\$tables .= \"" . \INS\Template::i()->getAcp( "server_databasebackup_row" ) . "\";" );
		}

		eval("\$this->html = \"" . \INS\Template::i()->getAcp("server_databasebackup") . "\";");

		\INS\Template::i()->output( $this->html, $this->title );
    } 

    /**
	 * Sends the backed up data to the browser
	 *
	 * @return		void
	 */
    public function _backup()
    {
    	if( \INS\Http::i()->request_method == 'post' )
    	{
    		if( empty( \INS\Http::i()->tables ) )
    		{
    			\INS\Template::i()->addNotification( \INS\Language::i()->strings['admin_modules_system_server_databasebackup_notablesselected'], 'error' );
    			$this->_default();
    			exit;
    		}
    		else
    		{
    			$output = $this->getBackUpData( implode( ',', \INS\Http::i()->tables ) );
    		}
    	}

    	$output = ( mb_strlen( $output ) ) ? $output : $this->getBackUpData();

    	try 
    	{
    		if( !is_dir( INS_SYSTEM_DIR . DIRECTORY_SEPARATOR . 'BackEnd' . DIRECTORY_SEPARATOR . 'Backups' ) )
    		{
				mkdir( INS_SYSTEM_DIR . DIRECTORY_SEPARATOR . 'BackEnd' . DIRECTORY_SEPARATOR . 'Backups' );
    		}

    		file_put_contents( INS_SYSTEM_DIR . str_replace( '/', DIRECTORY_SEPARATOR, '/BackEnd/Backups/backup_' ) . time() . '_' . date( 'Y_m_d' ) . '.sql', $output );
    	}
    	catch( \Exception $e )
    	{
    		\INS\Errorhandler::logErrorToFile( 'Failed to create a local backup!' );
    	}

    	header( 'Content-Type: application/x-download' );
		//header( 'Content-Encoding: gzip' );
		header( 'Content-Length: '.mb_strlen( $output ) );
		header( 'Content-Disposition: attachment; filename="database.sql"' );
		header( 'Cache-Control: no-cache, no-store, max-age=0, must-revalidate' );
		header( 'Pragma: no-cache' );

		die( $output );
    }

    /**
	 * Gets the data for backup
	 *
	 * @return		void
	 */
  	public function getBackUpData( $tables = NULL )
  	{
  		/* Create Back up of All Tables */
  		if( $tables === NULL )
  		{
  			$dbtables = \INS\Db::i()->db->query( 'SHOW TABLES' )->fetchAll();

  			foreach( $dbtables AS $table )
  			{
  				$tables[] = $table[0];
  			}
  		}
  		else
  		{
  			$tables = explode( ',', $tables );
  		}

  		$insert = $create = $comma = '';

  		foreach( $tables AS $table )
		{
			/*try
			{
				//\INS\Db::i()->db->query( sprintf( 'ALTER TABLE `%s` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;', $table ) );
				\INS\Db::i()->db->query( sprintf( 'ALTER TABLE `%s` ENGINE=INNODB', $table ) );
				\INS\Db::i()->db->query( sprintf( 'REPAIR TABLE `%s`', $table ) );
				\INS\Db::i()->db->query( sprintf( 'OPTIMIZE TABLE `%s`', $table ) );
			}
			catch( \Exception $e )
			{
				die( $e->getMessage() );
			}*/
			$create .=  str_replace( 'CREATE TABLE', 'CREATE TABLE IF NOT EXISTS', \INS\Db::i()->db->query( sprintf( 'SHOW CREATE TABLE %s', $table ) )->fetch()[1] ) . ";\n\n";

			$rows = \INS\Db::i()->fetchAll( '*', preg_replace( sprintf( '/^%s/', \INS\Db::i()->prefix ), '', $table ) );

			if( empty( $rows ) )
			{
				continue;
			}

			$insert .= sprintf( 'INSERT INTO %s(%s) VALUES ', $table, '`' . implode( '`, `', array_keys( $rows[0] ) ) . '`' );

			$comma = '';
			$i = 1;		
			$rowCount = count( $rows );	
			foreach( $rows AS $row )
			{
				array_walk( $row, function( &$v, $k ){
					$v = '\'' . ( ( \INS\Http::i()->type == 'sql' ) ? str_replace( '\'', '\'\'', $v ) : addcslashes( $v, '\'' ) ) . '\'';
				} );

				$insert .= sprintf( "%s(%s)" . ( $i++ == $rowCount ? '' : PHP_EOL ), $comma, implode( ', ', $row ) );
				$comma = ', ';
			}

			$insert .= ";\n\n";
		}

		return $create . $insert;
  	}
}
?>