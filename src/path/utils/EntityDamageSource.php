<?php

namespace path\utils;

use path\entity\Entity;

class EntityDamageSource extends DamageSource{

	protected $damageSourceEntity;
	private $isThornsDamage = false;

	public function __construct(Entity $damageSourceEntityIn){
		parent::__construct(DamageSource::CAUSE_ENTITY_ATTACK);
		$this->damageSourceEntity = $damageSourceEntityIn;
	}

	public function getEntity() : ?Entity{
		return $this->damageSourceEntity;
	}
}