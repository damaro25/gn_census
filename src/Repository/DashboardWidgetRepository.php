<?php

namespace App\Repository;

use App\Entity\DashboardWidget;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DashboardWidget>
 *
 * @method DashboardWidget|null find($id, $lockMode = null, $lockVersion = null)
 * @method DashboardWidget|null findOneBy(array $criteria, array $orderBy = null)
 * @method DashboardWidget[]    findAll()
 * @method DashboardWidget[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DashboardWidgetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DashboardWidget::class);
    }

    public function add(DashboardWidget $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DashboardWidget $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return DashboardWidget[] Returns an array of DashboardWidget objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?DashboardWidget
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }


public function  buildAllDataTable($columns, $orders, $status = NULL)
{

    $qb =  $this->createQueryBuilder('a');


    if ($status != NULL) {
        $qb->andWhere('a.actif = :status')
            ->setParameter('status', $status);
    }

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
    return $qb->getQuery();
}

}
