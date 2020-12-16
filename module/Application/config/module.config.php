<?php

/**
 * @see       https://github.com/laminas/laminas-mvc-skeleton for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc-skeleton/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc-skeleton/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Application;

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Application\Factory\IndexControllerFactory;

return [
    'router' => [
        'routes' => [
            'home' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'application' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/application[/:action]',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
        ],
    ],
	'service_manager' => [
		'abstract_factories' => [
			'Laminas\Cache\Service\StorageCacheAbstractServiceFactory',
			'Laminas\Log\LoggerAbstractServiceFactory',
		],
		'aliases' => [
			'translator' => 'MvcTranslator',
			'my_memcached_alias' => 'doctrine.cache.mycache',
		],	
		'factories' => [
			\Laminas\I18n\Translator\TranslatorInterface::class => \Laminas\I18n\Translator\TranslatorServiceFactory::class,
			'doctrine.cache.my_memcache' => function() {
				$cache = new \Doctrine\Common\Cache\MemcachedCache();
				$memcached = new \Memcached();
				$memcached->addServer('localhost',11211);
				$cache->setMemcached($memcached);
				return $cache;
			} 
		],
	],
	'translator' => [
		'locale' => 'hu_HU',
		'translation_file_patterns' => [
				[
				'type'     => 'gettext',
				'base_dir' => __DIR__ . '/../language',
				'pattern'  => '%s.mo',
				],
		],
	],
    'controllers' => [
        'factories' => [
            Controller\IndexController::class => IndexControllerFactory::class,
        ],
    ],
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => [
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
    // helper
	'view_helpers' => [
		'invokables' => [
        	'translate' => 'Laminas\I18n\View\Helper\Translate'
        ]
	],	
	// doctrine
	'doctrine' => [
		'driver' => [
			'application_driver' => [
				'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
				'cache' => 'array',
				'paths' => [
					__DIR__ . '/../../../vendor/vnw/Entity',  						
				]
			],
			'orm_default' => [
				'drivers' => [
					'vnw' => 'application_driver',
				],
			],
		],
		'configuration' => [
			'orm_default' => [
				'metadata_cache'    => 'array',
				'query_cache'       => 'my_memcache',
				'result_cache'      => 'my_memcache',
				'hydration_cache'	=> 'my_memcache',
			],
		],			
	],
];
