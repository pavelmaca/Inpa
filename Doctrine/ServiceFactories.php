<?php

namespace Inpa\Doctrine;

use Nette;
use Inpa;

/**
 * Description of ServiceFactories
 *
 * @author Pavel Máca <maca.pavel@gmail.com>
 */
class ServiceFactories
{

	/**
	 *
	 * @param Nette\DI\Container $container
	 * @param string $databaseConfigKey
	 * @param mixed $entityDirs
	 * @param string $proxyDir
	 * @param string $proxyNamespace
	 * @param array $listeners
	 * @return Container 
	 */
	public static function createServiceDoctrine(Nette\DI\Container $container, $databaseConfigKey, $entityDirs, $proxyDir, $proxyNamespace = "\App\Models\Proxy", $listeners = array())
	{
		$doctrine = new Container($container);
		$doctrine->params["config"] = (string) $databaseConfigKey;
		$doctrine->params["entityDirs"] = (array) $entityDirs;
		$doctrine->params["proxyDir"] = (string) $proxyDir;
		$doctrine->params["proxyNamespace"] = (string) $proxyNamespace;
		$doctrine->params["listeners"] = (array) $listeners;
		return $doctrine;
	}
	
	/**
	 *
	 * @param Nette\DI\Container $container
	 * @param mixed $service
	 * @param string $entityManagerName
	 * @return SchemaPanel
	 */
	public static function registerSchemaPanel(Nette\DI\Container $container, $service, $entityManagerName = "entityManager"){
		if($service instanceof Nette\DI\IContainer && $service->hasService($entityManagerName)){
			$entityManager = $service->getService($entityManagerName);
		}elseif( $service instanceof \Doctrine\ORM\EntityManager){
			$entityManager = $service;
		}else{
			throw new \Nette\InvalidArgumentException("Argument \$service must be instance of Nette\DI\IContainer with service '$entityManagerName' or instance of Doctrine\ORM\EntityManager");
		}
		
		return SchemaPanel::register($entityManager);
	}

}