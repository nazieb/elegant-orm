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

	protected $per_page = 20;

	protected $queryBuilder = null;
	protected $pagingBuilder = null;

	public $exists = false;

	protected $data = array();

	// To stored loaded relation
	protected $relations = array();

	function __construct(array $newData = array())
	{
		$this->ci =& get_instance();

		if(is_array($newData)) $this->setData( $newData );

		// Reset variable
		$this->exists = false;
		$this->queryBuilder = null;
	}

	protected function newQuery()
	{
		return new QueryBuilder($this->db_group, $this->table);
	}

	protected function query()
	{
		$this->newQuery();

		return $this;
	}

	protected function all($columns = array())
	{
		$builder = $this->newQuery();

		if(!empty($columns)) $builder->select($columns);

		$result = new Result( $this, $builder );
		return $result->rows();
	}

	protected function get($columns = array())
	{
		if(is_null( $this->queryBuilder )) return $this->all($columns);

		if(!empty($columns)) $this->queryBuilder->select($columns);

		$this->pagingBuilder = clone $this->queryBuilder;

		$result = new Result( $this, $this->queryBuilder );
		return $result->rows();
	}

	protected function first($columns = array())
	{
		$builder = $this->queryBuilder ?: $this->newQuery();

		if(!empty($columns)) $builder->select($columns);

		$result = new Result( $this, $builder );
		return $result->first();
	}

	protected function find($id)
	{
		$args = func_get_args();
		if(count($args) > 1)
		{
			$id = array();
			foreach($args as $arg) $id[] = $arg;
		}

		$builder = $this->newQuery();

		if(is_array($id))
			$builder->where_in($this->primary, $id);

		else
			$builder->where( array($this->primary => $id) );

		$result = new Result( $this, $builder );
		return is_array($id) ? $result->rows() : $result->first();
	}

	protected function pluck($field)
	{
		$row = $this->first( array($field) );

		return $row->$field;
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
					$this->setData( $this->primary, $builder->insert_id() );
			}

			return $return;
		}
		else
		{
			$where = array($this->primary => $this->getData( $this->primary ));

			return $builder->update($this->getData(), $where);
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

		if( $this->exists ) $this->where($this->primary, $this->getData( $this->primary ));

		$this->queryBuilder->delete();
	}

	protected function paging($page, $per_page = null)
	{
		$page = intval($page);
		if(empty($page) or $page < 0) $page = 1;

		if(empty($per_page)) $per_page = $this->per_page;

		$offset = ($page - 1) * $per_page;

		if(!$this->queryBuilder)
			$this->queryBuilder = $this->newQuery();

		$this->queryBuilder->limit($per_page, $offset);
	}

	protected function for_page($page, $per_page = null)
	{
		$this->paging($page, $per_page);

		return $this->get();
	}

	protected function paginate($per_page = 20, $uri_key = 'page', $link_suffix = '')
	{
		$per_page = intval($per_page);
		if($per_page <= 0) $per_page = 20;

		$uri_segment = null;
		$uri_array = $this->ci->uri->segment_array();

		foreach($uri_array as $i => $segment_name)
		{
			if($uri_key == $segment_name)
			{
				$uri_segment = $i;
				break;
			}
		}

		$is_odd = (!empty($uri_segment) and $uri_segment % 2 == 0);

		$uri = $this->ci->uri->uri_to_assoc( (!$is_odd ? 1 : 2) );
		unset($uri[$uri_key]);

		if(count($uri) == 1 and reset($uri) == false)
		{
			$key = reset( array_keys($uri) );
			$uri[ $key ] = 'index';
		}

		$this->ci->config->load('pagination', TRUE);
		$config = $this->ci->config->item('pagination');

		$builder = $this->pagingBuilder ?: ($this->queryBuilder ?: $this->newQuery());
		$builder->offset(false);

		$base_url = $this->ci->uri->assoc_to_uri($uri).'/'.$uri_key;
		if($is_odd) $base_url = $this->ci->uri->segment(1) . '/' . $base_url;

		$config['base_url'] = site_url( $base_url );
		$config['per_page'] = $per_page;
		$config['total_rows'] = $builder->count_all_results();
		$config['uri_segment'] = $uri_segment + 1;

		$this->ci->load->library('pagination', $config);

		$links = $this->ci->pagination->create_links();

		if(!empty($link_suffix))
			$links = preg_replace('/'.$uri_key.'\/([0-9]+)?/', '${0}'.$link_suffix, $links);

		return $links;
	}

	function getPrimaryKey()
	{
		return $this->primary;
	}

	function getData($field = null)
	{
		return !empty($field) ? $this->data[ $field ] : $this->data;
	}

	function setData($field, $value = null)
	{
		if(func_num_args() == 1 and is_array($field))
		{
			foreach($field as $key => $value)
				$this->data[ $key ] = $value;
		}

		else
			$this->data[ $field ] = $value;
	}

	function toArray()
	{
		$array = $this->data;

		foreach($this->relations as $relation => $models)
		{
			foreach($models as $model)
				$array[ $relation ][] = $model->toArray();
		}

		return $array;
	}

	function json()
	{
		return json_encode( $this->toArray() );
	}

	// ======================================
	// Relationship Methods
	// ======================================

	function hasOne($related, $foreign_key = null)
	{
		if(empty($foreign_key))
			$foreign_key = strtolower(get_called_class()) . '_id';

		return new Relations\HasOne($this, new $related, $foreign_key);
	}

	function hasMany($related, $foreign_key = null)
	{
		if(empty($foreign_key))
			$foreign_key = strtolower(get_called_class()) . '_id';

		return new Relations\HasMany($this, new $related, $foreign_key);
	}

	function belongsTo($related, $foreign_key = null)
	{
		if(empty($foreign_key))
			$foreign_key = strtolower($related) . '_id';

		return new Relations\BelongsTo($this, new $related, $foreign_key);
	}

	function belongsToMany($related, $pivot_table = null, $foreign_key = null, $other_key = null)
	{
		if(empty($pivot_table))
		{
			$models = array( strtolower( get_called_class() ), strtolower( $related ) );
			sort($models);

			$pivot_table = strtolower( implode('_', $models) );
		}

		if(empty($foreign_key))
			$foreign_key = strtolower(get_called_class()) . '_id';

		if(empty($other_key))
			$other_key = strtolower($related) . '_id';

		$pivot_builder = new QueryBuilder($this->db_group, $pivot_table);

		return new Relations\BelongsToMany($this, new $related, $pivot_builder, $foreign_key, $other_key);
	}

	function setRelation($name, Relations\Relation $relation)
	{
		$this->relations[ $name ] = $relation->relate( $this );
	}

	function getRelation($name)
	{
		return isset( $this->relations[ $name ] ) ? $this->relations[ $name ] : null;
	}

	// Eager loading for a single row? Just call the method
	function load($related)
	{
		if(!method_exists($this, $related)) return false;

		$this->setRelation( $related, $this->$related() );
	}

	// ======================================
	// Aggregate Methods
	// ======================================

	protected function aggregates($function, $field)
	{
		if(empty($this->queryBuilder))
			$this->queryBuilder = $this->newQuery();

		$this->queryBuilder->select($function.'(`'.$field.'`) as aggr');

		$result = $this->first();
		return $result->aggr;
	}

	protected function max($field)
	{
		return $this->aggregates(__FUNCTION__, $field);
	}

	protected function min($field)
	{
		return $this->aggregates(__FUNCTION__, $field);
	}

	protected function avg($field)
	{
		return round( $this->aggregates(__FUNCTION__, $field), 2);
	}

	protected function sum($field)
	{
		return $this->aggregates(__FUNCTION__, $field);
	}

	protected function count($field = null)
	{
		if (empty($field)) $field = $this->getPrimaryKey();

		return $this->aggregates(__FUNCTION__, $field);
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
			call_user_func_array( array($this->queryBuilder, $name), $arguments );
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

		$accessor = "getAttr". Helper::camelCase( $field );

		return method_exists($this, $accessor) ? call_user_func(array($this, $accessor), $value, $this) : $value;
	}

    function __set($field, $value)
    {
		$mutator = "setAttr". Helper::camelCase( $field );

		if( method_exists($this, $mutator) )
			$value = call_user_func(array($this, $mutator), $value, $this);

		$this->setData( $field, $value );
    }

	function __isset($field)
	{
		return !empty($this->data[ $field ]);
	}
}