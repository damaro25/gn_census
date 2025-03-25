<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Presentiel;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Presentiel>
 *
 * @method Presentiel|null find($id, $lockMode = null, $lockVersion = null)
 * @method Presentiel|null findOneBy(array $criteria, array $orderBy = null)
 * @method Presentiel[]    findAll()
 * @method Presentiel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PresentielRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Presentiel::class);
    }

    public function add(Presentiel $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Presentiel $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function buildDataTable($columns, $orders, User $sp)
    {
        $qb =  $this->createQueryBuilder('a')
            ->where('a.supervisor = :sp')
            ->setParameter('sp', $sp);

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

        return $qb
            ->orderBy('a.dayAt', 'ASC')
            ->getQuery();
    }
}
