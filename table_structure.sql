CREATE TABLE `contatore` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `php_self` varchar(60) NOT NULL,
  `remote_addr` varchar(40) NOT NULL,
  `http_referer` varchar(200) NOT NULL,
  `http_user_agent` varchar(200) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;
