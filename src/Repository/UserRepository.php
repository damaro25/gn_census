<?php

namespace App\Repository;

use App\Entity\Departements;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(User $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(User $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    /**
     * @return User[] Returns an array of User objects
     */
    public function findUserByRoles($role)
    {

        return $this->createQueryBuilder('u')
            ->andWhere('JSON_GET_TEXT(u.roles, 0) LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $role . '%')
            ->addOrderBy('u.username', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function buildDataTable($columns, $orders, $role = NULL, $userName = NULL)
    {
        $qb =  $this->createQueryBuilder('a')
            ->leftJoin('a.lga', 'lga');

        if (!empty($role)) {
            $qb = $qb->where('JSON_GET_TEXT(a.roles, 0) LIKE :searchTerm')
                ->setParameter('searchTerm', '%' . $role . '%');
        }

        if ($userName != NULL) {
            $qb->andWhere('a.username = :login')
                ->setParameter('login', $userName);
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

    function findNextSpLogin()
    {
        return $this->createQueryBuilder('u')
            ->select('MAX(SUBSTRING(u.username, 3, 4)) as email')
            ->andWhere('u.username LIKE :sp')
            ->andWhere('JSON_GET_TEXT(u.roles, 0) LIKE :roles')
            ->setParameter('roles', '%ROLE_SUPERVISOR%')
            ->setParameter('sp', 'SP%')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
