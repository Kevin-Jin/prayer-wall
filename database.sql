CREATE TABLE `users` (
	`userid` INT(11) NOT NULL AUTO_INCREMENT,
	`email` VARCHAR(254) NOT NULL,
	`password` BINARY(60) NOT NULL,
	`displayname` VARCHAR(255),
	PRIMARY KEY (`userid`),
	KEY (`email`)
) ENGINE=InnoDB;

CREATE TABLE `posts` (
	`postid` INT(11) NOT NULL AUTO_INCREMENT,
	`posttime` TIMESTAMP NOT NULL,
	`message` VARCHAR(255) NOT NULL,
	`poster` INT(11),
	PRIMARY KEY (`postid`),
	KEY (`poster`),
	CONSTRAINT FOREIGN KEY (`poster`) REFERENCES `users` (`userid`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `cookies` ( 
  `uniqueid` BIGINT(20) NOT NULL AUTO_INCREMENT, 
  `userid` INT(11) NOT NULL, 
  `tokenhash` BINARY(60) NOT NULL, 
  PRIMARY KEY (`uniqueid`), 
  KEY (`userid`), 
  CONSTRAINT FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON DELETE CASCADE
) Engine=InnoDB;