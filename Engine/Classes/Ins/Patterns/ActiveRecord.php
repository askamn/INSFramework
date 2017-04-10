<?php
/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms vSVN_VERSION
 * Active Record Pattern Implementation
 * Last Updated: $Date: 2014-10-28 5:35 (Tue, 26 Oct 2014) $
 * </pre>
 * 
 * @author      $Author: AskAmn$
 * @package     IN.CMS
 * @copyright   (c) 2014 - SVN_YYYY Infusion Network Solutions
 * @license     http://www.infusionnetwork/licenses/license.php?view=main&version=2014  Revision License 0.1.1
 * @since       SVN_VERSION
 * @version     SVN_VERSION_NUMBER
 */

namespace INS\Ins\Patterns;

abstract class ActiveRecord
{	
	/**
	 * @var  array	Stores all the data
	 */
	static public $store = [];

	/**
	 * @var  string	Database table
	 */
	static public $table = NULL;

	/**
	 * @var  string	ID field
	 */
	static public $idField = NULL;

	/**
	 * @var  boolean Is this a new record? NOTE: In order to perform SAVE() on a record, you need to explicitly mark it TRUE
	 */
	static public $newRecord = FALSE;

	/**
	 * @var  array  Array of changed values in a record
	 */
	public $changes = [];

	/**
	 * @var  array  Array of data for a record
	 */
	public $data = [];

	/**
	 * Constructs the default fields
	 */
	public function __construct()
	{
		$this->loadAnonymousData();
	}

	/**
	 * Get a field from the record
	 */
	public function __get( $name )
	{
		/* May be the class has its own work to do */
		if( method_exists( $this, '__get_' . $name ) )
		{
			return call_user_func( array( $this, '__get_' . $name ) );
		}

		if( isset( $this->data[ $name ] ) )
		{
			return $this->data[ $name ];
		}  

		return NULL;
	}

	/**
	 * Set a field with a value in the record
	 */
	public function __set( $name, $value )
	{
		/* May be the class has its own work to do */
		if( method_exists( $this, '__set_' . $name ) )
		{
			/* Store old values */
			$this->_data = $this->data;

			return call_user_func( array( $this, '__set_' . $name ), $this->data );

			/* Now figure out what is changed */
			foreach( $this->data AS $_name => $_value )
			{
				if( $_value != $this->_data[ $_name ] OR !array_key_exists( $_name, $this->_data ) )
				{
					$this->changes[ $_name ] = $_value;
				}
			}

			return TRUE;
		}

		$this->changes[ $name ] = $value;
		$this->data[ $name ] = $value;
	}

	/**
	 * Fetches a record
	 *
	 * @param 	integer|string 		The id of the record to fetch
	 * @param   string 				The record's id field
	 * @param   string 				Where clause to be used when fetching a record
	 * @param   string 				If the where clause contains binds, this param can be used
	 * @return	string
	 */
	static public function fetch( $id, $idField = NULL, $where = NULL, $binds = NULL )
	{
		/* Null? Use the child's id field */
		if( $idField !== NULL )
		{
			static::$idField = $idField;
		}

		/* Already in the store? */
		if( isset( static::$store[ $id ] ) )
		{
			if( static::$store[ $id ] === NULL )
			{
				return FALSE;
			}

			return static::$store[ $id ];
		}

		static::$store[ $id ] = static::feedData( static::fetchFromDb( $id, $where, $binds ) );

		return static::$store[ $id ];
	}

	/**
	 * Fetches a record from the database
	 *
	 * @param 	integer|string 		The id of the record to fetch
	 * @param   string 				The record's id field
	 * @param   string 				Where clause to be used when fetching a record
	 * @param   string 				If the where clause contains binds, this param can be used
	 * @return	array
	 */
	static public function fetchFromDb( $id, $where = NULL, $binds = NULL )
	{
		$where = ( $where !== NULL ? $where . ' AND ' : '' ) . '`'.static::$idField.'` = ? ';

		if( $binds !== NULL )
		{
			$num = count( $binds ) + 1;
			$binds = array_merge( [ $num => $id ], $binds );
		}
		else
		{
			$binds = [ 1 => $id ];
		}

		return \INS\Db::i()->fetch( '*', static::$table, $where, NULL, $binds );
	}

	/**
	 * Feeds data to a record
	 *
	 * @param 	array  Data to be fed
	 * @param   boolean Update Store if the record aleady exists
	 * @return 	Object
	 */
	static public function feedData( $data, $forceUpdateStore=TRUE )
	{
		$record = NULL;

		/* Record exists? */
		if( isset( static::$store[ $data[ static::$idField ] ] ) )
		{
			/* Return if we are not to force update the record */
			if( $forceUpdateStore === FALSE )
			{
				return static::$store[ $data[ static::$idField ] ];
			}

			$record = static::$store[ $data[ static::$idField ] ];
		}

		if( $record === NULL OR !$record )
		{
			$class  = get_called_class();
			$record = new $class;
			$record->data = $record->changes = [];
		}

		/* Feed the record! */
		foreach( $data AS $key => $value )
		{
			$record->data[ $key ] = $value;
		}

		static::$store[ $data[ static::$idField ] ] = $record;

		return $record;
	}

	/**
	 * Saves/Updates values for a record
	 */
	public function save()
	{
		/* Grab the id field */
		$idField = static::$idField;

		/* Changes is not empty, that means a record needs to be updated */
		if( $this->newRecord === TRUE )
		{
			/* Savor the lastInsertId in the id field */
			$this->$idField = \INS\Db::i()->insert( static::$table, $this->data );

			/* No longer a new record */
			$this->newRecord = FALSE;

			static::$store[ $this->$idField ] = $this;

			return TRUE;
		}

		/* We have some data, save it */ 
		if( !empty( $this->changes ) )
		{
			\INS\Db::i()->update( static::$table, $this->changes, array( $idField => $this->$idField ) );
			$this->changes = [];
		}
	}

	/**
	 * Deletes a record
	 */
	public function delete()
	{
		$idField = static::$idField;
		\INS\Db::i()->delete( static::$table, array( $idField => $this->$idField ) );
	}
}

?>