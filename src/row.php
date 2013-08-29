<?php namespace Elegant;

use Elegant\Helper as Helper;

class Row {

	protected $model = null;

	function __construct(Model $model, $rowObject)
	{
		$this->model = $model;

		$this->model->exists = true;
		$this->model->data = $rowObject;
	}

	// Getter
	function __get($field)
	{
		// Are we trying to get a related model?
		if(method_exists($this->model, $field))
			return call_user_func(array($this->model, $field));

		return $this->model->$field;
	}

	// Setter
	function __set($field, $value)
	{
		$this->model->$field = $value;
	}

	function __call($name, $arguments)
	{
		if(method_exists($this->model, $name))
			return call_user_func_array(array($this->model, $name), $arguments);
	}

	function save()
	{
		return $this->model->save();
	}

	function delete()
	{
		return $this->model->delete();
	}

}