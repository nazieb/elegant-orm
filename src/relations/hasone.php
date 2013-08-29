<?php namespace Elegant\Relations;

use Elegant\Model;

class HasOne extends Relation {

	function __construct(Model $parent, Model $related, $foreign_key)
	{
		parent::__construct($parent, $related);

		$this->related->where($foreign_key, $this->parent->getData( $this->parent->getPrimaryKey() ));
	}

	function getResults()
	{
		return $this->related->first();
	}

}