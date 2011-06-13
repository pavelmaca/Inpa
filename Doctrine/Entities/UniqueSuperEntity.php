<?php

namespace Inpa\Doctrine\Entities;

/*
 * @MappedSuperclass
 */
abstract class UniqueSuperEntity extends \Nette\Object
{
	/**
	 * @Id 
	 * @Column(type="integer")
	 * @GeneratedValue
	 * @var int
	 */
	protected $id;

	/**
	 * @return int 
	 */
	public function getId()
	{
		return $this->id;
	}

}