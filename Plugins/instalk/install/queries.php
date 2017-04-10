<?php

$queries = array();

/* Fist Drop Old Stuff */
$queries[] = 'DROP TABLE IF EXISTS `instalk`';

/* Now create table */
$queries[] = "CREATE TABLE instalk(
 `mid` INT unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY, 
 `fromid` INT NOT NULL, 
 `toid` INT NOT NULL, 
 `subject` VARCHAR(120) NOT NULL DEFAULT '',
 `message` TEXT NOT NULL,
 `status` INT(1) NOT NULL default '0',
 `stime` BIGINT(30) NOT NULL default '0',
 `rtime` BIGINT(30) NOT NULL default '0'
)ENGINE=MyISAM";

?>