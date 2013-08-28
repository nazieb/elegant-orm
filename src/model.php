<?php namespace Elegant;

use Elegant\QueryBuilder;
use Elegant\Result;

class Model {
	protected $ci = null;

	protected $db_group = "default";
	protected $table = "";

	protected $primary = "id";
	protected $incrementing = true;

	protected $queryBuilder = null;

	public $exists = false;

	public $data = array();

	function __construct()
	{
		$this->ci =& get_instance();

		// Reset variable
		$this->data = array();
		$this->exists = false;
		$this->queryBuilder = null;
	}

	public function __call($name, $arguments)
	{
		if(method_exists($this, $name))
			return call_user_func_array( array($this, $name), $arguments );

		if(is_null( $this->queryBuilder )) $this->queryBuilder = $this->newQuery();

		if(is_callable( array($this->queryBuilder, $name) ))
			return call_user_func_array( array($this->queryBuilder, $name), $arguments );

		return show_error('Unknown function '.$name, 500);
	}

	public static function __callStatic($name, $arguments)
	{
		$model = get_called_class();

		return call_user_func_array( array(new $model, $name), $arguments );
	}

	function __get($field)
	{
		if($field == 'query')
		{
			return $this->newQuery();
		}

		if(!isset( $this->data[ $field ] )) return null;
		$value = $this->data[ $field ];
		return $value;

		$accessor = "getAttr". Helper::camelCase( $field );

		return method_exists($this, $accessor) ? call_user_func(array($this, $accessor), $value) : $value;
	}

	function __set($field, $value)
	{
		$this->data[ $field ] = $value;
	}

	protected function newQuery()
	{
		return new QueryBuilder($this->db_group, $this->table);
	}

	protected function all()
	{
		$result = new Result( $this, $this->newQuery() );
		return $result->rows();
	}

	protected function get()
	{
		if(is_null( $this->queryBuilder )) return $this->all();

		$result = new Result( $this, $this->queryBuilder );
		return $result->rows();
	}

	protected function first()
	{
		$result = new Result( $this, $this->newQuery() );
		return $result->first();
	}

	protected function find($id)
	{
		$builder = $this->newQuery();
		$builder->where( array($this->primary => $id) );

		$result = new Result( $this, $builder );
		return $result->first();
	}

	protected function save()
	{
		if(empty($this->data)) return false;

		$builder = $this->newQuery();

		// Do an insert statement
		if(!$this->exists)
		{
			if( !$this->incrementing and empty( $this->data[ $this->primary ] ) ) return false;

			$return = $builder->insert( $this->data );

			if($return !== false)
			{
				$this->exists = true;

				if( $this->incrementing )
					$this->data[ $this->primary ] = $builder->insert_id();
			}

			return $return;
		}
		else
		{
			$where = array($this->primary => $this->data[ $this->primary ]);

			return $builder->update($this->data, $where);
		}
	}
}