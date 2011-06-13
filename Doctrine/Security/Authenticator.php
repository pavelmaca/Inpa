<?php

namespace Inpa\Doctrine\Security;

use Inpa;
use Nette;
use Doctrine;
use Nette\Security as NS;

/**
 * Users authenticator.
 * 
 * @author     Pavel Máca
 */
class Authenticator extends \Nette\Object implements \Nette\Security\IAuthenticator
{
	/** @var Inpa\Doctrine\Repositories\UserRepository  */
	protected $repository;

	/**
	 * @param ISecuredUsers
	 */
	public function __construct(Inpa\Doctrine\Repositories\UserRepository $repository)
	{
		$this->repository = $repository;
	}

	/*	 * ******************* interface \Nette\Security\IAuthenticator ******************* */

	/**
	 * Performs an authentication
	 * @param  array
	 * @return Nette\Security\Identity
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		list($username, $password) = $credentials;

		$user = $this->repository->findOne($username);

		if (!$user) {
			throw new NS\AuthenticationException("Uživatel '$username' nenalezen.", self::IDENTITY_NOT_FOUND);
		}

		if (!$user->verifyPassword($password)) {
			throw new NS\AuthenticationException("Chybné heslo.", self::INVALID_CREDENTIAL);
		}

		return new NS\Identity($user->getId(), $user->getRoles(), $user);
	}

}
