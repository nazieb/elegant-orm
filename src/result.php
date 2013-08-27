<?php namespace Elegant;

use Elegant\Row;

class Result {

	protected $model = null;
	protected $query = null;

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
		$rows = array();

		foreach($this->query->result_array() as $rowData)
		{
			$model = new $class;
			$model->exists = true;

			$newRow = new Row($model, $rowData);
			$rows[] = $newRow;
		}

		return $rows;
	}

	function first()
	{
		return $this->row();
	}

}