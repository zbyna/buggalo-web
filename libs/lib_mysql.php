<?php
// $Header: /var/lib/cvs/morsmad/libs/lib_mysql.php,v 1.2 2011-08-08 17:44:40 tommy Exp $

define('DB_NOCONNECTION', -1000);

/**
 * This class handles the database connection as well as the execution of the queries.
 * The class mostly wraps php mysql_* functions, but extends them in certain cases.
 * For example it simulates the ocibindbyname functionality.
 *
 * @author	tommy
 * @version	$Revision: 1.2 $
 */
class mysql_connection {
	var $connection;
	var $database;
	var $server;
	var $username;
	var $password;
	var $last_query;
	var $session_errors; // Any errors this session?

	// error handling
	var $error_handler;
	var $last_error;

	// for statistical purposes only
	var $number_of_queries;
	var $total_time_used;

	function __construct($database, $server, $username, $password) {
		$this->database = $database;
		$this->server = $server;
		$this->username = $username;
		$this->password = $password;
		
		$this->session_errors = false;
		$this->number_of_queries = 0;
		$this->total_time_used = 0;
		
		$this->get_instance();
	}

	function &get_instance() {
		static $instance;
		if(!isset($instance))
			$instance = $this;
		return $instance;
	}

	function open() {
		$this->connection =  mysqli_connect($this->server, $this->username, $this->password, $this->database);
		if($this->connection) {
			mysqli_select_db( $this->connection,$this->database);
			return true;
		} else {
			if(is_callable($this->error_handler))
				call_user_func($this->error_handler, $this, DB_NOCONNECTION);
			return false;
		}
	}

	function close() {
		mysqli_close($this->connection);
	}

	function fetch($query, $bind = NULL, $res_type = MYSQLI_ASSOC) {
		$this->number_of_queries++;

		$query = $this->_bind($query, $bind);
		$this->last_query = $query;

		$start_time = $this->_microtime();
		$res = mysqli_query( $this->connection, $query );
		$this->total_time_used += $this->_microtime() - $start_time;
		
		if($res) {
			$rows = array();
			while( $row =  mysqli_fetch_array($res, $res_type) )
				$rows[] = $row;
			mysqli_free_result($res);
		} else {
			$rows = false;
		}
		
		$this->session_errors |= (bool) $this->is_error();
		return $rows;
	}

	function fetch_row($query, $bind = NULL, $res_type = MYSQLI_ASSOC) {
		$this->number_of_queries++;

		$query = $this->_bind($query, $bind);
		$this->last_query = $query;

		$start_time = $this->_microtime();
		$res = mysqli_query( $this->connection, $query );
		$this->total_time_used += $this->_microtime() - $start_time;
		
		if($res) {
			$row = mysqli_fetch_array($res, $res_type);
			mysqli_free_result($res);
		} else {
			$row = false;
		}

		$this->session_errors |= (bool) $this->is_error();
		return $row;
	}

	function execute($query, $bind = NULL) {
		$this->number_of_queries++;

		$query = $this->_bind($query, $bind);
		$this->last_query = $query;

		$start_time = $this->_microtime();
		$res = mysqli_query( $this->connection, $query );
		$this->total_time_used += $this->_microtime() - $start_time;
		
		$affected = mysqli_affected_rows();

		$this->session_errors |= (bool) $this->is_error();
		return ($affected > 0);
	}

	function _prepare_string($string) {
		return is_null($string) ? 'NULL' : "'".mysqli_real_escape_string( $this->connection,stripslashes($string))."'";
	}

	function _prepare_numberic($number) {
		return is_null($number) ? 'NULL' : mysqli_real_escape_string( $this->connection,$number);
	}

	function _bind($sql, $bind) {
		if(isset($bind)) {
			foreach($bind as $key => $value) {
				$prepared_value = is_numeric($value) ? $this->_prepare_numberic($value) : $this->_prepare_string($value);
				$sql = str_replace(':'.$key, $prepared_value, $sql);
			}
		}

		return $sql;
	}

	function _microtime() {
		list($usec, $sec) = explode(' ', microtime());
		return (float) $usec + (float) $sec;
	}

	function get_auto_increment_value() {
		return mysqli_insert_id($this->connection);
	}

	function is_error() {
		if(is_callable($this->error_handler) && mysqli_error($this->connection)) {
			$this->last_error = mysqli_error($this->connection);
			call_user_func($this->error_handler, $this, mysqli_errno($this->connection), mysqli_error($this->connection));
			return true;
		} else {
			return false;
		}
	}

	function is_session_errors() {
		return $this->session_errors;
	}

	function get_last_query() {
		return $this->last_query;
	}

	function get_last_error() {
		return $this->last_error;
	}

	function get_number_of_queries() {
		return $this->number_of_queries;
	}

	function get_total_time_used() {
		return $this->total_time_used;
	}

	function set_error_handler($error_handler) {
		$this->error_handler = $error_handler;
	}
}
?>
