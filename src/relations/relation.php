<?php namespace Elegant\Relations;

use Countable;
use ArrayIterator;
use IteratorAggregate;
use EmptyIterator;
use Elegant\Model;
use Elegant\Result;
use Elegant\Row;

abstract class Relation implements Countable, IteratorAggregate {

	protected $parent;
	protected $related;

	protected $join;

	protected $eagerLoading = false;
	protected $eagerKeys;
	protected $eagerResults;

	function __construct(Model $parent, Model $related)
	{
		$this->parent = $parent;
		$this->related = $related;
	}

	abstract public function getResults();
	abstract public function setJoin();
	abstract public function match(Model $parent);

	function eagerLoad( $parent_rows, $related_keys, $relation )
	{
		$this->eagerLoading = true;
		$this->eagerKeys = (array) $related_keys;

		foreach($parent_rows as $i => $row)
		{
			$row->setRelation($relation, $this);

			$parent_rows[ $i ] = $row;
		}

		return $parent_rows;
	}

	function relate(Model $parent)
	{
		if(empty($this->eagerResults))
		{
			if(empty($this->join)) $this->join = $this->setJoin();

			$this->eagerResults = $this->join->get();
		}

		return $this->match($parent);
	}

	// Implements IteratorAggregate function so the result can be looped without needs to call get() first.
	public function getIterator()
	{
		$return = $this->getResults();

		return ($return instanceof Result) ? $return : new EmptyIterator;
	}

	// Implements Countable function
	public function count()
	{
		$result = $this->getResults();

		return ($result instanceof Result) ? count( $this->getResults() ) : 0;
	}

	// Chains with Active Record method if available
	function __call($name, $param)
	{
		if(is_callable( array($this->related, $name) ))
		{

			if(empty($this->join))
			{
				$parent_data = $this->parent->getData();

				// If parent data is empty then it means we are eager loading.
				if(!empty($parent_data))
					$this->join = $this->setJoin();

				// No need to generate the "join", it will be generated later with eager loading method
				else
					$this->join = $this->related;
			}

			$return = call_user_func_array(array( $this->join, $name ), $param);

			if($return instanceof Result or $return instanceof Row) return $return;

			elseif($name == 'get') return new EmptyIterator;

			return $this;
		}
	}

}