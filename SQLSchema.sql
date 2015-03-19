-- MySQL dump 10.13  Distrib 5.5.42, for Linux (x86_64)
-- Server version	5.5.41-log

--
-- Table structure for table `followingRedditors`
--

DROP TABLE IF EXISTS `followingRedditors`;
CREATE TABLE `followingRedditors` (
  `userName` varchar(30) NOT NULL,
  `redditor` varchar(30) NOT NULL,
  PRIMARY KEY (`userName`,`redditor`),
  CONSTRAINT `followingRedditors_ibfk_1` FOREIGN KEY (`userName`) REFERENCES `users` (`userName`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `followingSubreddit`
--

DROP TABLE IF EXISTS `followingSubreddit`;
CREATE TABLE `followingSubreddit` (
  `userName` varchar(30) NOT NULL,
  `subreddit` varchar(30) NOT NULL,
  `preferenceValue` int(11) DEFAULT '5',
  PRIMARY KEY (`userName`,`subreddit`),
  CONSTRAINT `followingSubreddit_ibfk_1` FOREIGN KEY (`userName`) REFERENCES `users` (`userName`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `userName` varchar(30) NOT NULL,
  `userPassword` varchar(30) NOT NULL,
  `redditToken` varchar(1000) DEFAULT NULL,
  `redditRefreshToken` varchar(1000) DEFAULT NULL,
  `expires_in` int(11) DEFAULT NULL,
  PRIMARY KEY (`userName`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
