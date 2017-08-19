<?php
	session_start();

//	define('BASEDIR', '/home/a8891895/public_html');
//
//	require_once(BASEDIR.'/libs/lib_mysql.php');
//	require_once(BASEDIR.'/session.php');
//
//    	$conn = new mysql_connection('a8891895_zbyna', 'mysql6.000webhost.com', 'a8891895_zbyna', 'slavenka');
//    	$conn->get_instance();
//    	$conn->open();
        

        define('BASEDIR', __DIR__);

	require_once(BASEDIR.'/libs/lib_mysql.php');
	require_once(BASEDIR.'/session.php');

        $conn = new mysql_connection('local', '127.0.0.1', 'root', '');
//        $conn =  new mysqli('127.0.0.1', 'root', '', 'local');
//        if ($conn->connect_errno) {
//           echo "Failed to connect to MySQL: " . $mysqli->connect_error;
//        }
    	$conn->get_instance();
    	$conn->open();