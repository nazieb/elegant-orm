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

	function __construct(Model $parent, Model $related)
	{
		$this->parent = $parent;
		$this->related = $related;
	}

	abstract public function getResults();

	// Implements IteratorAggregate function so the result can be looped without needs to call get() first.
	public function getIterator()
	{
		return $this->getResults();
	}

	// Implements Countable function
	public function count()
	{
		return count( $this->getResults() );
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