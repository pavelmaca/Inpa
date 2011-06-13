<?php

namespace Inpa\Doctrine;

use Nette;
use Doctrine;

/**
 * Description of Locator
 *
 * @author Pavel Máca <maca.pavel@gmail.com>
 * 
 * @property-read \Doctrine\ORM\EntityManager $entityManager
 */
class Container extends Nette\DI\Container
{

	public function __construct(Nette\DI\Container $context)
	{
		$this->addService("context", $context);
	}

	/*	 * ******************* ServiceFactories ******************* */

	/**
	 * @return Cache
	 */
	protected function createServiceCache()
	{
		return new Cache($this->context->cacheStorage);
	}

	/**
	 * @return \Inpa\Doctrine\Panels\DebugPanel
	 */
	protected function createServiceLogger()
	{
		return \Inpa\Doctrine\Panels\DebugPanel::register();
	}

	/**
	 * @return \Doctrine\DBAL\Event\Listeners\MysqlSessionInit
	 */
	protected function createServiceMysqlSessionInitListener()
	{
		$confName = $this->params['config'];
		$database = $this->context->params[$confName];

		return new \Doctrine\DBAL\Event\Listeners\MysqlSessionInit($database['charset']);
	}

	/**
	 * @service-param entityDirs
	 * @service-param proxyDir
	 * @return \Doctrine\ORM\Configuration
	 */
	protected function createServiceConfiguration()
	{
		$config = new \Doctrine\ORM\Configuration;

		// Cache
		$config->setMetadataCacheImpl($this->hasService('metadataCache') ? $this->metadataCache : $this->cache);
		$config->setQueryCacheImpl($this->hasService('queryCache') ? $this->queryCache : $this->cache);

		// Metadata
		$dirs = $this->params['entityDirs'];
		$config->setMetadataDriverImpl($config->newDefaultAnnotationDriver($dirs));

		// Proxies
		$config->setProxyDir($this->params['proxyDir']);
		$config->setProxyNamespace($this->params['proxyNamespace']);
		if ($this->context->params['productionMode']) {
			$config->setAutoGenerateProxyClasses(FALSE);
		} else {
			if ($this->hasService('logger')) {
				$config->setSQLLogger($this->logger);
			}
			$config->setAutoGenerateProxyClasses(TRUE);
		}

		return $config;
	}

	/**
	 * @return \Doctrine\Common\EventManager
	 */
	protected function createServiceEventManager()
	{
		$evm = new \Doctrine\Common\EventManager;
		foreach ($this->params['listeners'] as $listener) {
			$evm->addEventSubscriber($this->getService($listener));
		}

		return $evm;
	}

	/**
	 * @return \Doctrine\ORM\EntityManager
	 */
	protected function createServiceEntityManager()
	{
		$confName = $this->params['config'];
		$database = $this->context->params[$confName];

		$evm = $this->eventManager;
		if (key_exists('driver', $database) && $database['driver'] == "pdo_mysql" && key_exists('charset', $database)) {
			$evm->addEventSubscriber($this->mysqlSessionInitListener);
		}

		$this->freeze();
		return \Doctrine\ORM\EntityManager::create((array) $database, $this->configuration, $evm);
	}

}