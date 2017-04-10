<?php
/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.5.0
 * Database Class
 * Last Updated: $Date: 2014-10-28 5:35 (Tue, 26 Oct 2014) $
 * </pre>
 * 
 * @author      $Author: AskAmn$
 * @package     IN.CMS
 * @copyright   (c) 2014 Infusion Network Solutions
 * @license     http://www.infusionnetwork/licenses/license.php?view=main&version=2014  Revision License 0.1.1
 * @since       0.5.0
 * @version     Revision: 0510
 */

namespace INS;

class Db extends \PDOException 
{
	/**
	 * Internal Table Names
	 *
	 * @var 	array	
	 */
	public $tablenames = array(
			'blogcomments', 
			'admin_sessions', 
			'blogposts', 
			'settings', 
			'settings_group', 
			'shows', 
			'sidebar', 
			'sidebar_sublinks', 
			'themes',
			'users', 
			'templates',
			'templates_admin', 
			'user_requests', 
			'inscache',
			'applications',
		); 

	/**
	 * @var Array 
	 */
	public $queryTypes = [ 
				'DB_SELECT' 	=> 1, 
				'DB_UPDATE' 	=> 2,  
				'DB_INSERT' 	=> 3,
				'DB_DROP'		=> 4,
				'DB_CREATE' 	=> 5,
				'DB_TRUNCATE' 	=> 6,
				'DB_DELETE'		=> 7,
				'DB_OPTIMIZE'   => 8,
				'DB_SHOW'		=> 9,
				'DB_CSHOWTABLE' => 10,
			];

	/**
	 * @var Boolean For build query
	 */
	public $buildQueryNoBoundParameters = TRUE;

	/**
	 * @var Boolean Has the build query method completed its work?
	 */
	public $buildQueryComplete = TRUE;
   
    /**
	 * The SQL server host
	 *
	 * @var 	string	
	 */
	private $hostname = 'localhost';
   
    /**
	 * The database to work on
	 *
	 * @var 	string	
	 */
	private $database  = '';

	/**
	 * Bind ooffset
	 *
	 * @var 	string	
	 */
	public $bindOffset = 1;
   
    /**
	 * Username
	 *
	 * @var 	string	
	 */
	private $username = '';
   
    /**
	 * Password
	 *
	 * @var 	string	
	 */
	private $password = '';

	/**
	 * The driver to be used
	 *
	 * @var 	string	
	 */
	private $dbDriver = 'PDO';

	/**
	 * The driver to be used
	 *
	 * @var 	array 	Un-formatted name => Proper/Formatted Name
	 */
	protected $supportedDbDrivers = [
		'pdo' 	 => 'PDO',
		'mysqli' => 'MySQLi',
		'mssql'  => 'MsSQL'
	];

	/**
	 * Last fetched data
	 *
	 * @var 	array 	
	 */
	protected $lastFetchedData = [];
   
	/**
	 * Array to store errors
	 *
	 * @var 	array
	 */
	protected $errors = [];
   
	/**
	 * The SQL statement
	 *
	 * @var 	string	
	 */
	public $statement;
   
	/**
	 * The PDO Resource holder
	 *
	 * @var 	object	
	 */
    public $db = NULL;
   
	/**
	 * The current query
	 *
	 * @var 	string	
	 */
	public $query;

	/**
	 * Number of queries
	 *
	 * @var 	string	
	 */
	public $totalQueries;

	/**
	 * The Table Prefix
	 *
	 * @var 	string	
	 */
	public $prefix = '';

	/**
	 * [Build Query] Query Type
	 *
	 * @var 	string	
	 */
	public $queryType = '';

	/**
	 * [Build Query] Column names Specified
	 *
	 * @var 	boolean	
	 */
	public $columnNamesNotSpecified = TRUE;
	
	/** 
	 * Loads the instance of this class
	 *
	 * @return 		resource
	 */
	public static function i()
    {
    	$arguments = func_get_args();

    	if( !empty( $arguments ) )
    	{
        	return \INS\Core::getInstance( __CLASS__, $arguments);
    	}
        else
        {
        	return \INS\Core::getInstance( __CLASS__ );
        }
    }

