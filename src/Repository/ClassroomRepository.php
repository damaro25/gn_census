<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Classroom;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Classroom>
 *
 * @method Classroom|null find($id, $lockMode = null, $lockVersion = null)
 * @method Classroom|null findOneBy(array $criteria, array $orderBy = null)
 * @method Classroom[]    findAll()
 * @method Classroom[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClassroomRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Classroom::class);
    }

    public function add(Classroom $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Classroom $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    function findLastEnumeratorUsername($supUsername)
    {
        return $this->createQueryBuilder('u')
            ->select('MAX(u.username)')
            ->where('u.username LIKE :startLog')
            ->setParameter('startLog', "$supUsername%")
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function buildDatatable($columns, $orders, User $supervisor, $isProfile = NULL)
    {
        $qb =  $this->createQueryBuilder('a')
            ->leftJoin('a.enumerator', 'enumerator')
            ->leftJoin('enumerator.lga', 'lga')
            ->leftJoin('enumerator.district', 'district')
            ->where('a.supervisor = :sp')
            ->setParameter('sp', $supervisor);

        if ($isProfile != NULL) {
            $qb->andWhere('a.isProfile = :isChoosen')
                ->setParameter('isChoosen', $isProfile);
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

    function getSupervisorEnumerator($supervisorUsername, $enuUsername): Classroom
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.supervisor', 'supervisor')
            ->where('supervisor.username = :sp')
            ->andWhere('u.username = :enumerator')
            ->setParameter('sp', $supervisorUsername)
            ->setParameter('enumerator', $enuUsername)
            ->getQuery()
            ->getOneOrNullResult();
    }

    function getSpProfileEnumerator($supervisorUsername, $enuUsername = NULL)
    {
        $qb = $this->createQueryBuilder('u')
            ->leftJoin('u.supervisor', 'supervisor')
            ->where('supervisor.username = :sp')
            ->andWhere('u.isProfile = :isP')
            ->setParameter('sp', $supervisorUsername)
            ->setParameter('isP', 1);

        if ($enuUsername != NULL) {
            $qb->andWhere('u.username = :enumerator')
                ->setParameter('enumerator', $enuUsername);
        }

        return $qb->getQuery()
            ->getResult();
    }

    public function cptInClassroom()
    {
        return $this->createQueryBuilder('a')
            ->select('COUNT(a)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function cptProfiled()
    {
        return $this->createQueryBuilder('a')
            ->select('COUNT(a)')
            ->where('a.isProfile = :isP')
            ->andWhere('a.deleted = :del')
            ->setParameter('isP', 1)
            ->setParameter('del', 0)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
