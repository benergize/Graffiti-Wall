<?php


	/*	
		Run the below query to create the required table for this program:
		
			CREATE TABLE IF NOT EXISTS `pictures` (
			  `pic_id` int NOT NULL AUTO_INCREMENT,
			  `pic` text,
			  `tstamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
			  PRIMARY KEY (`pic_id`)
			)
	*/
	
	
	define( 'DB_NAME', 'database' );

	/** MySQL database username */
	define( 'DB_USER', 'user' );

	/** MySQL database password */
	define( 'DB_PASSWORD', 'password' );

	/** MySQL hostname */
	define( 'DB_HOST', 'localhost' );
	
	$con = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);