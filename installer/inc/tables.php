<?php
$tablenames = array("blogcomments", "admin_sessions", "blogposts", 
					"settings", "settings_group", "shows", 
					"sidebar", "sidebar_sublinks", "themes",
					"users", "templates",'templates_admin', "user_requests", "inscache",
					"applications"
					);
$tables[] = "CREATE TABLE IF NOT EXISTS `applications` (
  `aid` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `description` text,
  `author` text NOT NULL,
  `dir` text NOT NULL,
  `version` text,
  `version_code` int(10) DEFAULT NULL,
  `installdate` int(15) NOT NULL,
  `enabled` int(1) NOT NULL,
  `website` text,
  `updatecheck_url` text,
  `hooks` text,
  PRIMARY KEY (`aid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";		
			
$tables[] = "CREATE TABLE IF NOT EXISTS `admin_sessions` (
  `sid` int(11) NOT NULL,
  `key` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `time` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `uid` int(15) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

$tables[] = "CREATE TABLE IF NOT EXISTS `blogcomments` (
  `cid` int(50) NOT NULL AUTO_INCREMENT,
  `pid` int(50) NOT NULL,
  `comment` text NOT NULL,
  `author` varchar(50) NOT NULL,
  `date` date DEFAULT NULL,
  `time` time DEFAULT NULL,
  PRIMARY KEY (`cid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

$tables[] = "CREATE TABLE IF NOT EXISTS `blogposts` (
  `pid` int(50) NOT NULL AUTO_INCREMENT,
  `post` text NOT NULL,
  `author` varchar(50) NOT NULL,
  `date` date DEFAULT NULL,
  `time` time DEFAULT NULL,
  `comments` int(5) DEFAULT NULL,
  PRIMARY KEY (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

$tables[] = "CREATE TABLE IF NOT EXISTS `settings` (
  `sid` int(11) NOT NULL AUTO_INCREMENT,
  `group` text COLLATE utf8_unicode_ci NOT NULL,
  `name` text COLLATE utf8_unicode_ci NOT NULL,
  `name_string` text COLLATE utf8_unicode_ci NOT NULL,
  `name_desc` text COLLATE utf8_unicode_ci NOT NULL,
  `value` text COLLATE utf8_unicode_ci NOT NULL,
  `gid` int(11) NOT NULL DEFAULT '-1',
  `type` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'text',
  `parent` int(4) NOT NULL,
  PRIMARY KEY (`sid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

$tables[] = "CREATE TABLE IF NOT EXISTS `settings_group` (
  `group` text COLLATE utf8_unicode_ci NOT NULL,
  `group_title` text COLLATE utf8_unicode_ci NOT NULL,
  `group_desc` text COLLATE utf8_unicode_ci NOT NULL,
  `gid` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

$tables[] = "CREATE TABLE IF NOT EXISTS `shows` (
  `sid` int(1) NOT NULL AUTO_INCREMENT,
  `show` text NOT NULL,
  `season` varchar(30) NOT NULL,
  `episode` varchar(30) NOT NULL,
  PRIMARY KEY (`sid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

$tables[] = "CREATE TABLE IF NOT EXISTS `sidebar` (
  `lid` int(1) NOT NULL AUTO_INCREMENT,
  `link` text NOT NULL,
  `icon` text NOT NULL,
  `name` text NOT NULL,
  `app` text NOT NULL,
  `module` text NOT NULL,
  PRIMARY KEY (`lid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";

$tables[] = "CREATE TABLE IF NOT EXISTS `sidebar_sublinks` (
  `slid` int(1) NOT NULL AUTO_INCREMENT,
  `lid` int(1) NOT NULL,
  `link` text NOT NULL,
  `name` text NOT NULL,
  `app` text NOT NULL,
  PRIMARY KEY (`slid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";

$tables[] = "CREATE TABLE IF NOT EXISTS `templates` (
  `tid` int(11) NOT NULL AUTO_INCREMENT,
  `gid` int(11) NOT NULL,
  `original_template_data` text COLLATE utf8_unicode_ci NOT NULL,
  `template_data` text COLLATE utf8_unicode_ci NOT NULL,
  `name` text COLLATE utf8_unicode_ci NOT NULL,
  `group` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`tid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

$tables[] = "CREATE TABLE IF NOT EXISTS `themes` (
  `gid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` text,
  `group` varchar(10) DEFAULT NULL,
  `default` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`gid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

$tables[] = "CREATE TABLE IF NOT EXISTS `templates_admin` (
  `tid` int(11) NOT NULL AUTO_INCREMENT,
  `gid` int(11) NOT NULL,
  `original_template_data` text COLLATE utf8_unicode_ci NOT NULL,
  `template_data` text COLLATE utf8_unicode_ci NOT NULL,
  `name` text COLLATE utf8_unicode_ci NOT NULL,
  `group` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`tid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ";

$tables[] = "CREATE TABLE IF NOT EXISTS `users` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `password` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `avatar` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `facebook` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `twitter` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `google-plus` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `activation_state` int(2) NOT NULL DEFAULT '0',
  `activation_code` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `regip` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `joindate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `group` int(2) NOT NULL DEFAULT '2',
  `lastlogin` text COLLATE utf8_unicode_ci NOT NULL,
  `lastloginip` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

$tables[] = "CREATE TABLE IF NOT EXISTS `user_requests` (
  `rid` int(1) NOT NULL AUTO_INCREMENT,
  `uid` int(1) NOT NULL,
  `code` text NOT NULL,
  `rtype` varchar(1) NOT NULL,
  `misc` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`rid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

$tables[] = "CREATE TABLE IF NOT EXISTS `inscache` (
  `key` text NOT NULL,
  `cache` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8";

$tables[] = "CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(60) NOT NULL DEFAULT '0',
  `member_name` varchar(255) DEFAULT NULL,
  `uid` int(10) NOT NULL DEFAULT '',
  `member_group` smallint(3) DEFAULT NULL,
  `ip` varchar(46) DEFAULT NULL,
  `browser` varchar(200) NOT NULL DEFAULT '',
  `location_type` varchar(255) NOT NULL DEFAULT '',
  `location_id` int(10) NOT NULL DEFAULT '0',
  `app` varchar(100) NOT NULL DEFAULT '',
  `module` varchar(100) NOT NULL DEFAULT '',
  `section` varchar(100) NOT NULL DEFAULT '',
  `uagent` varchar(200) NOT NULL DEFAULT '',
  `uagent_version` varchar(100) NOT NULL DEFAULT '',
  `uagent_type` varchar(200) NOT NULL DEFAULT '',
  `uagent_bypass` int(1) NOT NULL DEFAULT '0',
  `search_thread_id` int(11) NOT NULL DEFAULT '0',
  `search_thread_time` int(11) NOT NULL DEFAULT '0',
  `session_msg_id` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `location1` (`location_1_type`,`location_1_id`),
  KEY `location2` (`location_2_type`,`location_2_id`),
  KEY `location3` (`location_3_type`,`location_3_id`),
  KEY `running_time` (`running_time`),
  KEY `member_id` (`member_id`),
  KEY `ip_address` (`ip_address`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
";
?>