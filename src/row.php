<?php namespace Elegant;

use Elegant\Helper as Helper;

class Row {

	protected $model = null;

	function __construct(Model $model, $rowObject)
	{
		$this->model = $model;

		$this->model->exists = true;
		$this->model->setData( $rowObject );
	}

	// Getter
	function __get($field)
	{
		// Are we trying to get a related model?
		if(method_exists($this->model, $field))
		{
			// Is it eager loaded?
			$related = $this->model->getRelation($field);
			if($related !== null) return $related;

			$relation = call_user_func(array($this->model, $field));

			$data = $relation->getResults();
			return $data ?: array();
		}

		return $this->model->$field;
	}

	// Setter
	function __set($field, $value)
	{
		$this->model->$field = $value;
	}

	function __isset($field)
	{
		return !empty($this->model->$field);
	}

	function __call($name, $arguments)
	{
		if(method_exists($this->model, $name))
			return call_user_func_array(array($this->model, $name), $arguments);
	}

	function __toString()
	{
		$json = array();

		foreach($this->model->getData() as $field => $value)
			$json[$field] = $this->{$field};

		return $json;
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