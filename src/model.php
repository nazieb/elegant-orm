<?php namespace Elegant;

use Elegant\QueryBuilder;
use Elegant\Result;
use Elegant\Helper;

class Model {
	protected $ci = null;

	protected $db_group = "default";
	protected $table = "";

	protected $primary = "id";
	protected $incrementing = true;

	protected $queryBuilder = null;

	public $exists = false;

	public $data = array();

	function __construct(array $newData = array())
	{
		$this->ci =& get_instance();

		$this->data = is_array($newData) ? $newData : array();

		// Reset variable
		$this->exists = false;
		$this->queryBuilder = null;
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
		$builder = $this->queryBuilder ?: $this->newQuery();

		$result = new Result( $this, $builder );
		return $result->first();
	}

	protected function find($id)
	{
		$builder = $this->newQuery();
		$builder->where( array($this->primary => $id) );

		$result = new Result( $this, $builder );
		return $result->first();
	}

	protected static function create($data)
	{
		if(!is_array($data) or empty($data)) return false;

		$class = new static($data);
		$class->save();

		return $class;
	}

	protected function update($data)
	{
		if( empty($this->queryBuilder) )
		{
			$param = func_get_args();
			if(count($param) < 1) return false;

			@list($data, $where) = $param;

			return $this->newQuery()->update($data, $where);
		}

		else return $this->queryBuilder->update($data);
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

	protected function delete()
	{
		if( !$this->exists and empty($this->queryBuilder) )
		{
			$params = func_get_args();
			if(empty($params)) return false;

			$first = reset($params);
			if(is_array($first)) $params = $first;

			$where = array();

			foreach($params as $id)
			{
				if(is_array($id)) continue;
				$where[] = $id;
			}

			$builder = $this->newQuery();

			if(count($where) <= 1)
				$builder->where($this->primary, reset($where));

			else
				$builder->where_in($this->primary, $where);

			return $builder->delete();
		}

		if( $this->exists ) $this->where($this->primary, $this->data[ $this->primary ]);

		$this->queryBuilder->delete();
	}

	// ======================================
	// Magic Methods
	// ======================================

	public function __call($name, $arguments)
	{
		// Check if the method is available in this model
		if(method_exists($this, $name))
			return call_user_func_array( array($this, $name), $arguments );

		// Check if the method is a "scope" method
		// Read documentation about scope method
		$scope = "scope" . Helper::studlyCase($name);

		if(method_exists($this, $scope))
		{
			array_unshift($arguments, $this);

			return call_user_func_array( array($this, $scope), $arguments );
		}


		if(is_null( $this->queryBuilder )) $this->queryBuilder = $this->newQuery();

		if(is_callable( array($this->queryBuilder, $name) ))
		{
			$return = call_user_func_array( array($this->queryBuilder, $name), $arguments );
			return $this;
		}

		return show_error('Unknown function '.$name, 500);
	}

	public static function __callStatic($name, $arguments)
	{
		$model = get_called_class();

		return call_user_func_array( array(new $model, $name), $arguments );
	}

	function __get($field)
	{
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
}