	/**
	 * Constructor of class.
	 *
	 * @param 		array 	$dbArray	An array containing Database details
	 * @return 		void
	 */
	public function __construct( $dbArray )
	{
		\INS\Core::checkState( __CLASS__ ); 
	
    	if( empty( $dbArray ) )
    	{
    		trigger_error(DATABASE_INFO_MISSING, E_USER_WARNING);	
    	}

        $this->username = $dbArray['username'];
		$this->password = $dbArray['password'];
		$this->database = $dbArray['name'];
		$this->dbDriver = $dbArray['driver'];
		$this->host 	= !mb_strlen( $dbArray['hostname'] ) ? 'localhost' : $dbArray['hostname'];
		$this->prefix   = mb_strlen( $dbArray['tablePrefix'] ) ? $dbArray['tablePrefix'] . '_' : '';

		if( !in_array( $this->dbDriver, $this->supportedDbDrivers ) )
		{
			trigger_error(	DATABASE_NOT_SUPPORTED, E_USER_WARNING	);
		}

        if ( !mb_strlen( $this->username ) OR !mb_strlen( $this->database ))
	    { 
			trigger_error(	DATABASE_INFO_MISSING, E_USER_WARNING	);
		}
		
		$options = array(
            \PDO::ATTR_PERSISTENT       => FALSE,
            \PDO::ATTR_ERRMODE          => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_EMULATE_PREPARES => FALSE,
        );
		
		try
		{
			/*  CHARSET  |      NAME     |      COLLATION     */
			/*  utf8mb4  | UTF-8 Unicode | utf8mb4_unicode_ci */
			$this->db = new \PDO("mysql:host={$this->host};dbname={$this->database};charset=utf8mb4", $this->username, $this->password, $options);
		}
		catch( \Exception $e )
		{
			if( $e->code == '2002' )
			{
				die( DATABASE_CONNECTION_FAILURE );
			}
		}
    }

    /**
	 * Checks if connection is broken and attempts to reconnect
	 *
	 * @return	void
	 */
	public function checkConnection()
	{
		/*try 
		{
			$this->db->query('SHOW TABLES');
		}
		catch ( \PDOException $e ) 
		{  
			if( INS_DEV_MODE ) print_r( $e );

			$options = array(
								\PDO::ATTR_PERSISTENT    => FALSE,
								\PDO::ATTR_ERRMODE       => \PDO::ERRMODE_EXCEPTION,
								\PDO::ATTR_EMULATE_PREPARES => FALSE,
			);

			$this->db = new \PDO("mysql:host={$this->host};dbname={$this->database}", $this->username, $this->password, $options);
		}*/
	}

	/**
	 * Binds the supplied parameters to their value
	 *
	 * @param 	array		Array of values to be bound
	 * @return 	void		
	 */
	public function bind( $array )
	{ 	
		/* No binds? */
		if( empty( $array ) OR $array === NULL )
		{
			if( !empty( $this->binds ) )
			{
				$array = $this->binds;
			}
			/* No binds here too? Return */
			else
			{
				return static::i();
			}
		}

		/* A build query has been initiated, prepare the statement */
		if( $this->buildQueryComplete === FALSE )
		{
			/* 
			   We have bound parameters, so mark this false preventing the complete() method to 
			   interfere with the binds
			 */

			$this->buildQueryNoBoundParameters = FALSE;
			$this->statement = $this->db->prepare( $this->query );
		} 

		if( is_array( $array ) )
		{
			/* Do the binds start with 0-indexed position? */
			$offsetFromZero = ( isset( $array[0] ) ) ? TRUE : FALSE;

			foreach( $array AS $param => $value )
			{
				if( is_numeric( $param ) AND $offsetFromZero === TRUE )
				{
					$this->statement->bindValue( $param+1, $value, $this->detectType( $value ) ); 
					continue;
				}

				$this->statement->bindValue( $param, $value, $this->detectType( $value ) ); 
			}
		}

		return static::i();
    } 
	
    /**
	 * Detects the data-type of supplied value
	 *
	 * @param 	string		Value to work on
	 * @return 	string 		Type of value	
	 */
	public function detectType( $value )
	{
		switch( $value ) 
		{
			case is_string( $value ):
				$type = \PDO::PARAM_STR;
				break;
			case is_int( $value ):
				$type = \PDO::PARAM_INT;
				break;
			case is_bool( $value ):
				$type = \PDO::PARAM_BOOL;
				break;
			case is_null( $value ):
				$type = \PDO::PARAM_NULL;
				break;
			default:
				$type = \PDO::PARAM_STR;
		}

		return $type;  
	}

