-- MySQL dump 10.13  Distrib 5.5.42, for Linux (x86_64)
-- Server version	5.5.41-log

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `userid` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(30) NOT NULL,
  `password_hash` varchar(255),
  `reddit_token` varchar(1000) DEFAULT NULL,
  `reddit_refresh_token` varchar(1000) DEFAULT NULL,
  `expires_in` int(11) DEFAULT NULL,
  PRIMARY KEY (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `redditors_followed`
--

DROP TABLE IF EXISTS `redditors_followed`;
CREATE TABLE `redditors_followed` (
  `userid` int(11) NOT NULL,
  `redditor` varchar(30) NOT NULL,
  PRIMARY KEY (`userid`,`redditor`),
  CONSTRAINT `redditors_followed_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `subreddits_followed`
--

DROP TABLE IF EXISTS `subreddits_followed`;
CREATE TABLE `subreddits_followed` (
  `userid` int(11) NOT NULL,
  `subreddit` varchar(30) NOT NULL,
  `preference_value` int(11) DEFAULT '5',
  PRIMARY KEY (`userid`,`subreddit`),
  CONSTRAINT `subreddits_followed_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `sessionid` varchar(255),
  `userid` int(11) NOT NULL,
  `expires_in` int(11) DEFAULT NULL,
  PRIMARY KEY (`sessionid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
