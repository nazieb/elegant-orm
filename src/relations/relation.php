<?php namespace Elegant\Relations;

use Countable;
use ArrayIterator;
use IteratorAggregate;
use Elegant\Model;
use Elegant\Result;
use Elegant\Row;

abstract class Relation implements Countable, IteratorAggregate {

	protected $parent;
	protected $related;

	protected $related_items = null;

	function __construct(Model $parent, Model $related)
	{
		$this->parent = $parent;
		$this->related = $related;
	}

	abstract public function getResults();

	// Implements IteratorAggregate function
	public function getIterator()
	{
		if(empty($this->related_items)) $this->related_items = $this->related->get();

		return $this->related_items;
	}

	// Implements Countable function
	public function count()
	{
		if(empty($this->related_items)) $this->related_items = $this->related->get();

		return count($this->related_items);
	}

	function __call($name, $param)
	{
		if(is_callable( array($this->related, $name) ))
		{
			$return = call_user_func_array(array( $this->related, $name ), $param);

			if($return instanceof Result or $return instanceof Row) return $return;

			return $this;
		}
	}

}