-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: 
-- Generation Time: Oct 16, 2019 at 03:13 AM
-- Server version: 5.7.25-log
-- PHP Version: 7.1.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `php_mafia`
--


--
-- Table structure for table `actions`
--

CREATE TABLE `actions` (
  `action_id` int(11) NOT NULL COMMENT 'Autogenerated action index',
  `action_enum` varchar(255) NOT NULL DEFAULT '' COMMENT 'The ENUM we\'ll reference in php',
  `action_banner` varchar(255) NOT NULL DEFAULT '' COMMENT 'The banner to display for this action'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `actions`
--

INSERT INTO `actions` (`action_id`, `action_enum`, `action_banner`) VALUES
(1, 'KILL', ''),
(2, 'SAVE', ''),
(3, 'NO_KILL', 'Kill no one'),
(4, 'NO_SAVE', 'Save no one'),
(5, 'INVESTIGATE', ''),
(6, 'NO_INVESTIGATE', 'Investigate no one'),
(7, 'READY', 'Ready'),
(8, 'UN_READY', 'Not ready yet'),
(9, 'START', 'Start Game'),
(11, 'LYNCH', ''),
(12, 'NO_LYNCH', 'Lynch no one'),
(13, 'NO_ACTION', '');

-- --------------------------------------------------------

--
-- Table structure for table `channel_members`
--

CREATE TABLE `channel_members` (
  `channel_member_id` int(11) NOT NULL COMMENT 'Autogenerated id',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT 'User id',
  `channel_id` int(11) NOT NULL DEFAULT '0' COMMENT 'Channel id',
  `channel_post_rights` tinyint(4) DEFAULT '1' COMMENT '0 - Read only 1 - Write and read'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `channel_members`
--


-- --------------------------------------------------------

--
-- Table structure for table `channel_messages`
--

CREATE TABLE `channel_messages` (
  `message_id` int(11) NOT NULL,
  `channel_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `message_text` text NOT NULL,
  `message_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `channel_messages`
--

-- --------------------------------------------------------

--
-- Table structure for table `channels`
--

CREATE TABLE `channels` (
  `channel_id` int(11) NOT NULL,
  `channel_name` varchar(255) NOT NULL DEFAULT '',
  `game_id` int(11) NOT NULL DEFAULT '0',
  `global` char(1) DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `channels`
--

-- --------------------------------------------------------

--
-- Table structure for table `death_notices`
--

CREATE TABLE `death_notices` (
  `death_notice_id` int(11) NOT NULL,
  `death_notice` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `factions`
--

CREATE TABLE `factions` (
  `faction_id` int(11) NOT NULL COMMENT 'The autogenerated id',
  `faction_name` varchar(255) NOT NULL DEFAULT '' COMMENT 'The name of this faction',
  `faction_investigate_id` int(11) NOT NULL DEFAULT '0' COMMENT 'The faction id that this faction investigates as'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `factions`
--

INSERT INTO `factions` (`faction_id`, `faction_name`, `faction_investigate_id`) VALUES
(1, 'Town', 1),
(2, 'Antitown', 2),
(3, 'Psychopaths', 2),
(4, 'Unknown', 4);

-- --------------------------------------------------------

--
-- Table structure for table `game_actions`
--

CREATE TABLE `game_actions` (
  `game_action_id` int(11) NOT NULL COMMENT 'Auto generated in',
  `game_id` int(11) NOT NULL DEFAULT '0' COMMENT 'Game id',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT 'Id of player doing action',
  `action_id` int(11) NOT NULL DEFAULT '0' COMMENT 'Action id',
  `game_turn` int(11) NOT NULL DEFAULT '0' COMMENT 'Game turn',
  `game_phase` int(11) NOT NULL DEFAULT '0' COMMENT 'Game Phase (1/2)',
  `target_id` int(11) DEFAULT '0' COMMENT 'id of target',
  `game_action_priority` int(11) DEFAULT '0' COMMENT 'The priority this action should take in relation to others this turn'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `game_actions`
--

-- --------------------------------------------------------

--
-- Table structure for table `game_investigations`
--

CREATE TABLE `game_investigations` (
  `investigation_id` int(11) NOT NULL COMMENT 'Autogenerated id',
  `game_id` int(11) NOT NULL DEFAULT '0' COMMENT 'Game id',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT 'User id (does the investigation)',
  `target_id` int(11) NOT NULL DEFAULT '0' COMMENT 'Target id (gets investigated)',
  `role_id` int(11) NOT NULL DEFAULT '0' COMMENT 'Role id (what\'s found out)'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `game_investigations`
--

-- --------------------------------------------------------

--
-- Table structure for table `game_player_results`
--

CREATE TABLE `game_player_results` (
  `game_player_results_id` int(11) NOT NULL COMMENT 'Autogenerated id',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT 'User id',
  `role_id` int(11) NOT NULL DEFAULT '0' COMMENT 'Role id',
  `game_id` int(11) NOT NULL DEFAULT '0' COMMENT 'Game id',
  `result_id` int(11) NOT NULL DEFAULT '0' COMMENT 'Result id (look up in results table)'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `game_player_results`
--

-- --------------------------------------------------------

--
-- Table structure for table `game_players`
--

CREATE TABLE `game_players` (
  `game_player_id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `role_id` int(11) NOT NULL DEFAULT '0',
  `player_alive` varchar(255) NOT NULL DEFAULT 'Y',
  `player_ready` varchar(255) NOT NULL DEFAULT 'N',
  `player_needs_update` tinyint(1) DEFAULT '0' COMMENT '0 - up to date, 1 - needs update'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `game_players`
--

-- --------------------------------------------------------

--
-- Table structure for table `game_results`
--

CREATE TABLE `game_results` (
  `game_result_id` int(11) NOT NULL COMMENT 'The autogenerated id',
  `game_id` int(11) NOT NULL DEFAULT '0' COMMENT 'The game id',
  `roleset_id` int(11) NOT NULL DEFAULT '0' COMMENT 'The id of the roleset that was used',
  `faction_id` int(11) NOT NULL DEFAULT '0' COMMENT 'The id of the winning faction'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `game_results`
--

-- --------------------------------------------------------

--
-- Table structure for table `games`
--

CREATE TABLE `games` (
  `game_id` int(11) NOT NULL COMMENT 'The auto generated game index.',
  `game_name` varchar(255) NOT NULL DEFAULT '' COMMENT 'The game\'s name',
  `game_creator` int(11) NOT NULL DEFAULT '0' COMMENT 'The user id of the creator',
  `game_creation_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'The date this game was created',
  `game_phase` varchar(255) NOT NULL DEFAULT '' COMMENT 'The phase this game is in (0 - unstrated, 1 - night, 2 - day, 3 - finished)',
  `game_recent_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'I think the time of the last turn/phase change',
  `game_password` varchar(255) NOT NULL DEFAULT '' COMMENT 'Password for the game, if applicable',
  `game_turn` int(11) NOT NULL DEFAULT '0' COMMENT 'The game turn, in ints',
  `game_tracker` bigint(20) DEFAULT '0' COMMENT 'This gets updated by a random amount after every action.',
  `game_locked` tinyint(1) DEFAULT '0' COMMENT 'Is game locked. 0 - no, 1 - yes (meaning are players prohibited from doing stuff)',
  `game_roleset_id` int(11) DEFAULT '0' COMMENT 'The roleset chosen for this game'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `games`
--

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `news_id` int(11) NOT NULL,
  `news_text` text NOT NULL,
  `news_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `news_title` varchar(255) NOT NULL DEFAULT '',
  `news_author` varchar(255) NOT NULL DEFAULT '',
  `news_author_id` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='News table that the home page will pull from.';

--
-- Dumping data for table `news`
--

-- --------------------------------------------------------

--
-- Table structure for table `registration_codes`
--

CREATE TABLE `registration_codes` (
  `reg_id` int(11) NOT NULL,
  `user_email` varchar(255) NOT NULL DEFAULT '',
  `reg_code` varchar(255) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `results`
--

CREATE TABLE `results` (
  `result_id` int(11) NOT NULL COMMENT 'Autogenerated id',
  `result_enum` varchar(255) NOT NULL DEFAULT '' COMMENT 'Result enum',
  `result_english` varchar(255) NOT NULL DEFAULT '' COMMENT 'Result as it should be rendered in English'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `results`
--

INSERT INTO `results` (`result_id`, `result_enum`, `result_english`) VALUES
(1, 'LIVE_WIN', 'Won while alive'),
(2, 'DEAD_WIN', 'Won while dead'),
(3, 'LIVE_LOSS', 'Lost while alive'),
(4, 'DEAD_LOSS', 'Lost while dead');
-- --------------------------------------------------------

--
-- Table structure for table `rolesets`
--

CREATE TABLE `rolesets` (
  `roleset_id` int(11) NOT NULL COMMENT 'auto-generated id',
  `roleset_num_players` int(11) NOT NULL DEFAULT '0' COMMENT 'number of players for this roleset',
  `roleset_roles` varchar(255) NOT NULL DEFAULT '' COMMENT 'Roleset, in the form of a nice array (1, 2). Only do the unique roles'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='This table holds the rolesets. It stores the number of playe';

--
-- Dumping data for table `rolesets`
--

INSERT INTO `rolesets` (`roleset_id`, `roleset_num_players`, `roleset_roles`) VALUES
(1, 6, '2,2,4'),
(2, 6, '2,2,3'),
(3, 5, '7,3'),
(4, 5, '7,4'),
(5, 5, '7,4,4'),
(6, 6, '7,3'),
(7, 6, '7,4'),
(8, 6, '7,3,4'),
(9, 6, '7,6,6'),
(10, 7, '7,7,3,4'),
(11, 7, '7,7,4,4'),
(12, 7, '2,2'),
(13, 7, '2,2,3'),
(14, 7, '2,2,3,4'),
(15, 7, '2,2,4,4'),
(16, 7, '2,2,7,4,4'),
(17, 7, '2,2,6,6'),
(18, 7, '7,7,8'),
(19, 7, '7,7,8,3'),
(20, 7, '7,7,8,4'),
(21, 7, '7,8'),
(22, 8, '7'),
(23, 8, '7,3'),
(24, 8, '7,7,3,3'),
(25, 8, '7,7,4'),
(26, 8, '7,7,3,4'),
(27, 8, '7,7,4,4'),
(28, 8, '7,7,4,4,4'),
(29, 8, '7,7,6,6,6'),
(30, 8, '7,8,3'),
(31, 8, '2,2'),
(32, 8, '2,2,3'),
(33, 8, '2,2,3,4'),
(34, 8, '2,2,4'),
(35, 8, '2,2,2'),
(36, 8, '2,2,2,3'),
(37, 8, '2,2,2,3,4'),
(38, 8, '2,2,2,4,4'),
(39, 8, '2,2,2,8'),
(40, 8, '2,2,2,8,4'),
(41, 8, '2,2,6,6'),
(42, 8, '2,2,8,3'),
(43, 8, '2,2,8,4');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL COMMENT 'Autogenerated id',
  `role_name` varchar(255) NOT NULL DEFAULT '' COMMENT 'Role\'s name',
  `role_description` text NOT NULL COMMENT 'Role\'s description',
  `faction_id` int(11) NOT NULL DEFAULT '0' COMMENT 'Role\'s faction id',
  `night_instructions` text COMMENT 'Instructions for night time',
  `day_instructions` text COMMENT 'Instructions for day time',
  `role_channel` varchar(255) NOT NULL DEFAULT '' COMMENT 'Channel this role gets to use, if any. A private channel ends in _',
  `role_channel_rights` tinyint(4) DEFAULT '1' COMMENT '0 - Read only 1 - Write and read',
  `day_action_id` int(11) NOT NULL DEFAULT '0' COMMENT 'Action id for the day',
  `night_action_id` int(11) NOT NULL DEFAULT '0' COMMENT 'Action id for the night',
  `day_alt_action_id` int(11) NOT NULL DEFAULT '0' COMMENT 'Alternate action for the day (i.e. NO_LYNCH)',
  `night_alt_action_id` int(11) NOT NULL DEFAULT '0' COMMENT 'Alternate action for the night (i.e. NO_KILL)',
  `role_target_group` varchar(255) DEFAULT NULL COMMENT 'IF a group has to agree on a target, put a value here. Otherwise ignore it.',
  `role_action_priority` int(11) DEFAULT '0' COMMENT 'The order in which this role\'s kill should happen. Low is last.',
  `role_inform_others` tinyint(1) DEFAULT '0' COMMENT 'Should others of this role be able to identify each other? (0 - no, 1 - yes)',
  `role_help` text,
  `investigate_faction_id` int(11) NOT NULL DEFAULT '0' COMMENT 'The id of the faction this character shows up as during an investigation'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Table full of what roles are what';

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`, `role_description`, `faction_id`, `night_instructions`, `day_instructions`, `role_channel`, `role_channel_rights`, `day_action_id`, `night_action_id`, `day_alt_action_id`, `night_alt_action_id`, `role_target_group`, `role_action_priority`, `role_inform_others`, `role_help`, `investigate_faction_id`) VALUES
(1, 'Townie', 'You are just your everyday citizen, bent on surviving another night!', 1, 'Sit back and pray you don\'t die a horrible, painful death.', 'Please click on the person you would like to vote to lynch.', '', 1, 11, 13, 12, 13, NULL, 0, 0, 'The Townie is the most basic role in Mafia. Simply put, you are a pro-Town character, who only wins when the town wins. You have no night actions, and can only lynch during the day. Essentially cannon fodder.', 1),
(2, 'Mafia Goon', 'You are a Mafia hitman, killing off citizens and trying to frame others.', 2, 'Chat with your partners, once all Mafia have voted to kill the same person it\'ll happen.', 'Pretend you\'re a loyal citizen and get a real one lynched!', 'mafia', 1, 11, 1, 12, 3, 'mafia_goon', 3, 1, 'The Mafia Goon is the evil hitman for the Mafia. They are anti-town characters who get a night kill and the ability to talk amongst themselves. In order to perform the night kill, all (living) Mafia Goons must be targeting the same character. Mafia Goon kills come after Serial Killer kills.', 2),
(3, 'Cop', 'You are the last defense of the town. Your investigative skills alone can determine who\'s good, and who\'s evil.', 1, 'Choose the person you would like to investigate.', 'Please click on the person you would like to vote to lynch.', 'cop_', 0, 11, 5, 12, 6, NULL, 0, 0, 'The Cop is a pro-town character who has the ability to investigate one player every night and learn their faction (pro-town, or anti-town). Note: Serial Killers, and any other Psychopaths, show upas simply \'anti-town\'.', 1),
(4, 'Doctor', 'You use your medical skills to save the townsfolk. ', 1, 'Choose a person to perform an operation on tonight, perhaps saving their life.', 'Please click on the person you would like to vote to lynch.', 'doctor_', 0, 11, 2, 12, 4, NULL, 0, 0, 'The doctor is another pro-town role who has a special night action. Once per night the doctor can choose a character to save, and if that character was supposed to die, they won\'t. They can also choose to save no one, but may never choose to save themselves.', 1),
(5, 'Unassigned', 'Role not assigned yet.', 4, 'Role not assigned yet.', 'Role not assigned yet.', 'unassigned', 1, 7, 13, 7, 13, NULL, 0, 0, 'Unassigned is the role that all players are assigned before the game begins. This role goes away as soon as the game begins, so pay no heed to it.', 4),
(6, 'Mason', 'You are a member of the secretive Mason group, utilizing your brotherly ties to trust citizens around you who you might otherwise not be able to.', 1, 'Sit back, try not to get killed, and chat with your other Mason brothers.', 'Pick someone to lynch!', 'mason', 1, 11, 13, 12, 13, NULL, 0, 1, 'The Mason is a pro-town character who belongs to a secret brother(or sister)hood. All Masons can talk to each other during the night phase, but get no special action other than chatting.', 1),
(7, 'Serial Killer', 'You are a psychopath. Kill everyone.', 3, 'Choose a person to kill tonight.', 'Lay low and try to get an innocent lynched! ', 'serialkiller_', 0, 11, 1, 12, 3, NULL, 5, 0, 'The Serial Killer is a pure psychopath. He wins if and only if all other players are dead. He gets one kill per night, and his kills happen first, so if the Mafia target the Serial Killer, and the Serial Killer targets one of the Mafia, the Mafia would die, then the Serial Killer. This order is purely to prevent ties.', 2),
(8, 'Vigilante', 'You are a vigilante, dispensing whatever justice you see fit on the streets at night. ', 1, 'Pick a criminal to die. Don\'t know one? Then take a guess.', 'Help the other townsfolk lynch the evil syndicate!', 'vigilante_', 0, 11, 1, 12, 3, NULL, 1, 0, 'The Vigilante is a pro-town role who has taken it upon them self to rid the world of evil. They get one kill per night, a kill that happens after all evil role kills, and the Vigilante wins if the town wins. ', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `user_name` varchar(255) NOT NULL DEFAULT '',
  `user_email` varchar(255) NOT NULL DEFAULT '',
  `user_pass` varchar(255) NOT NULL DEFAULT '',
  `user_role` varchar(255) DEFAULT 'U' COMMENT 'U - unprivileged A - admin',
  `user_avatar` varchar(255) DEFAULT 'face.png',
  `user_hash` varchar(255) NOT NULL DEFAULT '',
  `user_joined` date NOT NULL DEFAULT '0000-00-00'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Users table';

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `user_name`, `user_email`, `user_pass`, `user_role`, `user_avatar`, `user_hash`, `user_joined`) VALUES
(1, 'admin', 'admin@totallyfakedomain.com', MD5('admin'), 'A', 'face.png', '433255f52fdc64ab5389d49333836ec2', NOW());

--
-- Indexes for dumped tables
--

--
-- Indexes for table `actions`
--
ALTER TABLE `actions`
  ADD PRIMARY KEY (`action_id`);

--
-- Indexes for table `channels`
--
ALTER TABLE `channels`
  ADD PRIMARY KEY (`channel_id`),
  ADD UNIQUE KEY `channel_name_per_game` (`channel_name`,`game_id`);

--
-- Indexes for table `channel_members`
--
ALTER TABLE `channel_members`
  ADD PRIMARY KEY (`channel_member_id`),
  ADD KEY `user_channel_id` (`user_id`,`channel_id`);

--
-- Indexes for table `channel_messages`
--
ALTER TABLE `channel_messages`
  ADD PRIMARY KEY (`message_id`);

--
-- Indexes for table `death_notices`
--
ALTER TABLE `death_notices`
  ADD PRIMARY KEY (`death_notice_id`);

--
-- Indexes for table `factions`
--
ALTER TABLE `factions`
  ADD PRIMARY KEY (`faction_id`);

--
-- Indexes for table `games`
--
ALTER TABLE `games`
  ADD PRIMARY KEY (`game_id`);

--
-- Indexes for table `game_actions`
--
ALTER TABLE `game_actions`
  ADD PRIMARY KEY (`game_action_id`),
  ADD KEY `game_id` (`game_id`,`user_id`,`game_turn`,`game_phase`);

--
-- Indexes for table `game_investigations`
--
ALTER TABLE `game_investigations`
  ADD PRIMARY KEY (`investigation_id`);

--
-- Indexes for table `game_players`
--
ALTER TABLE `game_players`
  ADD PRIMARY KEY (`game_player_id`),
  ADD UNIQUE KEY `game_player` (`game_id`,`user_id`);

--
-- Indexes for table `game_player_results`
--
ALTER TABLE `game_player_results`
  ADD PRIMARY KEY (`game_player_results_id`);

--
-- Indexes for table `game_results`
--
ALTER TABLE `game_results`
  ADD PRIMARY KEY (`game_result_id`);

--
-- Indexes for table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`news_id`);

--
-- Indexes for table `registration_codes`
--
ALTER TABLE `registration_codes`
  ADD PRIMARY KEY (`reg_id`),
  ADD UNIQUE KEY `email` (`user_email`);

--
-- Indexes for table `results`
--
ALTER TABLE `results`
  ADD PRIMARY KEY (`result_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `rolesets`
--
ALTER TABLE `rolesets`
  ADD PRIMARY KEY (`roleset_id`),
  ADD KEY `roleset_num_players` (`roleset_num_players`,`roleset_roles`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `user_name_2` (`user_name`),
  ADD UNIQUE KEY `user_email` (`user_email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `actions`
--
ALTER TABLE `actions`
  MODIFY `action_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Autogenerated action index', AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `channels`
--
ALTER TABLE `channels`
  MODIFY `channel_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `channel_members`
--
ALTER TABLE `channel_members`
  MODIFY `channel_member_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Autogenerated id', AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `channel_messages`
--
ALTER TABLE `channel_messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `death_notices`
--
ALTER TABLE `death_notices`
  MODIFY `death_notice_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `factions`
--
ALTER TABLE `factions`
  MODIFY `faction_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'The autogenerated id', AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `games`
--
ALTER TABLE `games`
  MODIFY `game_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'The auto generated game index.', AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `game_actions`
--
ALTER TABLE `game_actions`
  MODIFY `game_action_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Auto generated in', AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `game_investigations`
--
ALTER TABLE `game_investigations`
  MODIFY `investigation_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Autogenerated id', AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `game_players`
--
ALTER TABLE `game_players`
  MODIFY `game_player_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `game_player_results`
--
ALTER TABLE `game_player_results`
  MODIFY `game_player_results_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Autogenerated id', AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `game_results`
--
ALTER TABLE `game_results`
  MODIFY `game_result_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'The autogenerated id', AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
  MODIFY `news_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `registration_codes`
--
ALTER TABLE `registration_codes`
  MODIFY `reg_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `results`
--
ALTER TABLE `results`
  MODIFY `result_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Autogenerated id', AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Autogenerated id', AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `rolesets`
--
ALTER TABLE `rolesets`
  MODIFY `roleset_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'auto-generated id', AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

-- --------------------------------------------------------
