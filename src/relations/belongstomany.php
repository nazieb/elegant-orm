<?php namespace Elegant\Relations;

use Elegant\Model;
use Elegant\QueryBuilder;

class BelongsToMany extends Relation {

	protected $pivot_builder;

	function __construct(Model $parent, Model $related, $pivot_builder, $foreign_key, $other_key)
	{
		parent::__construct($parent, $related);

		$this->pivot_builder = $pivot_builder;

		$pivot_query = $this->pivot_builder->where($foreign_key, $this->parent->getData( $this->parent->getPrimaryKey() ))->get();

		$other_id = array();

		foreach($pivot_query->result_array() as $row)
		{
			$other_id[] = $row[ $other_key ];
		}

		if(!empty($other_id)) $this->related->where_in( $this->related->getPrimaryKey(), $other_id );
	}

	function getResults()
	{
		return $this->related->get();
	}

}