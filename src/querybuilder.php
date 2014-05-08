<?php

namespace Elegant;

class QueryBuilder {

    protected $db_conn = null;
    protected $table = '';

    function __construct($db_group, $table) {
        global $__CI;
        if (!isset($__CI)) {
            $__CI =& get_instance();
        }


        $this->db_conn = $__CI->db;

        $this->table = $table;
        $this->db_conn->from($this->table);

//        if (defined('ELEGANT_DEBUG') and ELEGANT_DEBUG === true) {
////            $property_name = 'elegant_db_' . rand();
////            $ci->asd = $this->db_conn;
//        }
    }

    function __call($name, $arguments) {
        if (!method_exists($this->db_conn, $name))
            return show_error('Unknown function ' . $name, 500);

        return call_user_func_array(array($this->db_conn, $name), $arguments);
    }

    function select($columns = array()) {
        if (empty($columns))
            $columns = '*';

        elseif (is_array($columns))
            $columns = implode(', ', $columns);

        $this->db_conn->select($columns);
    }

    function insert($data) {
        $insert = $this->db_conn->insert($this->table, $data);
        return ($insert !== false) ? $this->db_conn->insert_id() : false;
    }

    function update($data, $where = array()) {
        if (empty($where) and ! is_array($where))
            $where = array();

        $update = $this->db_conn->update($this->table, $data, $where);
        return $update;
    }

}
