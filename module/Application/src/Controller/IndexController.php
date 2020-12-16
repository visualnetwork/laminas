<?php

/**
 * @see       https://github.com/laminas/laminas-mvc-skeleton for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc-skeleton/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc-skeleton/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Interop\Container\ContainerInterface;
use Doctrine\ORM\Tools\SchemaTool;

class IndexController extends AbstractActionController
{
	
	protected $serviceLocator;
	
	public function __construct(ContainerInterface $container)
	{
		$this->serviceLocator=$container;
	}
	
	public function indexAction()
	{
		
		
		$entityManager = $this->serviceLocator->get('Doctrine\ORM\EntityManager');
		
		// Így kell frissíteni a módosult táblát. Be lehet állítani, hogy DEV módban ezt megtegye
		
		$tool = new SchemaTool($entityManager);
		$class=$entityManager->getMetadataFactory()->getAllMetadata();
		$tool->updateSchema($class,true);
		
		
		return new ViewModel();
	}
}
