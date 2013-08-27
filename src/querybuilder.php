<?php namespace Elegant;
class QueryBuilder {

	protected $db_conn = null;
	protected $table = '';

	function __construct($db_group, $table)
	{
		$ci =& get_instance();

		$this->db_conn = $ci->load->database($db_group, true);

		$this->table = $table;
		$this->db_conn->from( $this->table );
	}

	function __call($name, $arguments)
	{
		if(!method_exists($this->db_conn, $name)) return show_error('Unknown function '.$name, 500);

		return call_user_func_array( array($this->db_conn, $name), $arguments );
	}

	function insert($data)
	{
		$insert = $this->db_conn->insert( $this->table, $data );
		return ($insert !== false) ? $this->db_conn->insert_id() : false;
	}

	function update($data, $where)
	{
		$update = $this->db_conn->update( $this->table, $data, $where );
		return $update;
	}
}