    /**
	 * Prepares a query for execution & then executes it
	 *
	 * @param 	 string		The query to work on
	 * @param 	 string 	Options
	 * @param 	 array		Array of values to be bound
	 * @return 	 array	
	 */
	public function query($query, $options = NULL, $binds = NULL)
    {  
		$this->query = str_replace( array( '<%P%>', '<%PREFIX%>' ), array( $this->prefix, $this->prefix ), $query );  
		$this->statement = $this->db->prepare($this->query);
		
		if( is_array( $binds ) )
		{
			$this->bind( $binds );
		}
		
		$this->execute();
	  
		if($options != NULL)
		{
			if( $options['rows'] == 1 )
			{
				return $this->statement->fetch( \PDO::FETCH_ASSOC);
			}
			else
			{
				return $this->statement->fetchAll( \PDO::FETCH_ASSOC);		 
			}
		}
	}
	
    /**
	 * Replace
	 *
	 * @param 	string 	Table name
	 * @param 	boolean Array of Insertions
	 * @param 	array   Binds
	 * @return 	integer Last insert id
	 */
	public function insert( $table, $array, $binds = NULL )
    {  
    	$table = $this->prefix . $table;
		$comma = "";

		$offsetFromZero = FALSE;
		$initialBind = $this->bindOffset = 1;

		/* User has explicitly supplied binds */
		if( $binds !== NULL )
		{
			if( isset( $binds[0] ) )
			{
				$offsetFromZero = TRUE;
				$initialBind = 0;
			}
		}

		foreach( $array as $field => $value )
		{
			/* The named param is already there */
			if( trim( $value ) == '?' OR mb_strpos( $value, ':' ) === 0 )
			{
				/* Then find its value */
				$this->binds[ $this->bindOffset++ ] = $binds[ $initialBind ]; 
				/* Un-set the user supplied bind and increment the counter */
				unset( $binds[ $initialBind++ ] );
			}
			/* or No? */
			else
			{
				/* Then set the bind's value the actual value */
				$this->binds[ $this->bindOffset++ ] = $value;
				$value = '?';
			}

			/* Check for binds: ? or :named */
			$values .= $comma . $value;
			$comma = ', ';
		}

		$fields = '`' . implode( '`,`', array_keys( $array ) ) . '`';
		$this->query = sprintf( 'INSERT INTO %s( %s ) VALUES ( %s ) ', $table, $fields, $values );

		$this->statement = $this->db->prepare($this->query);
		$this->bind( $this->binds );
		$this->execute();

		return $this->lastInsertId();
	}

	/**
	 * Replace
	 *
	 * @param 	string 	Table name
	 * @param 	boolean Array of Replacements
	 * @param 	array   Binds
	 * @return 	integer Last insert id
	 */
	public function replace( $table, $array, $binds = NULL )
    {  
    	$table = $this->prefix . $table;
		$comma = "";

		$offsetFromZero = FALSE;
		$initialBind = $this->bindOffset = 1;

		/* User has explicitly supplied binds */
		if( $binds !== NULL )
		{
			if( isset( $binds[0] ) )
			{
				$offsetFromZero = TRUE;
				$initialBind = 0;
			}
		}

		foreach( $array as $field => $value )
		{
			/* The named param is already there */
			if( trim( $value ) == '?' OR mb_strpos( $value, ':' ) === 0 )
			{
				/* Then find its value */
				$this->binds[ $this->bindOffset++ ] = $binds[ $initialBind ]; 
				/* Un-set the user supplied bind and increment the counter */
				unset( $binds[ $initialBind++ ] );
			}
			/* or No? */
			else
			{
				/* Then set the bind's value the actual value */
				$this->binds[ $this->bindOffset++ ] = $value;
				$value = '?';
			}

			/* Check for binds: ? or :named */
			$values .= $comma . $value;
			$comma = ', ';
		}

		$fields = '`' . implode( '`,`', array_keys( $array ) ) . '`';
		$this->query = sprintf( 'REPLACE INTO %s( %s ) VALUES ( %s ) ', $table, $fields, $values );

		$this->statement = $this->db->prepare($this->query);
		$this->bind( $this->binds );
		$this->execute();

		return $this->lastInsertId();
	}
	
