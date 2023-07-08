<?php

namespace App\Repository\Model;

trait Stats
{
    public function countByPeriod(string $period = 'day', \DateTimeInterface $dateLimit= null): array
    {
        switch ($period) {
            case 'month':
                $periodTricks = 7;
                break;
            case 'year':
                $periodTricks = 4;
                break;
            case 'day':
            default:
                $periodTricks = 10;
                break;
        }
        $qb = $this->createQueryBuilder('t')
            ->select("SUBSTRING(t.createdAt, 1, $periodTricks) as period, COUNT(t.id) as count")
            ->groupBy('period')
            ->orderBy('t.createdAt', 'ASC');
        if ($dateLimit) {
            $qb->andWhere('t.createdAt > :dateLimit')
                ->setParameter('dateLimit', $dateLimit);
        }
        return $qb->getQuery()->getResult();
    }
}
