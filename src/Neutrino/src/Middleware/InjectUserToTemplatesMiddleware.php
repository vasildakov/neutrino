<?php

declare(strict_types=1);
/*
 * This file is part of Neutrino.
 *
 * (c) Vasil Dakov <vasildakov@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Neutrino\Middleware;

use Mezzio\Authentication\UserInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final readonly class InjectUserToTemplatesMiddleware implements MiddlewareInterface
{
    public function __construct(private TemplateRendererInterface $templates)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = $request->getAttribute(UserInterface::class);

        // Makes $user available in *all* templates
        $this->templates->addDefaultParam(TemplateRendererInterface::TEMPLATE_ALL, 'user', $user);

        return $handler->handle($request);
    }
}