	/**
	 * Performs DELETION in database
	 *
	 * @param 	string  	The table to work on
	 * @param 	string  	WHERE clause
	 * @param   array|null  Binds
	 * @return 	\INS\Db::i()
	 */
	public function delete( $table, $where, $binds = NULL )
    {  
		$table = $this->prefix . $table;

		$this->query = 'DELETE FROM ' . $table . ' WHERE ' . $this->parseWhereClause( $where );
		$this->statement = $this->db->prepare( $this->query );
		$this->bind( $binds );

		$this->execute();
		return static::i();
	}
	
	/**
	 * Performs UPDATE in database
	 *
	 * @param 	string  The table name to work on
	 * @param 	array   Array of parameters to be updated
	 * @param	string  Where clause
	 * @param	string  Limit clause
	 * @param   array   Array of Binds for PDOStatement::bindValue
	 * @return 	\INS\Db::i()
	 */
	public function update( $table, $array, $where = NULL, $limit = NULL, $binds = NULL )
    {  
		$table = $this->prefix . $table;
		
		$comma = $_where = "";
		$quote = "'";

		$offsetFromZero = FALSE;
		$initialBind = $this->bindOffset = 1;

		if( $binds !== NULL )
		{
			if( isset( $binds[0] ) )
			{
				$offsetFromZero = TRUE;
				$initialBind = 0;
			}
		}

		foreach( $array AS $field => $value )
		{
			/* The named param is already there */
			if( trim( $value ) == '?' OR mb_strpos( $value, ':' ) === 0 )
			{
				/* Then find its value */
				$this->binds[ $this->bindOffset++ ] = $binds[ $initialBind ]; 
				/* Un-set the user supplied bind and increment the counter */
				unset( $binds[ $initialBind++ ] );
			}
			/* or No? */
			else
			{
				/* Then set the bind's value the actual value */
				$this->binds[ $this->bindOffset++ ] = $value;
				$value = '?';
			}

			$this->query .= $comma . '`' . $field . '` = ' . $value;
			$comma = ', ';
		}
		
		/* Still not empty? The where clause must have bound params too */
		if( !empty( $binds ) )
		{
			foreach ( $binds AS $i => $value )
			{
				$this->binds[ $this->bindOffset++ ] = $value;
				unset( $binds[ $i ] );
			}
		}

		$this->query = ( is_array( $where ) OR ( mb_strlen( $where ) AND $where !== NULL ) ) ? $this->query . ' WHERE ' . $this->parseWhereClause( $where ) : $this->query;
		$this->query = ( mb_strlen( $limit ) AND $limit !== NULL )  ? $this->query . ' LIMIT ' . $limit : $this->query;
		$this->query = "UPDATE `{$table}` SET {$this->query}";
		$this->statement = $this->db->prepare($this->query);
		
		$this->bind( $this->binds );

		$this->execute();
		return static::i();
	}
	
	/**
	 * Fetches a row
	 *
	 * @param 	string
	 * @param 	string
	 * @param 	string
	 * @param	string
	 * @param	array
	 * @return 	array
	 */
	public function fetch($rows, $table, $where="", $limit="", $binds = NULL)
    {  
		$table = $this->prefix . $table;

		$this->query = "SELECT {$rows} FROM `{$table}`";		
		$this->query = ( is_array( $where ) OR ( mb_strlen( $where ) AND $where !== NULL ) ) ? $this->query . ' WHERE ' . $where : $this->query;	
		$this->query = ( mb_strlen( $limit ) AND $limit !== NULL ) ? $this->query . ' LIMIT ' . $limit : $this->query;

		$this->statement = $this->db->prepare( $this->query );
		
		if($binds !== NULL) 
		{
			$this->bind($binds);
		}

		$this->execute();
		return $this->statement->fetch( \PDO::FETCH_ASSOC );
	}

	/**
	 * Fetches a row | Shorthand method
	 *
	 * @param 	string
	 * @param 	string
	 * @param 	string
	 * @param	string
	 * @param	array
	 * @return 	object
	 */
	public function f( $rows, $table, $where="", $limit="", $binds = NULL )
    {  
		$table = $this->prefix . $table;

		$this->query = "SELECT {$rows} FROM `{$table}`";			
		$this->query = ( is_array( $where ) OR ( mb_strlen( $where ) AND $where !== NULL ) ) ? $this->query . ' WHERE ' . $where : $this->query;		
		$this->query = ( mb_strlen( $limit ) AND $limit !== NULL )  ? $this->query . ' LIMIT ' . $limit : $this->query;
	
		$this->statement = $this->db->prepare( $this->query );
		
		if($binds !== NULL) 
		{
			$this->bind($binds);
		}

		$this->execute();
		$this->lastFetchedData = $this->statement->fetch( \PDO::FETCH_ASSOC );

		return static::i();
	}

