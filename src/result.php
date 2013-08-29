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
		$row = $this->query->row_array();
		return new Row($this->model, $row);
	}

	function rows()
	{
		$class = get_class($this->model);
		$this->rows = array();

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