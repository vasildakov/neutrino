<?php

declare(strict_types=1);

namespace Neutrino\Domain\Analytics;

use Doctrine\ORM\EntityRepository;

/**
 * @extends EntityRepository<AnalyticsEvent>
 */
class AnalyticsRepository extends EntityRepository
{
    public function aggregateVisitsByCountry(): array
    {
        return $this->createQueryBuilder('e')
            ->select('e.country.name AS country')
            ->addSelect('e.country.isoCode AS isoCode')
            ->addSelect('COUNT(e.id) AS visits')
            ->groupBy('e.country.name')
            ->addGroupBy('e.country.isoCode')
            ->orderBy('visits', 'DESC')
            ->getQuery()
            ->getArrayResult();
    }

    public function aggregateVisitsByBrowser(): array
    {
        return $this->createQueryBuilder('e')
            ->select('e.browser AS browser')
            ->addSelect('COUNT(e.id) AS visits')
            ->where('e.browser IS NOT NULL')
            ->groupBy('e.browser')
            ->orderBy('visits', 'DESC')
            ->getQuery()
            ->getArrayResult();
    }


    public function aggregateVisitsByCityForMap(?\DateTimeImmutable $since = null): array
    {
        $qb = $this->createQueryBuilder('e')
            ->select('e.city AS city')
            ->addSelect('e.country AS country')
            ->addSelect('e.latitude AS lat')
            ->addSelect('e.longitude AS lon')
            ->addSelect('COUNT(e.id) AS visits')
            ->where('e.city IS NOT NULL')
            ->andWhere('e.latitude IS NOT NULL')
            ->andWhere('e.longitude IS NOT NULL')
            ->groupBy('e.city, e.country, e.latitude, e.longitude')
            ->orderBy('visits', 'DESC');

        if ($since !== null) {
            $qb->andWhere('e.occurredAt >= :since')
                ->setParameter('since', $since);
        }

        // Normalize types for JSON consumers
        $rows = $qb->getQuery()->getArrayResult();

        foreach ($rows as &$r) {
            $r['visits'] = (int) $r['visits'];
            // lat/lon are DECIMAL -> strings in Doctrine; keep numeric strings or cast:
            $r['lat'] = (float) $r['lat'];
            $r['lon'] = (float) $r['lon'];
        }

        return $rows;
    }

    //Bounce Rate
    //SELECT session_id
    //FROM analytics_events
    //GROUP BY session_id
    //HAVING COUNT(*) = 1;

    //Average Session Duration
    //SELECT
    //session_id,
    //TIMESTAMPDIFF(SECOND, MIN(occurred_at), MAX(occurred_at)) AS duration
    //FROM analytics_events
    //GROUP BY session_id;

    //Conversion Rate
    //SELECT COUNT(DISTINCT session_id)
    //FROM analytics_events
    //WHERE path = '/checkout/success';
}
