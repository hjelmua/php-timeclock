-- php-timeclock database schema
-- Exporterad från phptimeclock på arnor.pentarex.se (2026-03-29)
-- Skapa databas och kör sedan: mysql -u <user> -p phptimeclock < database-schema.sql

CREATE TABLE `audit` (
  `modified_by_ip` varchar(39) NOT NULL DEFAULT '',
  `modified_by_user` varchar(50) NOT NULL DEFAULT '',
  `modified_when` bigint(14) NOT NULL,
  `modified_from` bigint(14) NOT NULL,
  `modified_to` bigint(14) NOT NULL,
  `modified_why` varchar(250) NOT NULL DEFAULT '',
  `user_modified` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`modified_when`),
  UNIQUE KEY `modified_when` (`modified_when`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

CREATE TABLE `dbversion` (
  `dbversion` decimal(5,1) NOT NULL DEFAULT 0.0,
  PRIMARY KEY (`dbversion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

CREATE TABLE `employees` (
  `empfullname` varchar(50) NOT NULL DEFAULT '',
  `tstamp` bigint(14) DEFAULT NULL,
  `employee_passwd` varchar(25) NOT NULL DEFAULT '',
  `displayname` varchar(50) NOT NULL DEFAULT '',
  `email` varchar(75) NOT NULL DEFAULT '',
  `groups` varchar(50) NOT NULL DEFAULT '',
  `office` varchar(50) NOT NULL DEFAULT '',
  `admin` tinyint(1) NOT NULL DEFAULT 0,
  `reports` tinyint(1) NOT NULL DEFAULT 0,
  `time_admin` tinyint(1) NOT NULL DEFAULT 0,
  `disabled` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`empfullname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

CREATE TABLE `groups` (
  `groupname` varchar(50) NOT NULL DEFAULT '',
  `groupid` int(10) NOT NULL AUTO_INCREMENT,
  `officeid` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`groupid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

CREATE TABLE `info` (
  `fullname` varchar(50) NOT NULL DEFAULT '',
  `inout` varchar(50) NOT NULL DEFAULT '',
  `timestamp` bigint(14) DEFAULT NULL,
  `notes` varchar(250) DEFAULT NULL,
  `ipaddress` varchar(39) NOT NULL DEFAULT '',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  KEY `fullname` (`fullname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

CREATE TABLE `metars` (
  `metar` varchar(255) NOT NULL DEFAULT '',
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `station` varchar(4) NOT NULL,
  PRIMARY KEY (`station`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

CREATE TABLE `offices` (
  `officename` varchar(50) NOT NULL DEFAULT '',
  `officeid` int(10) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`officeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

CREATE TABLE `punchlist` (
  `punchitems` varchar(50) NOT NULL DEFAULT '',
  `color` varchar(7) NOT NULL DEFAULT '',
  `in_or_out` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`punchitems`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