	/**
	 * Returns a field from the lastfetcheddata array
	 *
	 * @param 		array|string|null 	What needs to be returned?
	 * @return 		mixed
	 */
	public function get( $item = NULL )
	{
		$return = $this->getLastFetchedItem( $item );
		/* Clean the array */
		$this->lastFetchedData = [];
		return $return;
	}

	/**
	 * Returns a field from the lastfetcheddata array
	 *
	 * @param 		array|string|null 	What needs to be returned?
	 * @return 		mixed
	 */
	public function getLastFetchedItem( $item = NULL )
	{
		if( $item === NULL )
		{
			return array_values( $this->lastFetchedData )[0];
		}
		elseif( is_array( $item ) )
		{
			foreach( $item AS $i )
			{
				$return[ $i ] = $this->lastFetchedData[ $i ];
			} 

			return $return;
		}
		else
		{
			return $this->lastFetchedData[ $item ];
		}
	}

	/**
	 * Fetches all rows with a criteria
	 *
	 * @param 	string
	 * @param 	string
	 * @param 	string
	 * @param	string
	 * @param	array
	 * @return 	array
	 */
	public function fetchAll( $rows, $table, $where="", $limit="", $binds = NULL )
    {  
		$table = $this->prefix . $table;

		$this->query = "SELECT {$rows} FROM `{$table}`";
		$this->query = ( is_array( $where ) OR ( mb_strlen( $where ) AND $where !== NULL ) ) ? $this->query . ' WHERE ' . $where : $this->query;		
		$this->query = ( mb_strlen( $limit ) AND $limit !== NULL )  ? $this->query . ' LIMIT ' . $limit : $this->query;	

		$this->statement = $this->db->prepare($this->query);
		
		if( $binds !== NULL )
		{
			$this->bind($binds);
		}

		$this->execute();
		return $this->statement->fetchAll( \PDO::FETCH_ASSOC );
	}

	/**
	 * Builds a query
	 *
	 * @param 		string 		The type of query we want to build
	 * @return 		\INS\Db::i()
	 */
	public function buildQuery( $type = 'select' )
	{
		/* Mark this so that other functions will know that we are still inside build query */
		$this->buildQueryComplete = FALSE; 

		switch( $type )
		{
			case 'select': 
				$this->query = 'SELECT ';
				$this->queryType = 1;
				break;
			case 'update': 
				$this->query = 'UPDATE ';
				$this->queryType = 2;
				break;
			case 'insert': 
				$this->query = 'INSERT INTO ';
				$this->queryType = 3;
			break;
			case 'drop': 
				$this->query = 'DROP TABLE IF EXISTS ';
				$this->queryType = 4;
				break;
			case 'create':
				$this->query = 'CREATE TABLE IF NOT EXISTS ';
				$this->queryType = 5;
				break;
			case 'truncate':
				$this->query = 'TRUNCATE TABLE  ';
				$this->queryType = 6;
				break;
			default: 
				$this->query = 'SELECT ';
				$this->queryType = 1;
		}

		$this->columnNamesNotSpecified = TRUE;
		return static::i();
	}

	/**
	 * Selects a table to work on
	 *
	 * @param 		string 		Table Name
	 * @return 		\INS\Db::i()
	 */
	public function table( $table = NULL )
	{
		if( $table === NULL )
		{
			return;
		}

		$table = $this->prefix . $table;

		/* No column yet specified? Probably the user forgot, lets leave a marker to insert column names */
		if( $this->columnNamesNotSpecified == TRUE )
		{
			/* For Select Queries */
			if( in_array( $this->queryType, [ 1 ] ) )
			{
				$this->query .= '<%C%> FROM ' . $table . ' ';
				$this->columnNamesNotSpecified = FALSE;
			}
			else
			{
				$this->query .= ' ' . $table . ' ';
			}
		}
		else
		{
			if( in_array( $this->queryType, [ 1 ] ) )
			{
				$this->query .= ' FROM ' . $table . ' ';
			}
			else
			{
				$this->query .= ' ' . $table . ' ';
			}
		}

		/* DROP/TRUNCATE */
		if( in_array( $this->queryType, [ 4,6 ] ) )
		{
			$this->statement = $this->db->prepare( $this->query );
			$this->execute();

			/* We have completed */
			$this->buildQueryComplete = TRUE; 
		}

		return static::i();
	}

