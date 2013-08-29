<?php namespace Elegant\Relations;

use Elegant\Model;

class BelongsTo extends Relation {

	function __construct(Model $parent, Model $related, $foreign_key)
	{
		parent::__construct($parent, $related);

		$this->related->where($this->related->getPrimaryKey(), $this->parent->getData( $foreign_key ));
	}

	function getResults()
	{
		return $this->related->first();
	}

}