<?php

/* No queries to run... so let us leave it as is. */
$queries = array();

$queries[] = '
CREATE TABLE `drschedule` 
(
	`sid` int(20) PRIMARY KEY AUTO_INCREMENT,
	`start` text,
	`finish` text, 
	`presenter` text,
	`showname` text, 
	`description` text,
	`banner` text
)
';

$queries[] = '
CREATE TABLE `drlogs`
(
	`lid` int(20) PRIMARY KEY AUTO_INCREMENT,
	`name` text,
	`time` text,
	`ip` text
)
';

$queries[] = '
CREATE TABLE `drevents`
(
	`eid` int(20) PRIMARY KEY AUTO_INCREMENT,
	`name` text,
	`description` text,
	`date` text,
	`times` text
)
';
$queries[] = '
CREATE TABLE `drcovers`
(
	`cid` int(20) PRIMARY KEY AUTO_INCREMENT,
	`uid` int(20),
	`day` text,
	`date` text,
	`start` text,
	`finish` text, 
	`reason` text, 
	`status` text
)
';

?>