	/**
	 * Columns
	 *
	 * @param 		string|array 		rows
	 * @return 		\INS\Db::i()
	 */
	public function columns( $rows )
	{
		if( is_array( $rows ) )
		{
			$rows = '`'.implode( '`,`', $rows ).'`';
		}

		/* Column marker present? Replace that! */
		if( $this->columnNamesNotSpecified == FALSE )
		{
			$this->query = str_replace( '<%C%>', ' '.$rows.' ', $this->query );
		}
		else
		{
			/* Select */
			if( $this->queryType === 1 )
			{
				$this->query .= $rows . ' ';
			}
			/* Update */
			elseif( $this->queryType === 3 )
			{
				$this->query .= sprintf( '(%s) ', $rows );
			}

			$this->columnNamesNotSpecified = FALSE;
		}

		return static::i();
	}

	/**
	 * Where
	 *
	 * @param 		string
	 * @return 		\INS\Db::i()
	 */
	public function where( $where )
	{
		$this->query .= sprintf( ' WHERE %s ', $where );

		return static::i();
	}	

	/**
	 * LEFT JOIN
	 *
	 * @param 		string 	Table to left join
	 * @param 		string  Join Condition
	 * @return 		\INS\Db::i()
	 */
	public function leftJoin( $table, $on )
	{
		$table = $this->prefix . $table;
		$this->query .= sprintf( ' LEFT JOIN %s ON (%s) ', $table, $on );

		return static::i();
	}	

	/**
	 * GROUP BY
	 *
	 * @param 		mixed 		The column/s to group by
	 * @return 		\INS\Db::i()
	 */
	public function groupBy( $column )
	{
		if( is_array( $column ) )
		{
			$column = implode( ',', $column );
		}
		$this->query .= sprintf( ' GROUP BY %s ', $column );

		return static::i();
	}	

	/**
	 * ORDER BY
	 *
	 * @param 		mixed 		The column/s to order by
	 * @return 		\INS\Db::i()
	 */
	public function orderBy( $column )
	{
		if( is_array( $column ) )
		{
			$column = implode( ',', $column );
		}

		$this->query .= sprintf( ' ORDER BY %s ', $column );

		return static::i();
	}	

	/**
	 * ORDER BY
	 *
	 * @param 		mixed 		The column/s to order by
	 * @return 		\INS\Db::i()
	 */
	public function limit( $lower, $upper = NULL )
	{
		$upper = ( $upper !== NULL ) ? ', ' . $upper : '';
		$this->query .= sprintf( ' LIMIT %s%s ', $lower, $upper );

		return static::i();
	}	

	/**
	 * Values for buildQuery
	 *
	 * @param 		array
	 * @return 		\INS\Db::i()
	 */
	public function values( $values )
	{
		/* Update */
		if( $this->queryType === 2 )
		{
			$comma = '';
			foreach( $values as $field => $value )
			{
				$value = str_replace( "'", "''", $value );
				$this->query .= ( ( $value == '?' ) OR preg_match('#^:#', $value)) ? "{$comma}`{$field}` = {$value}" : ( preg_match( sprintf( '/^(?:[\s]+)?%s/', $field ), $value ) ) ? "{$comma}`{$field}`={$value}" : "{$comma}`{$field}`={$quote}{$value}{$quote}";
				$comma = ', ';
			}
		}
		/* Insert */
		elseif( $this->queryType === 3 )
		{
			$comma = '';
			$_values = '';
			foreach( $values AS $value )
			{
				$value = str_replace( "'", "''", $value );
				/* Check for binds: ? or :named */
				$_values .= ( ( $value == '?' ) OR preg_match('#^:#', $value)) ? "{$comma}{$value}" : "{$comma}'{$value}'";
				$comma = ', ';
			}

			$this->query .= sprintf( ' VALUES(%s) ', $_values );
		}

		return static::i();
	}

	/**
	 * Completes a buildQuery
	 *
	 * @param 		boolean 	Return the first row?
	 * @return 		mixed
	 */
	public function complete( $oneRowOnly = FALSE )
	{
		/* No bound Parameters? Then just prepare and execute */
		if( $this->buildQueryNoBoundParameters === TRUE )
		{
			$this->statement = $this->db->prepare( $this->query );
		}
		/* Otherwise, mark this TRUE for other build queries. */
		else
		{
			$this->buildQueryNoBoundParameters = TRUE;
		}

		$this->execute();
		/* Build Query has completed its work :) */
		$this->buildQueryComplete = TRUE;

		/* If this is a select query, return rows appropriately */
		if( $this->queryType === 1 )
		{
			$return = $this->statement->fetchAll( \PDO::FETCH_ASSOC );

			if( $oneRowOnly )
			{
				return $return[0];
			}
			
			return $return;
		}
	}
	
