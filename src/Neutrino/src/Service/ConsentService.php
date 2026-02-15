<?php

declare(strict_types=1);

namespace Neutrino\Service;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Neutrino\Consent\CookieSigner;
use Neutrino\Domain\Consent\ConsentEvent;
use Neutrino\Domain\Consent\ConsentPurpose;
use Ramsey\Uuid\Uuid;

use function hash;
use function time;

final readonly class ConsentService
{
    public function __construct(
        private EntityManagerInterface $em,
        private CookieSigner $signer,
        private string $cookieName = 'neutrino_consent'
    ) {
    }

    /**
     * @param array<string,bool> $purposes
     */
    public function buildAndSignPayload(string $visitorId, array $purposes): string
    {
        $payload = [
            'v'        => 1,
            'ts'       => time(),
            'vid'      => $visitorId,
            'purposes' => $purposes,
        ];

        return $this->signer->sign($payload);
    }

    /**
     * Append-only log for proof (do not update).
     *
     * @param array<string,bool> $purposes
     */
    public function recordEvents(
        string $subjectType,
        string $subjectId,
        array $purposes,
        string $source,
        ?string $ip,
        ?string $userAgent
    ): void {
        $ipHash = $ip ? hash('sha256', $ip) : null;

        /** @var list<ConsentPurpose> $defs */
        $defs     = $this->em->getRepository(ConsentPurpose::class)->findAll();
        $versions = [];
        foreach ($defs as $d) {
            $versions[$d->code()] = $d->version();
        }

        foreach ($purposes as $code => $granted) {
            $event = new ConsentEvent(
                id: Uuid::uuid4()->toString(),
                subjectType: $subjectType,
                subjectId: $subjectId,
                purposeCode: (string) $code,
                granted: (bool) $granted,
                purposeVersion: (int) ($versions[$code] ?? 1),
                source: $source,
                occurredAt: new DateTimeImmutable(),
                ipHash: $ipHash,
                userAgent: $userAgent,
                meta: []
            );

            $this->em->persist($event);
        }

        $this->em->flush();
    }

    public function cookieName(): string
    {
        return $this->cookieName;
    }
}
