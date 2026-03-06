<?php

namespace Contexis\Events\Platform;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class EventDispatcherFactory
{

	/**
     * @param string[] $subscriberClasses Eine Liste von Klassennamen (Strings)
     */
    public function __construct(
        private readonly array $subscriberClasses
    ) {}

	public function __invoke(ContainerInterface $container): EventDispatcherInterface
	{
		$dispatcher = new EventDispatcher();

		foreach ($this->subscriberClasses as $subscriberClass) {
            $subscriber = $container->get($subscriberClass);
            $dispatcher->addSubscriber($subscriber);
        }
        
		return $dispatcher;
	}
}
