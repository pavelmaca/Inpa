<?php

namespace Inpa\Doctrine\Repositories;

/**
 * Description of UserRepository
 *
 * @author Pavel Máca <maca.pavel@gmail.com>
 */
class UserRepository extends \Doctrine\ORM\EntityRepository
{
	/**
	 *
	 * @param string $username
	 * @return Inpa\Doctrine\Entities\UserEntity
	 */
	public function findOne($username){
		return $this->findOneByLogin($username);
	}

}