<?php
namespace Application\Factory;


use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Application\Controller\IndexController;
use Interop\Container\ContainerInterface;

class IndexControllerFactory implements FactoryInterface
{
	public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
	{				
		$controller = new IndexController($container);
		return $controller;
	}
	
	public function createService(ServiceLocatorInterface $serviceLocator) {
		
	}
	
}