-- phpMyAdmin SQL Dump
-- version 2.8.0.3
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Jun 15, 2007 at 04:49 PM
-- Server version: 5.0.27
-- PHP Version: 5.2.0
-- 
-- Database: `vibage`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `132_cards`
-- 

CREATE TABLE `132_cards` (
  `id` int(11) NOT NULL auto_increment,
  `card` enum('h.2','h.3','h.4','h.5','h.6','h.7','h.8','h.9','h.10','h.11','h.12','h.13','h.14','c.2','c.3','c.4','c.5','c.6','c.7','c.8','c.9','c.10','c.11','c.12','c.13','c.14','d.2','d.3','d.4','d.5','d.6','d.7','d.8','d.9','d.10','d.11','d.12','d.13','d.14','s.2','s.3','s.4','s.5','s.6','s.7','s.8','s.9','s.10','s.11','s.12','s.13','s.14') NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=53 ;

-- 
-- Dumping data for table `132_cards`
-- 

INSERT INTO `132_cards` (`id`, `card`) VALUES (1, 'h.2'),
(2, 'h.3'),
(3, 'h.4'),
(4, 'h.5'),
(5, 'h.6'),
(6, 'h.7'),
(7, 'h.8'),
(8, 'h.9'),
(9, 'h.10'),
(10, 'h.11'),
(11, 'h.12'),
(12, 'h.13'),
(13, 'h.14'),
(14, 'c.2'),
(15, 'c.3'),
(16, 'c.4'),
(17, 'c.5'),
(18, 'c.6'),
(19, 'c.7'),
(20, 'c.8'),
(21, 'c.9'),
(22, 'c.10'),
(23, 'c.11'),
(24, 'c.12'),
(25, 'c.13'),
(26, 'c.14'),
(27, 'd.2'),
(28, 'd.3'),
(29, 'd.4'),
(30, 'd.5'),
(31, 'd.6'),
(32, 'd.7'),
(33, 'd.8'),
(34, 'd.9'),
(35, 'd.10'),
(36, 'd.11'),
(37, 'd.12'),
(38, 'd.13'),
(39, 'd.14'),
(40, 's.2'),
(41, 's.3'),
(42, 's.4'),
(43, 's.5'),
(44, 's.6'),
(45, 's.7'),
(46, 's.8'),
(47, 's.9'),
(48, 's.10'),
(49, 's.11'),
(50, 's.12'),
(51, 's.13'),
(52, 's.14');

-- --------------------------------------------------------

-- 
-- Table structure for table `132_cards_in_game`
-- 

CREATE TABLE `132_cards_in_game` (
  `game_id` int(11) NOT NULL,
  `card_id` int(11) NOT NULL,
  PRIMARY KEY  (`game_id`,`card_id`),
  KEY `card_id` (`card_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `132_cards_in_game`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `132_games`
-- 

CREATE TABLE `132_games` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `132_games`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `132_players`
-- 

CREATE TABLE `132_players` (
  `id` int(11) NOT NULL auto_increment,
  `username` varchar(255) NOT NULL,
  `password` varchar(40) NOT NULL,
  `balance` float(12,2) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `132_players`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `132_players_in_games`
-- 

CREATE TABLE `132_players_in_games` (
  `player_id` int(11) NOT NULL default '0',
  `game_id` int(11) NOT NULL,
  `seat` enum('1','2','3','4','5','6','7','8') NOT NULL default '1',
  `bet` float(12,2) NOT NULL,
  `in_or_out` enum('in','out') NOT NULL default 'in',
  `ready_for_next_round` enum('0','1') NOT NULL default '0',
  PRIMARY KEY  (`player_id`,`game_id`),
  KEY `game_id` (`game_id`),
  KEY `player_id` (`player_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `132_players_in_games`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `132_rounds`
-- 

CREATE TABLE `132_rounds` (
  `id` int(11) NOT NULL auto_increment,
  `game_id` int(11) NOT NULL,
  `pot` float(12,2) NOT NULL default '0.00',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `132_rounds`
-- 


-- 
-- Constraints for dumped tables
-- 

-- 
-- Constraints for table `132_players_in_games`
-- 
ALTER TABLE `132_players_in_games`
  ADD CONSTRAINT `132_players_in_games_fk1` FOREIGN KEY (`game_id`) REFERENCES `132_games` (`id`),
  ADD CONSTRAINT `132_players_in_games_fk` FOREIGN KEY (`player_id`) REFERENCES `132_players` (`id`);
