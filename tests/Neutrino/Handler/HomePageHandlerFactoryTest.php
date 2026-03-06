<?php

declare(strict_types=1);

namespace NeutrinoTest\Handler;

use Mezzio\Router\RouterInterface;
use Mezzio\Template\TemplateRendererInterface;
use Neutrino\Handler\Home\HomePageHandler;
use Neutrino\Handler\Home\HomePageHandlerFactory;
use NeutrinoTest\InMemoryContainer;
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
