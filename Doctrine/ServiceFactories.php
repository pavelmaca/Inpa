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
	
	public static function registerSchemaPanel(Nette\DI\Container $container, Container $doctrine){
		return SchemaPanel::register($doctrine);
	}

}