	/** 
	 * Parses where clause
	 * Example usage: 
	 * $where = array( 'uid' => array( 'value' => 1 ), 'username' => array( 'value' => 'askamn', 'separator' => 'OR' ) ); 
	 *
	 * @param 	string|null|array 	The where clause to be parsed
	 * @return  string 				Parsed where clause
	 * @access 	public
	 */
	public function parseWhereClause( $where = NULL )
	{
		if( $where === NULL )
		{
			$where = $this->where;
		}

		if( is_array( $where ) )
		{
			foreach( $where AS $column => $array )
			{
				if( !is_array( $array ) )
				{
					$array = array( 'value' => $array );
				}

				$separator = isset( $array['separator'] ) ? ' '.$array['separator'].' ' : '';
				$_where .= $separator . '`' . $column . '` = ? ';
				$this->binds[ $this->bindOffset++ ] = $array['value'];
			}

			$where = $_where . ' ';
		}

		return $where;
	}

	/**
	 * Adds a column to a table in database
	 *
	 * @param	string		The table name
	 * @param	string		Name of column to add
	 * @param	string		Type of value column will store
	 * @param	integer		Other extra clauses
	 * @return	void
	 */
    public function addColumn($table, $colname, $type, $param = NULL, $extras = "NOT NULL")
	{
		$table = $this->prefix . $table;

	    if($param == NULL)
		{
			switch($type)
			{
				case 'int': $colvaltype = 'int(11)';
							break;
				case 'int_1': $colvaltype = 'int(1)';
							break;  		
				case 'str_32': $colvaltype = 'varchar(32)';
							break;  				
				case 'text': $colvaltype = 'text';
							break;  
				case 'date': $colvaltype = 'date(Y-m-d H:i:s)';
                            break;				
				default: $colvaltype = 'varchar(32)';						
			}	
		}
		else
		{
			switch($type)
			{
				case 'int': $colvaltype = "int({$param})";
							break;
				case 'str': $colvaltype = "varchar({$param})";
							break;  		
				case 'text': $colvaltype = "text";
							break;
				case 'date': $colvaltype = "date({$param})";
                            break;						
				default: $colvaltype = "varchar({$param})";						
			}
		}
		
		$this->query("ALTER TABLE `{$table}` ADD `{$colname}` {$colvaltype} {$extras}");
	}	
	
    /**
	 * Executes a query
	 * On failure, triggers a custom SQL error with no Backtrace
	 *
	 * @param 	string	
	 */
	public function execute()
    {
		try
		{ 
			$this->totalQueries++;
			$return = $this->statement->execute();

			/* Unset used variables */
			$this->query = '';
			$this->binds = [];
			$this->bindOffset = 1;

			return $return;
		}
		catch(\PDOException $e)
		{
			$definationarray = array(
				"query" 	=> '"'.$this->query.'"', 
				"message"   => $e->getMessage(), 
				"code"      => $e->getCode()
			);

			$errorTrace = $e->getTrace();
			array_shift($errorTrace);
			array_shift($errorTrace);
			
			\INS\Errorhandler::printError( 1000, $definationarray, $errorTrace[0]['file'], $errorTrace[0]['line'] );
		}
    }
	
    /**
	 * Number of rows that were used in the last query
	 *
	 * @return 	integer		Number of rows
	 */
	public function num_rows()
    {
		return $this->statement->rowCount();
    }

    /**
	 * Returns the ID of the last inserted row or sequence value
	 *
	 * @return 	integer		If a sequence name was not specified for the name parameter, PDO::lastInsertId() returns a string representing the row ID of the last row that was inserted into the database.
	 */
    public function lastInsertId( $name = NULL )
    {
    	return $this->db->lastInsertId( $name );	
    }
	
