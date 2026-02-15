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

namespace Neutrino\Handler\Consent;

use Doctrine\ORM\EntityManagerInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Neutrino\Domain\Consent\ConsentPurpose;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function array_map;

final class ConsentConfigHandler implements RequestHandlerInterface
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $purposes = $this->em->getRepository(ConsentPurpose::class)->findAll();

        $out = array_map(static fn(ConsentPurpose $p) => [
            'code'        => $p->code(),
            'title'       => $p->title(),
            'description' => $p->description(),
            'required'    => $p->required(),
            'version'     => $p->version(),
        ], $purposes);

        return new JsonResponse(['purposes' => $out]);
    }
}
