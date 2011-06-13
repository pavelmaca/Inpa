<?php

namespace Inpa\Doctrine\Entities;

/**
 * @Entity(repositoryClass="Inpa\Doctrine\Repositories\UserRepository")
 * @Table(name="users")
 */
class UserEntity extends UniqueSuperEntity
{
	/**
	 * @Column(type="string", unique=true)
	 * @var string
	 */
	private $login;
	
	/**
	 * @Column(type="string")
	 * @var string
	 */
	private $password;
	
	/**
	 * @Column(type="string")
	 * @var string
	 */
	private $salt;

	/**
	 * @return string
	 */
	public function getLogin()
	{
		return $this->login;
	}

	/**
	 *
	 * @param string $login
	 * @return UserEntity 
	 */
	public function setLogin($login)
	{
		$this->login = $login;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPassword()
	{
		return $this->password;
	}

	/**
	 * @param string $password
	 * @return UserEntity 
	 */
	public function setPassword($password)
	{
		$this->password = $this->_hash($password);
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getRoles()
	{
		return NULL;
	}

	/**
	 * @param string $password
	 * @return string
	 */
	public function verifyPassword($password)
	{
		return $this->_hash($password) === $this->getPassword();
	}

	/*	 * ******************* Tools ******************* */

	/**
	 * @param string $password
	 * @return string 
	 */
	protected function _hash($password)
	{
		$hash = md5('=x8U' . $this->getSalt() . $password . sha1("U" . $this->getSalt() . $password . "2") . '2i');
		return $hash;
	}

	/**
	 * @return string
	 */
	protected function getSalt()
	{
		return isset($this->salt) ? $this->salt : $this->generateSalt();
	}

	/**
	 * @return string
	 */
	protected function generateSalt()
	{
		$alfabetic = "abcdefghijklmnopqrstuvwxyz";
		$alfabetic_len = strlen($alfabetic);
		$special = ".-\/*-+$@#=";
		$special_len = strlen($special);

		$salt = "";
		for ($i = 0; $i < 8; $i++) {
			$type = rand(0, 2);
			switch ($type) {
				case 0:
					$salt .= substr($alfabetic, rand(0, $alfabetic_len), 1);
					break;
				case 1:
					$salt .= rand(0, 99);
					break;
				case 2:
					$salt .= substr($special, rand(0, $special_len), 1);
					break;
			}
		}
		return $this->salt = md5($salt);
	}

}