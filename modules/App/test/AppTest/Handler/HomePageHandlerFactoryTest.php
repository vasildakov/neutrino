<?php

declare(strict_types=1);

namespace App\test\AppTest\Handler;

use App\test\AppTest\InMemoryContainer;
use Mezzio\Router\RouterInterface;
use Mezzio\Template\TemplateRendererInterface;
use App\Handler\HomePageHandler;
use App\Handler\HomePageHandlerFactory;
use PHPUnit\Framework\TestCase;

final class HomePageHandlerFactoryTest extends TestCase
{
    public function testFactoryWithoutTemplate(): void
    {
        $container = new InMemoryContainer();
        $container->setService(RouterInterface::class, $this->createMock(RouterInterface::class));

        $factory  = new HomePageHandlerFactory();
        $homePage = $factory($container);

        self::assertInstanceOf(HomePageHandler::class, $homePage);
    }

    public function testFactoryWithTemplate(): void
    {
        $container = new InMemoryContainer();
        $container->setService(RouterInterface::class, $this->createMock(RouterInterface::class));
        $container->setService(TemplateRendererInterface::class, $this->createMock(TemplateRendererInterface::class));

        $factory  = new HomePageHandlerFactory();
        $homePage = $factory($container);

        self::assertInstanceOf(HomePageHandler::class, $homePage);
    }
}
