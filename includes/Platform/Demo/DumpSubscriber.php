<?php
declare(strict_types=1);

// src/Platform/Demo/DumpSubscriber.php
namespace Contexis\Events\Platform\Demo;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DumpSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        // Ich höre auf "HelloWorldEvent" und rufe dann "onSayHello" auf
        return [
            HelloWorldEvent::class => 'onSayHello'
        ];
    }

    public function onSayHello(HelloWorldEvent $event): void
    {
        echo "<pre style='background:yellow; padding:20px; font-size: 20px;'>";
        echo "EVENT EMPFANGEN! 🚀\n";
        var_dump($event->text);
        echo "</pre>";
    }
}