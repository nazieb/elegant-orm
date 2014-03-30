<?php namespace Elegant;

use Countable;
use ArrayIterator;
use IteratorAggregate;
use Elegant\Row;

class Result implements Countable, IteratorAggregate {

	protected $model = null;
	protected $query = null;

	protected $rows;

	function __construct(Model $model, QueryBuilder $query = null)
	{
		$this->model = $model;
		$this->query = $query->get();
	}

	function row()
	{
		if($this->query->num_rows() == 0) return;

		$row = $this->query->row_array();
		return new Row($this->model, $row);
	}

	function rows()
	{
		$this->rows = array();
		if($this->query->num_rows() == 0) return $this;

		$class = get_class($this->model);

		foreach($this->query->result_array() as $rowData)
		{
			$model = new $class;
			$model->exists = true;

			$newRow = new Row($model, $rowData);
			$this->rows[] = $newRow;
		}

		return $this;
	}

	// Alias for row();
	function first()
	{
		return $this->row();
	}

	function pluck($field)
	{
		$first = $this->row();

		return $first->$field;
	}

	// Eager loading
	function load($method)
	{
		if(!is_callable(array($this->model, $method))) return false;

		$relation = call_user_func(array($this->model, $method));

		$primaries = array();

		foreach($this->rows as $row)
			$primaries[] = $row->getData( $row->getPrimaryKey() );

		$this->rows = $relation->eagerLoad( $this->rows, $primaries, $method );
	}

	function toArray()
	{
		$array = array();

		foreach($this->rows as $row) $array[] = $row->toArray();

		return $array;
	}

	function json()
	{
		return json_encode( $this->toArray() );
	}

	// Implements IteratorAggregate function
	public function getIterator()
	{
		return new ArrayIterator($this->rows);
	}

	// Implements Countable function
	public function count()
	{
		return count($this->rows);
	}
}
