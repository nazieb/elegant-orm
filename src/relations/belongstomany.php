<?php namespace Elegant\Relations;

use Elegant\Model;
use Elegant\QueryBuilder;

class BelongsToMany extends Relation {

	protected $pivot_builder;
	protected $pivot_result;

	protected $foreign_key;
	protected $other_key;

	function __construct(Model $parent, Model $related, $pivot_builder, $foreign_key, $other_key)
	{
		parent::__construct($parent, $related);

		$this->pivot_builder = $pivot_builder;
		$this->foreign_key = $foreign_key;
		$this->other_key = $other_key;
	}

	function setJoin()
	{
		if( $this->eagerLoading )
			$pivot_query = $this->pivot_builder->where_in($this->foreign_key, $this->eagerKeys)->get();

		else
			$pivot_query = $this->pivot_builder->where($this->foreign_key, $this->parent->getData( $this->parent->getPrimaryKey() ))->get();

		$other_id = array();

		$this->pivot_result = $pivot_query->result_array();
		foreach($this->pivot_result as $row)
		{
			$other_id[] = $row[ $this->other_key ];
		}

		$other_id = array_unique($other_id);

		if(!empty($other_id)) return $this->related->where_in( $this->related->getPrimaryKey(), $other_id );
	}

	function match(Model $parent)
	{
		$return = array();

		foreach($this->eagerResults as $row)
		{
			foreach($this->pivot_result as $pivot_row)
			{
				if(
					$parent->getData( $parent->getPrimaryKey() ) == $pivot_row[ $this->foreign_key ] and
					$row->getData( $row->getPrimaryKey() ) == $pivot_row[ $this->other_key ]
				)
				{
					$return[] = $row;
					break;
				}
			}
		}

		return $return;
	}

	function getResults()
	{
		if(empty($this->join)) $this->join = $this->setJoin();

		return $this->join->get();
	}

}