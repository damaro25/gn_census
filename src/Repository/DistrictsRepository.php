<?php

namespace App\Repository;

use App\Entity\Districts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Districts>
 *
 * @method Districts|null find($id, $lockMode = null, $lockVersion = null)
 * @method Districts|null findOneBy(array $criteria, array $orderBy = null)
 * @method Districts[]    findAll()
 * @method Districts[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DistrictsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Districts::class);
    }

    public function add(Districts $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Districts $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


    function getApplicationsTracking($code)
    {
        $qb =  $this->createQueryBuilder('district');

        if ($code == 'gambia') {
            // $qb = $qb->orderBy('u.fdcode', 'ASC');

            $qb->select(
                'lga.name AS lalga, 
                district.name, 
                district.fdcode,
                COUNT(u.id) as nbrCandidacies,
                COALESCE(SUM(district.nb_enum_expected), 0) as nbrExpected'
            )
                ->leftJoin('district.applications', 'u')
                ->leftJoin('district.lga', 'lga')
                ->groupBy('lalga')
                ->addGroupBy('district.name')
                ->addGroupBy('district.fdcode')
                ->orderBy('district.fdcode', 'ASC');
        } else if (strlen($code) == 1) {
            // $qb = $qb
            //     ->select('u.name')
            //     ->where('u.fdcode LIKE :val')
            //     ->setParameter('val', $code . '%')
            //     ->groupBy('u.fdcode')
            //     ->orderBy('u.fdcode', 'ASC');

                $qb->select(
                    'lga.name AS lalga,
                    district.id,
                    district.name, district.fdcode,
                    COUNT(u.id) as nbrCandidacies'
                )
                    ->leftJoin('district.applications', 'u')
                    ->leftJoin('district.lga', 'lga')
                    ->andWhere('district.lga.code = :codeLga')
                    ->setParameter('codeLga', $code)
                    ->orderBy('district.fdcode', 'ASC')
                    ->groupBy('district.id')
                    ->addGroupBy('lalga')
                    ->addGroupBy('district.name')
                    ->addGroupBy('district.fdcode');    
        }

        return $qb->getQuery()->getResult();
    }



    /**
     * @return Districts[] Returns an array of Districts objects
     */
    public function findLgaDist($lga)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.fdcode LIKE :val')
            ->setParameter('val', $lga . "%")
            ->orderBy('s.fdcode', 'ASC')
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return Districts[] Returns an array of Districts objects
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

//    public function findOneBySomeField($value): ?Districts
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
