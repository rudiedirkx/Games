-- phpMyAdmin SQL Dump
-- version 3.2.0.1
-- http://www.phpmyadmin.net
--
-- Machine: localhost
-- Genereertijd: 05 Sept 2010 om 22:15
-- Serverversie: 5.1.36
-- PHP-Versie: 5.3.0

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `mpp2`
--

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `bets`
--

CREATE TABLE IF NOT EXISTS `bets` (
  `pot_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `player_id` int(10) unsigned NOT NULL DEFAULT '0',
  `bet` int(10) unsigned NOT NULL DEFAULT '0',
  `out` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`pot_id`),
  KEY `player_id` (`player_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Gegevens worden uitgevoerd voor tabel `bets`
--


-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `players`
--

CREATE TABLE IF NOT EXISTS `players` (
  `player_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `table_id` int(11) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `balance` int(11) NOT NULL DEFAULT '0',
  `sitting_out` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`player_id`),
  UNIQUE KEY `table_id` (`table_id`,`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Gegevens worden uitgevoerd voor tabel `players`
--

INSERT INTO `players` (`player_id`, `table_id`, `user_id`, `balance`, `sitting_out`) VALUES
(1, 2, 1, 90, 0),
(2, 2, 2, 80, 0),
(3, 2, 5, 130, 0);

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `pots`
--

CREATE TABLE IF NOT EXISTS `pots` (
  `pot_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `table_id` int(10) unsigned NOT NULL DEFAULT '0',
  `o` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`pot_id`),
  KEY `table_id` (`table_id`),
  KEY `o` (`o`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Gegevens worden uitgevoerd voor tabel `pots`
--


-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `tables`
--

CREATE TABLE IF NOT EXISTS `tables` (
  `table_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `table_name` varchar(60) NOT NULL DEFAULT '',
  `small_blind` int(10) unsigned NOT NULL DEFAULT '0',
  `big_blind` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`table_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Gegevens worden uitgevoerd voor tabel `tables`
--

INSERT INTO `tables` (`table_id`, `table_name`, `small_blind`, `big_blind`) VALUES
(1, 'For the pros', 50, 100),
(2, 'For the loserts', 1, 2);

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(40) NOT NULL DEFAULT '',
  `balance` int(11) NOT NULL DEFAULT '1000',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

--
-- Gegevens worden uitgevoerd voor tabel `users`
--

INSERT INTO `users` (`user_id`, `username`, `balance`) VALUES
(1, 'rudie', 900),
(2, 'jaap', 1000),
(3, 'bert', 1000),
(4, 'katrien', 1000),
(5, 'henk', 1000);
