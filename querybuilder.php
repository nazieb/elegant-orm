<?php namespace Elegant;
class QueryBuilder {

	protected $db_conn = null;

	function __construct($db_group, $table)
	{
		$ci =& get_instance();

		$this->db_conn = $ci->load->database($db_group, true);
		$this->db_conn->from( $table );
	}

	function __call($name, $arguments)
	{
		if(!method_exists($this->db_conn, $name)) return show_error('Unknown function '.$name, 500);

		return call_user_func_array( array($this->db_conn, $name), $arguments );
	}

}