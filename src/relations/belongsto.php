<?php namespace Elegant\Relations;

use Elegant\Model;

class BelongsTo extends Relation {

	protected $foreign_key;

	function __construct(Model $parent, Model $related, $foreign_key)
	{
		parent::__construct($parent, $related);

		$this->foreign_key = $foreign_key;
	}

	function setJoin()
	{
		if( $this->eagerLoading )
			return $this->related->where_in($this->related->getPrimaryKey(), $this->eagerKeys);

		else
			return $this->related->where($this->related->getPrimaryKey(), $this->parent->getData( $this->foreign_key ));
	}

	function match(Model $parent)
	{
		foreach($this->eagerResults as $row)
		{
			if($parent->{$this->foreign_key} == $row->getData( $row->getPrimaryKey() ))
				return $row;
		}
	}

	function getResults()
	{
		if(empty($this->join)) $this->join = $this->setJoin();

		return $this->join->first();
	}

}