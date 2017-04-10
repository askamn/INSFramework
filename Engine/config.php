<?php
/**
 *  Database Configuration
 */
$config['database']['name'] = 'source_up2ga514';
$config['database']['username'] = 'root';
$config['database']['password'] = '';
$config['database']['hostname'] = '127.0.0.1';
$config['database']['driver'] = 'PDO';
$config['database']['tablePrefix'] = 'ins';

/**
 * 	Data cache method to use
 *  Default: {%DEFAULT%}
 *
 *  If you wish to use the file system (cache/ directory), MemCache, xcache, or eAccelerator
 *  you can change the value below to 'files', 'memcache', 'xcache' or 'eaccelerator'.
 */
 
$config['cache']['method'] = 'Database';
$config['cache']['identifier'] = '';

/** 
 * If you are using Cache Layers other than DB, then: 
 *
 * @val TRUE	If a fetch from External cache layer has failed, the normal Database Cache method will be used to fetch data  		
 * @val	FALSE	If a fetch from External cache layer has failed, no other attempt will be made to fetch data from database
 */
$config['cache']['use_database_on_fail'] = TRUE; 

/**
 *	MemCache Configuration
 * 	Set the memcache Server/Port etc here
 *  This config is not required if you use some other Cache layer
 *
 * 	Default is set to Server:Port = localhost:11211
 *	Set the debug option to true if you wish Debug the Cache layer
 */
  
$config['memcache']['servers'][] = array(
	'server' => 'localhost', 
	'port' => 11211
);
$config['memcache']['debug'] = true;
$config['memcache']['compress'] = true;

/**
 * 	Admin Configuration
 */
$config['admin']['dir'] = "Admin";
$config['ins_unique_key'] = "wYzU1YWQwMTVhM2JmNGYxYjJiMGI4M";
$config['session_key'] = "561279";
?>