    /**
	 * Checks for duplicate rows
	 *
	 * @return 	boolean 
	 */
	public function hasDuplicates($value, $where, $table = 'users')
    {
    	$table = $this->prefix . $table;
		$rows = $this->db->query("SELECT COUNT(*) FROM `{$table}` WHERE `{$where}`='{$value}'")->fetchColumn();
	  
		/* No duplicates */
		if ( $rows == 0 )
			return FALSE;
		/* Duplicates */
		else
			return TRUE;
    }
	
    /**
	 * Escapes strings by adding slashes
	 *
	 * @return string 	escaped String
	 */
	public function escapeString($string)
    {
		return addslashes($string);
    }

    /**
	 * Optimizes a Table
     *
     * @param 	string 			The table name
	 * @return 	void
     */	
	public function optimize($table)
	{
		$table = $this->prefix . $table;
		$this->db->query("OPTIMIZE TABLE `{$table}`");
	}
	
    /**
	 * Optimizes all Table
     *
	 * @return 	void
     */	
	public function optimizeAll()
	{
		foreach($this->tablenames as $table)
		{	
			$table = $this->prefix . $table;
			$this->db->query("OPTIMIZE TABLE `{$table}`");
		}
	}
	
	/**
	 * Counts rows in a table
     *
     * @param 	string 		The table to work on
     * @param 	string 		Where clause
	 * @return 	void
     */	
	public function rowCount( $table, $where = '', $binds = NULL )
	{
		$table = $this->prefix . $table;

		if( $where == '' )	
		{
			return $this->db->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
		}
		else 	
		{
			$this->statement = $this->db->prepare( "SELECT COUNT(*) FROM `{$table}` WHERE {$where}" );
			/* Binds? */
			if( $binds !== NULL )
			{
				$this->bind( $binds );
			}

			$this->execute();
			return $this->statement->fetchColumn();
		}
	}
	
    /**
	 * Performs ACP queries SQL toolbox
     *
     * @param 		string 		The query to execute
	 * @return 		void
     */	
	public function acpQuery($query)
	{
		try
		{
			/* SELECT query */
			if( preg_match("/^(SELECT)/i", $query) )
			{
				$this->statement = $this->db->prepare( $query );
				$this->statement->execute();
				$array = $this->statement->fetchAll( \PDO::FETCH_ASSOC );
				
				$keys = array_keys($array[0]);
				if(count($keys) > 5)
				{
					$string = '<table class="table center" style="display: block; overflow: scroll;">';
				}
				else
				{
					$string = '<table class="table center">';
				}
				$string .= '<thead>';
				$string .= '<tr>';
				foreach($keys as $key)
				{
					$string .= "<th>{$key}</th>";
				}
				$string .= '</tr>';
				$string .= '</thead>';
				$string .= '<tbody>';
				foreach($array as $rows)
				{
					$string .= "<tr>";
					foreach($rows as $row => $val)
					{
						$string .= "<td>{$val}</td>";
					}
					$string .= "</tr>";
				}
				$string .= '</tbody>';
				$string .= '</table>';
				
				return $string;
			}
			/* Check for drop and flush */
			elseif ( preg_match("/^(DROP|FLUSH)/i", $query) )
			{
				return 'DROP/FLUSH Disallowed';
			}
			/* Check for SHOW */
			elseif ( preg_match("/^(SHOW)/i", $query) )
			{
				$rows = $this->db->query($query);
				$string = '<table class="table center">';
				$string .= '<tbody>';
				$string .= '<thead>';
				$string .= '<tr>';
				$string .= "<th>Table Name</th>";
				$string .= '</tr>';
				$string .= '</thead>';
				foreach( $rows->fetchAll() AS $row ) 
				{
					$string .= "<tr>";
					$i = 0;
					foreach($row as $_rows => $val)
					{
						if( $i == 0 )
							$string .= "<td>{$val}</td>";
						$i = 1;
					}	
					$string .= "</tr>";
				}
				
				$string .= '</tbody>';
				$string .= '</table>';
				
				return $string;
			}
			/* Other queries */
			else
			{
				$this->statement = $this->db->prepare($query);
				$this->statement->execute();
				$string = 'Statement Executed Successfully.';
				return $string;
			}
		}
		catch( \PDOException $e )
		{
			$e = preg_match( '#\'PDOException\' with message \'(.*?).php:#', $e, $s );
			$error['error_statement'] = $s[0];
			return $error;
		}		
		return $string;
	}	
   
    /**
     * Unsets the DB variable to close connection with Server
	 * 
	 * @return 	void
	 */
	public function __destruct()
    {
		unset( $this->db );
	}
} 

?>