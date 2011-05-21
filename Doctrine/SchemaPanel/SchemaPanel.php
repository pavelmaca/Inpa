<?php

namespace Inpa\Doctrine;

use Nette;
use Nette\Diagnostics\IBarPanel;
use Nette\Diagnostics\Debugger;
use Nette\Environment;
use Nette\Application\Responses\JsonResponse;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;

	
/**
 * @author David Morávek
 * @author Pavel Máca
 * @link https://github.com/davidmoravek/SchemaPanel
 */
class SchemaPanel implements IBarPanel
{

	/**
	 * @param Nette\DI\IContainer
	 */
	public function __construct(Nette\DI\IContainer $container, $EMserviceName)
	{
		if(!$container->hasService($EMserviceName)){
			throw new Nette\InvalidStateException("Service '$EMserviceName' required for new instance of ". __CLASS__);
		}
		$this->processRequest($container, $EMserviceName);
	}



	/**
	 * IBarPanel
	 *
	 * @return string
	 */
	public function getTab()
	{
		return '<img src="data:image/png;base64,AAABAAEAEBAAAAEACABoBQAAFgAAACgAAAAQAAAAIAAAAAEACAAAAAAAAAEAAAAAAAAAAAAAAAEAAAABAAAAAAAA////ACRo8gCQs/gAWIz1AMzc+wADUvAArsf6AHWg9gA/e/QA5+/9ABNd8QCfvfkAvNH6AGiX9QCDq/cAM3PyAPP3/QDb5/0AG2PxACxt8gBgk/UAw9b7AHyl9wAMWPEARYDzAPn7/gC2zfoAia73AOLr/QCkwPkAO3jzABdg8QCbuvkAH2XyAOvx/QAoa/IAEFrxAI2w+ACAqPcA8PX+AC5w8wC/0/sANnXzAGuZ9gD8/f4As8v6AFWL9QBjk/YAhqz3AApW8AChv/gA9/n+ACFn8gBDfvMA3ej8ACVq8QD1+P0ADlrwABFc8QAVXvEAGWHxAI6y+AB+pvcAeqT3AHOf9gAqbfIALG/yADR18wD+/v4AIGbxADx59ABAfPMAYpT1AHah9wD7/P4A+Pr+APT3/gAEU/AADVnxABJc8AAeZfEAJWnyACZq8gAtb/MALXDyAIWr9wB/p/cAYZL1AP///gD//v8A//7+AP7//wD+//4A/v7/AP3+/gD9/f4A/P7+APv9/gD6/P4A+Pv+APT3/QAOWvEAEFvxABJc8QDq8f0AEl3wABNc8QAWXvEAGGHxABli8QAjaPIAJGnyACdr8gDC1vsALW/yAC5w8gCzzPoAj7L4AI2y+ABik/UAdqH2AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAByJg55DHIAAAAAAAAAAAB2AhNET20rcgAAAAAAAAA/Zj0NARdoUTxyAAAAAAAqIG87HgEBQGgiKXIAAAAALGZDRxByAVlAPDwMAAAAAEgUHV85EUVLAUFOMQAAAAAfcwpFZGVfYwFKBlYAAAAABDt0CVR1WVkPbDw+AAAAACFocCUDWQEcJVITBQAAAAAAGTxGFgF3ZyQ6CAAAAAAAAHI2MlUvblILFQAAAAAAAAAAciFCa3FQSQAAAAAAAAAAAAAbOFNqeAAAAAAAAAAAAAAWPTU7WAAAAAAAAAAAAAAAMxgLMAAAAAAAAAAAAAAAAHInV3IAAAAAAAAAAPgfAADwDwAA4AcAAMADAADAAwAAwAMAAMADAADAAwAAwAMAAOAHAADgDwAA8B8AAPg/AADwfwAA8P8AAPD/AAA=">SchemaTool';
	}



	/**
	 * IBarPanel
	 *
	 * @return string
	 */
	public function getPanel()
	{
		ob_start();
		require_once __DIR__ . '/schema.panel.phtml';
		return ob_get_clean();
	}



	/**
	 * IBarPanel
	 *
	 * @return string
	 */
	public function getId()
	{
		return 'schema-tool';
	}



	/**
	 * Ajax request process
	 * @var Nette\DI\IContainer
	 * @throws Nette\InvalidArgumentException
	 */
	public function processRequest(Nette\DI\IContainer $container, $EMserviceName)
	{
		$request = Environment::getHttpRequest();
		

		if ($request->isPost() && $request->isAjax() && $request->getHeader('X-Schema-Client')) {
			
			$cmd = file_get_contents('php://input', TRUE);
		
			$em = $container->getService($EMserviceName);
			$schemaTool = new SchemaTool($em);
			
			try {
				switch ($cmd) {
					case 'create':
						$em->getMetadataFactory()->getCacheDriver()->deleteAll();
						$metadatas = $em->getMetadataFactory()->getAllMetadata();
						$schemaTool->createSchema($metadatas);
						break;
					case 'update':
						$em->getMetadataFactory()->getCacheDriver()->deleteAll();
						$metadatas = $em->getMetadataFactory()->getAllMetadata();
						$schemaTool->updateSchema($metadatas);
						break;
					case 'drop':
						$metadatas = $em->getMetadataFactory()->getAllMetadata();
						$schemaTool->dropSchema($metadatas);
						break;

					default:
						throw new Nette\InvalidArgumentException('Invalid argument!');
						break;
				}
				$message['text'] = ucfirst($cmd) . ' query was successfully executed';
				$message['cls'] = 'success';
			} catch (\Exception $e) {
	
				$message['text'] = $e->getMessage();
				$message['cls'] = 'error';
			}
			$response = new JsonResponse($message);
			$response->send($request, new Nette\Http\Response());
			exit;
		}
	}



	/**
	 * @param Nette\DI\IContainer
	 * @return SchemaPanel
	 */
	public static function register(Nette\DI\IContainer $container, $EMserviceName = 'entityManager')
	{
		$panel = new static($container, $EMserviceName);		
		Debugger::$bar->addPanel($panel);
		return $panel;
	}

}