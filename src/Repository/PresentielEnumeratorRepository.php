<?php

namespace App\Repository;

use App\Entity\Presentiel;
use App\Entity\PresentielEnumerator;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<PresentielEnumerator>
 *
 * @method PresentielEnumerator|null find($id, $lockMode = null, $lockVersion = null)
 * @method PresentielEnumerator|null findOneBy(array $criteria, array $orderBy = null)
 * @method PresentielEnumerator[]    findAll()
 * @method PresentielEnumerator[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PresentielEnumeratorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PresentielEnumerator::class);
    }

    public function add(PresentielEnumerator $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PresentielEnumerator $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function buildDataTable($columns, $orders, Presentiel $presentiel)
    {
        $qb =  $this->createQueryBuilder('a')
            ->leftJoin('a.enumeratorClassroom', 'enumeratorClassroom')
            ->leftJoin('enumeratorClassroom.enumerator', 'enumerator')
            ->where('a.presentiel = :pr')
            ->setParameter('pr', $presentiel);

        if ($columns != null && count($columns) > 0) {
            $exprOr = [];
            $i = 0;
            foreach ($columns as $column) {

                $data = $column['data'];
                $value = $column['search']['value'];
                if ($value == NULL || !$value) {
                    continue;
                }
                ++$i;
                $exprOr[] = $qb->expr()->like(strpos($data, ".") == FALSE ? "a.$data" : $data, ":" . str_replace('.', '', $data));
                $qb->setParameter(str_replace('.', '', $data), '%' . $value . '%');
            }
            if ($i > 0) {
                $qb->andWhere($qb->expr()->orX(...$exprOr));
            }
        }

        if ($orders != null && count($orders) > 0) {
            foreach ($orders as $order) {
                $colName = $columns[$order['column']]['data'];

                if (strpos($colName, ".") == FALSE) {
                    $qb->orderBy("a.$colName", strtolower($order['dir']));
                } else {
                    $qb->orderBy("$colName", strtolower($order['dir']));
                }
            }
        }

        return $qb
            ->orderBy('enumerator.name', 'ASC')
            ->getQuery();
    }
}
