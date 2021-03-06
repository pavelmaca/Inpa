<?php

namespace Inpa\Doctrine\Loaders;

use Symfony;


/**
 * @author Filip Procházka
 */
class SymfonyLoader
{

	/** @var array */
	private static $registered = FALSE;



	/**
	 * @param string|NULL $namespace
	 * @return Kdyby\Loaders\SymfonyLoader
	 */
	public static function register()
	{
		if (self::$registered) {
			throw SymfonyLoaderException::alreadyRegistered();
		}

		require_once LIBS_DIR . '/Symfony/Component/ClassLoader/UniversalClassLoader.php';

		$symfonyLoader = self::$registered[] = new Symfony\Component\ClassLoader\UniversalClassLoader();
		$symfonyLoader->registerNamespaces(array(
			'Symfony' => LIBS_DIR,
		));
		$symfonyLoader->register();

		return new self;
	}

}



/**
 * @author Filip Procházka
 */
class SymfonyLoaderException extends \Exception
{

	/**
	 * @return Kdyby\Loaders\SymfonyLoaderException
	 */
	public static function alreadyRegistered()
	{
		return new self("Cannot register, already registered loader for Symfony");
	}

}
