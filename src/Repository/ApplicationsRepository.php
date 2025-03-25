<?php

namespace App\Repository;

use App\Entity\Applications;
use App\Entity\Districts;
use App\Entity\Lgas;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Applications|null find($id, $lockMode = null, $lockVersion = null)
 * @method Applications|null findOneBy(array $criteria, array $orderBy = null)
 * @method Applications[]    findAll()
 * @method Applications[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApplicationsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Applications::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Applications $entity, bool $flush = true): void
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
    public function remove(Applications $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return Applications[] Returns an array of Applications objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */


    public function findCandidatAllowToReapply(string $nin, string $submission_number, string $validationCode)
    {
        return $this->createQueryBuilder('c')
            ->where('c.submission_number = :submission_number AND c.nin = :nin AND c.captcha = :captcha')
            ->setParameter('submission_number', $submission_number)
            ->setParameter('nin', $nin)
            ->setParameter('captcha', $validationCode)
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getOneOrNullResult();
    }

    
    public function findCandidatAllowToapply(string $nin, string $submission_number)
    {
        return $this->createQueryBuilder('c')
            ->where('c.submission_number = :submission_number AND c.nin = :nin')
            ->setParameter('submission_number', $submission_number)
            ->setParameter('nin', $nin)
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findCandidatEigible($datedeb = null, $datefin = null, $tranche = null, $mode = null): array
    {
        $bqb = " c.id IS NOT NULL ";
        if ($mode != "aucun") {
            $bqb = " salle.modePaiement = '$mode' ";
        } else {
            $bqb = " salle.modePaiement IS NULL ";
        }

        if ($tranche == 1) {
            return $this->createQueryBuilder('c')
                ->select('c.id, 
                        c.prenom, 
                        c.nom, 
                        reg.nom AS lalga, 
                        dept.nom AS ledistrict, 
                        salle.telephone, 
                        c.nin,            
                        salle.modePaiement AS modeP,           
                        workDistrict.nom AS lacommune,           
                        sup.nom AS supNom,            
                        sup.prenom AS supPrenom ,
                         count(presentiela.id) as nbj           
                    ')
                ->leftJoin('c.lga', 'reg')
                ->leftJoin('c.district', 'dept')
                ->leftJoin('c.workDistrict', 'workDistrict')
                ->join('c.salles', 'salle')
                ->leftJoin('salle.superviseur', 'sup')
                ->join('salle.presentielAgents', 'presentiela')
                ->join('presentiela.presentiel', 'presentiel')
                ->leftJoin('c.etatCandidats', 'etatCandidat')
                ->where('presentiela.payer = :payer')
                ->andWhere('presentiela.isPresent = :isP')
                ->andWhere('presentiela.etatPaiement IS NULL')
                ->andWhere('etatCandidat IS NULL')
                ->andWhere('presentiel.createAt BETWEEN :from AND :to')
                ->andWhere($bqb)
                ->groupBy('
                            c.id, 
                            c.prenom, 
                            c.nom, 
                            reg.nom, 
                            dept.nom, 
                            salle, 
                            c.nin,            
                            salle.modePaiement,           
                            workDistrict,           
                            sup.nom,            
                            sup.prenom
                        ')
                ->having('COUNT(presentiela.id) >= :tranche')
                ->setParameter('payer', 0)
                ->setParameter('isP', 1)
                ->setParameter('from', $datedeb)
                ->setParameter('to', $datefin)
                ->setParameter('tranche', $tranche)
                // ->setParameter('mode', $mode)

                ->getQuery()
                ->getResult();
        } else {
            //dd($bqb);
            // Récupération des Candidat présent durant les trois premier jours
            /*$qBPresent = $this->createQueryBuilder('c1')
            //->select('c1.id')
            ->join('c1.salles', 'salle1')
            ->join('salle1.presentielAgents', 'presentiela1')
            ->join('presentiela1.presentiel', 'presentiel1')
            ->where('presentiela1.etatPaiement IS NOT NULL')

            ->andWhere('presentiel1.createAt BETWEEN :from1 AND :to1')
            ->andWhere('salle1.typeRemplacement != 3 ')  ; */


            $qCpercu = $this->createQueryBuilder('c2')
                ->join('c2.etatCandidats', 'etat')
                ->where('etat.nbjour > 5000');
            //
            //->join('salle2.presentielAgents', 'presentiela2')
            //->join('presentiela2.presentiel', 'presentiel2')

            //->andWhere('presentiel1.createAt BETWEEN :from1 AND :to1')
            //->andWhere('salle1.typeRemplacement != 3 ')  ; 
            //->setParameter('from1', $datedeb)
            //->setParameter('to1', $datefin);
            // ->getQuery();
            //->getResult();

            //dd($qBPresent->getResult());

            return $this->createQueryBuilder('c')
                ->select('c.id, 

                                    c.prenom, 
                                    c.nom, 
                                    reg.nom AS lalga, 
                                    dept.nom AS ledistrict, 
                                    salle.telephone, 
                                    c.nin,            
                                    salle.modePaiement AS modeP,           
                                    workDistrict.nom AS lacommune,           
                                    sup.nom AS supNom,            
                                    sup.prenom AS supPrenom ,
                                    ec.nbjour AS mnt_a_payer,
                                    count(presentiela.id) as nbj           
                                ')
                ->leftJoin('c.lga', 'reg')
                ->leftJoin('c.district', 'dept')
                ->leftJoin('c.workDistrict', 'workDistrict')
                ->join('c.salles', 'salle')
                ->leftJoin('salle.superviseur', 'sup')
                ->join('salle.presentielAgents', 'presentiela')
                ->join('presentiela.presentiel', 'presentiel')
                ->leftJoin('c.etatCandidats', 'ec')
                ->where('presentiela.payer = :payer')
                ->andWhere('presentiela.isPresent = :isP')
                ->andWhere('presentiel.createAt > :from')
                ->andWhere('presentiela.etatPaiement IS NULL')
                ->andWhere('salle.typeRemplacement IN (0,2)')

                ->andWhere($bqb)

                ->groupBy('
                        c.id, 
                        c.prenom, 
                        c.nom, 
                        reg.nom, 
                        dept.nom, 
                        salle, 
                        c.nin,            
                        salle.modePaiement,           
                        workDistrict,           
                        sup.nom,            
                        sup.prenom,
                        ec.nbjour
                        ')
                ->having('COUNT(presentiela.id) >= :tranche')

                ->setParameter('payer', 0)
                ->setParameter('isP', 1)
                ->setParameter('from', $datedeb)
                //->setParameter('to', $datefin)
                //->setParameter('from1', $datedeb)
                //->setParameter('to1', $datefin)
                ->setParameter('tranche', $tranche)

                ->getQuery()
                ->getResult();
        }
    }

    public function findCandidatNoEigible($me, $myProfil, $columns = null, $xls = 0)
    {
        $entityManager = $this->getEntityManager();
        $lga = $me->getDistrict()->getLga()->getId();
        $dep = $me->getDistrict()->getId();
        //dd($dep);
        $bqb = "c.id IS NOT NULL";
        if ($myProfil == "CTD") {
            // $bqb = "AND c.district =  $dep ";
            $bqb = "c.district =  $dep ";
        } else if ($myProfil == "CTR") {
            // $bqb = "AND c.lga =  $lga";
            $bqb = "c.lga =  $lga";
        } else {
            //
        }

        if ($xls == 1) {
            $qb = $this->createQueryBuilder('c')

                ->leftJoin('c.lga', 'reg')
                ->leftJoin('c.district', 'dept')
                ->leftJoin('c.workDistrict', 'workDistrict')
                ->join('c.salles', 'salle')     ///
                ->leftJoin('salle.superviseur', 'sup') ////
                ->join('salle.presentielAgents', 'presentiela')  //////
                ->where('presentiela.etatPaiement is NULL')
                ->andWhere('salle.typeRemplacement != 3')
                ->andwhere($bqb)
                ->getQuery();

            return $qb->getResult();
        } else {
            $qb = $this->createQueryBuilder('c')

                ->leftJoin('c.lga', 'reg')
                ->leftJoin('c.district', 'dept')
                ->leftJoin('c.workDistrict', 'workDistrict')
                ->join('c.salles', 'salle')     ///
                ->leftJoin('salle.superviseur', 'sup') ////
                ->join('salle.presentielAgents', 'presentiela')  //////
                ->where('presentiela.etatPaiement is NULL')
                ->andwhere($bqb);

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
                    $exprOr[] = $qb->expr()->like(strpos($data, ".") == FALSE ? "c.$data" : $data, ":" . str_replace('.', '', $data));
                    $qb->setParameter(str_replace('.', '', $data), '%' . $value . '%');
                }
                if ($i > 0) {
                    $qb->where($qb->expr()->orX(...$exprOr));
                }
            }


            return $qb->getQuery();
        }
    }

    public function findCandidatEigibleE($me, $myProfil, $xls = 0, $columns = null)
    {

        $lga = $me->getDistrict()->getLga()->getId();
        $dep = $me->getDistrict()->getId();
        //dd($dep);
        $bqb = "presentiela.createAt IS NOT NULL";
        if ($myProfil == "CTD") {
            // $bqb = " AND c.district =  $dep ";
            $bqb = "c.district =  $dep ";
        } else if ($myProfil == "CTR") {
            // $bqb = " AND c.lga =  $lga";
            $bqb = "c.lga =  $lga";
            //    ->andWhere('u.district.lga = :reg')
            //    ->setParameter('reg', $me->getDistrict()->getLga())

        } else {
            //
        }




        if ($xls == 1) {
            $qb = $this->createQueryBuilder('c')

                ->leftJoin('c.lga', 'lga')
                ->leftJoin('c.district', 'district')
                ->leftJoin('c.workDistrict', 'workDistrict')
                ->join('c.salles', 'salle')     ///
                ->leftJoin('salle.superviseur', 'sup') ////
                ->join('salle.presentielAgents', 'presentiela')
                ->join('c.etatCandidats', 'etatCandidat') ///
                ->where($bqb)
                ->getQuery();

            return $qb->getResult();
        } else {

            $qb = $this->createQueryBuilder('c')

                ->leftJoin('c.lga', 'lga')
                ->leftJoin('c.district', 'district')
                ->leftJoin('c.workDistrict', 'workDistrict')
                ->join('c.salles', 'salle')     ///
                ->leftJoin('salle.superviseur', 'sup') ////
                ->join('salle.presentielAgents', 'presentiela')
                ->join('c.etatCandidats', 'etatCandidat') ///
                ->where($bqb);

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
                    $exprOr[] = $qb->expr()->like(strpos($data, ".") == FALSE ? "c.$data" : $data, ":" . str_replace('.', '', $data));
                    $qb->setParameter(str_replace('.', '', $data), '%' . $value . '%');
                }
                if ($i > 0) {
                    $qb->where($qb->expr()->orX(...$exprOr));
                }
            }


            return $qb->getQuery();
        }
    }

    public function findCandidatPayer($me, $myProfil, $xls = 0, $columns = null)
    {
        $entityManager = $this->getEntityManager();
        $lga = $me->getDistrict()->getLga()->getId();
        $dep = $me->getDistrict()->getId();
        //dd($dep);
        $bqb = "c.id IS NOT NULL";
        if ($myProfil == "CTD") {
            // $bqb = "AND c.district =  $dep ";
            $bqb = "c.district =  $dep ";
        } else if ($myProfil == "CTR") {
            // $bqb = "AND c.lga =  $lga";
            $bqb = "c.lga =  $lga";
        } else {
            //
        }

        if ($xls == 1) {
            $qb = $this->createQueryBuilder('c')

                ->leftJoin('c.lga', 'reg')
                ->leftJoin('c.district', 'dept')
                ->leftJoin('c.workDistrict', 'workDistrict')
                ->join('c.etatCandidats', 'etat')     ///
                // ->leftJoin('salle.superviseur', 'sup') ////
                // ->join('salle.presentielAgents', 'presentiela')  //////
                // ->join('presentiela.presentiel', 'presentiel')///
                // ->where('presentiela.payer= 1')
                // ->andwhere('presentiela.isPresent=1')
                ->where('etat.noPaye = 1')
                ->andwhere($bqb)
                ->getQuery();




            return $qb->getResult();
        } else {
            $qb = $this->createQueryBuilder('c')

                /*->leftJoin('c.lga', 'reg')
                ->leftJoin('c.district', 'dept')
                ->leftJoin('c.workDistrict', 'workDistrict')            
                ->join('c.salles', 'salle')     ///
                ->leftJoin('salle.superviseur', 'sup') ////
                ->join('salle.presentielAgents', 'presentiela')  //////
                ->join('presentiela.presentiel', 'presentiel')///
                ->where('presentiela.payer= 1')
                ->andwhere('presentiela.isPresent=1')
                ->andwhere($bqb);*/

                ->leftJoin('c.lga', 'reg')
                ->leftJoin('c.district', 'dept')
                ->leftJoin('c.workDistrict', 'workDistrict')
                ->join('c.etatCandidats', 'etat')     ///
                // ->leftJoin('salle.superviseur', 'sup') ////
                // ->join('salle.presentielAgents', 'presentiela')  //////
                // ->join('presentiela.presentiel', 'presentiel')///
                // ->where('presentiela.payer= 1')
                // ->andwhere('presentiela.isPresent=1')
                ->where('etat.noPaye = 1')
                ->andwhere($bqb);

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
                    $exprOr[] = $qb->expr()->like(strpos($data, ".") == FALSE ? "c.$data" : $data, ":" . str_replace('.', '', $data));
                    $qb->setParameter(str_replace('.', '', $data), '%' . $value . '%');
                }
                if ($i > 0) {
                    $qb->where($qb->expr()->orX(...$exprOr));
                }
            }


            return $qb->getQuery();
        }
    }

    public function findCandidatNoPayer($me, $myProfil, $xls = 0, $columns = null)
    {
        $entityManager = $this->getEntityManager();
        $lga = $me->getDistrict()->getLga()->getId();
        $dep = $me->getDistrict()->getId();
        //dd($dep);
        $bqb = "c.id IS NOT NULL";
        if ($myProfil == "CTD") {
            // $bqb = "AND c.district =  $dep ";
            $bqb = "c.district =  $dep ";
        } else if ($myProfil == "CTR") {
            // $bqb = "AND c.lga =  $lga";
            $bqb = "c.lga =  $lga";
        } else {
            //
        }

        if ($xls == 1) {
            $qb = $this->createQueryBuilder('c')

                //->leftJoin('c.lga', 'reg')
                //->leftJoin('c.district', 'dept')
                //->leftJoin('c.workDistrict', 'workDistrict')            
                //->join('c.salles', 'salle')     ///
                //->leftJoin('salle.superviseur', 'sup') ////
                ->join('c.etatCandidats', 'etatCandidat') ///
                ->where('etatCandidat.noPaye = :notp')
                ->andwhere($bqb)
                ->setParameter('notp', 0)
                ->getQuery();

            return $qb->getResult();
        } else {
            $qb = $this->createQueryBuilder('c')

                // ->leftJoin('c.lga', 'reg')
                //->leftJoin('c.district', 'dept')
                //->leftJoin('c.workDistrict', 'workDistrict')            
                // ->join('c.salles', 'salle')     ///
                // ->leftJoin('salle.superviseur', 'sup') ////
                ->join('c.etatCandidats', 'etatCandidat') ///
                ->where('etatCandidat.noPaye = :notp')
                ->andwhere($bqb)
                ->setParameter('notp', 0);

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
                    $exprOr[] = $qb->expr()->like(strpos($data, ".") == FALSE ? "c.$data" : $data, ":" . str_replace('.', '', $data));
                    $qb->setParameter(str_replace('.', '', $data), '%' . $value . '%');
                }
                if ($i > 0) {
                    $qb->where($qb->expr()->orX(...$exprOr));
                }
            }


            return $qb->getQuery();
        }
    }



    public function  buildDataTable($columns, $orders, Districts $district = NULL, $isSelected = NULL, Lgas $lga = NULL, $confirmation = NULL, $posting_district = NULL, $codeDistrictArray = [], $onWaitingList = NULL)
    {

        $qb =  $this->createQueryBuilder('a')
            ->select('a')
            ->leftJoin('a.district', "district")
            ->leftJoin('a.lga', "lga")
            ->leftJoin('a.workDistrict', "workDistrict")
            ->leftJoin('a.temporal_district_residence', "temporal_district_residence");

        if ($isSelected != NULL) {
            $qb = $qb->where('a.isSelected = :status');
        }

        if ($district != NULL) {
            $qb = $qb->andWhere('a.district = :district')
                ->setParameter('district', $district);
        }

        if ($lga != NULL) {
            $qb = $qb->andWhere('lga.code = :lga')
                ->setParameter('lga', $lga->getCode());
        }

        if ($confirmation != NULL) {
            $qb = "NULL" === $confirmation ? $qb->andWhere('a.confirmation IS NULL') : $qb->andWhere('a.confirmation = :confirmation')->setParameter('confirmation', $confirmation);
        }

        if ($onWaitingList != NULL) {
            $qb = $qb->andWhere('a.onWaitingList IS NULL');
        }

        if ($posting_district != NULL) {
            $qb = $qb->andWhere('a.workDistrict = :com')
                ->setParameter('com', $posting_district);
        }

        if ($isSelected != NULL) {
            $qb = $qb->setParameter('status', $isSelected);
        }

        if (count($codeDistrictArray) > 0) {
            $qb = $qb->andWhere('workDistrict.fdcode IN (:codes)')
                ->setParameter('codes', $codeDistrictArray);
        }

        if ($columns != null && !empty($columns)) {
            $exprOr = [];
            $i = 0;
            foreach ($columns as $column) {

                $data = $column['data'];
                $value = $column['search']['value'];
                $value = $value === 'true' ? TRUE : ($value === 'false' ? false :  $value);
                if (($value == NULL || !$value)) { // ignorer recherche pour la colonnes confirmation car elle est deja en parametre de la foction
                    continue;
                }
                ++$i;
                if (\is_bool($value)) {
                    $exprOr[] = $qb->expr()->eq(!strpos($data, ".") ? "a.$data" : $data, ":" . str_replace('.', '', $data));
                    $qb->setParameter(str_replace('.', '', $data),  $value);
                } else {
                    $exprOr[] = $qb->expr()->like(!strpos($data, ".") ? "a.$data" : $data, ":" . str_replace('.', '', $data));
                    $qb->setParameter(str_replace('.', '', $data), '%' . $value . '%');
                }
            }
            if ($i > 0) {
                $qb->andWhere($qb->expr()->orX(...$exprOr));
            }
        }

        if ($orders != null && count($orders) > 0) {
            foreach ($orders as $order) {
                $colName = $columns[$order['column']]['data'];
                if (!strpos($colName, ".")) {
                    $qb->orderBy("a.$colName", strtolower($order['dir']));
                } else {
                    $qb->orderBy("$colName", strtolower($order['dir']));
                }
            }
        }
        return $qb->getQuery();
    }


    public function buildDataTableCopteCoordination($columns, $orders, Districts $district, $codesCacrWork = [])
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.district', "district")
            ->leftJoin('a.workDistrict', "workDistrict")
            // ->leftJoin('a.commune', "commune")
            ->where('a.estCopte = :status')
            ->andWhere('a.district = :depar');

        if (count($codesCacrWork) > 0) {
            $qb = $qb->andWhere('workDistrict.code IN (:codes)')
                ->setParameter('codes', $codesCacrWork);
        }

        $qb = $qb
            ->setParameter('depar', $district)
            ->setParameter('status', 1);

        if ($columns != null && !empty($columns)) {
            $exprOr = [];
            $i = 0;
            foreach ($columns as $column) {

                $data = $column['data'];
                $value = $column['search']['value'];
                $value = $value === 'true' ? TRUE : ($value === 'false' ? false :  $value);
                if (($value == NULL || !$value)) { // ignorer recherche pour la colonnes confirmation car elle est deja en parametre de la foction
                    continue;
                }
                ++$i;
                if (\is_bool($value)) {
                    $exprOr[] = $qb->expr()->eq(!strpos($data, ".") ? "a.$data" : $data, ":" . str_replace('.', '', $data));
                    $qb->setParameter(str_replace('.', '', $data),  $value);
                } else {
                    $exprOr[] = $qb->expr()->like(!strpos($data, ".") ? "a.$data" : $data, ":" . str_replace('.', '', $data));
                    $qb->setParameter(str_replace('.', '', $data), '%' . $value . '%');
                }
            }
            if ($i > 0) {
                $qb->andWhere($qb->expr()->orX(...$exprOr));
            }
        }

        if ($orders != null && count($orders) > 0) {
            foreach ($orders as $order) {
                $colName = $columns[$order['column']]['data'];
                if (!strpos($colName, ".")) {
                    $qb->orderBy("a.$colName", strtolower($order['dir']));
                } else {
                    $qb->orderBy("$colName", strtolower($order['dir']));
                }
            }
        }
        return $qb->getQuery();
    }


    // liste des AR non affecté à un superviseur pour un DEPT.
    public function  buildDataTableARNotAffected($columns, $orders, Districts $district, $arrndCacrsCodes = [], $searchCarc = NULL)
    {
        $qb =  $this->createQueryBuilder('a')
            ->leftJoin('a.district', "district")
            ->leftJoin('a.cav', "cav")
            ->leftJoin('a.workDistrict', "workDistrict")
            ->leftJoin('a.posting_district', "posting_district")
            ->leftJoin('a.commune', "commune")
            ->leftJoin('a.temporal_district_residence', "temporal_district_residence")
            ->where('a.isSelected = :status')
            ->andWhere('a.isAffected = :affected')
            ->andWhere('a.district = :district')
            // ->andWhere('a.confirmation IS NULL OR a.confirmation = :isdispo') // ne prendre que les candidats ayant confirmé leur disponibilité
        ;

        $qb = $qb->setParameter('status', 1)
            ->setParameter('district', $district)
            ->setParameter('affected', 0)
            // ->setParameter('isdispo', 1) // ne prendre que les candidats ayant confirmé leur disponibilité
        ;

        if (count($arrndCacrsCodes) > 0) {
            $qb = $qb->andWhere('workDistrict.code IN (:codes)')
                ->setParameter('codes', $arrndCacrsCodes);
        }

        if ($searchCarc != NULL) {
            $qb = $qb->andWhere('workDistrict.id = :myposting_district')
                ->setParameter('myposting_district', $searchCarc);
        }

        if ($columns != null && !empty($columns)) {
            $exprOr = [];
            $i = 0;
            foreach ($columns as $column) {

                $data = $column['data'];
                $value = $column['search']['value'];
                if ($value == NULL || !$value || !$searchCarc == NULL) {
                    continue;
                }
                ++$i;
                $exprOr[] = $qb->expr()->like(!strpos($data, ".") ? "a.$data" : $data, ":" . str_replace('.', '', $data));
                $qb->setParameter(str_replace('.', '', $data), '%' . $value . '%');
            }
            if ($i > 0) {
                $qb->andWhere($qb->expr()->orX(...$exprOr));
            }
        }

        if ($orders != null && count($orders) > 0) {
            foreach ($orders as $order) {
                $colName = $columns[$order['column']]['data'];
                if (!strpos($colName, ".")) {
                    $qb->orderBy("a.$colName", strtolower($order['dir']));
                } else {
                    $qb->orderBy("$colName", strtolower($order['dir']));
                }
            }
        } else {
            $qb->orderBy("a.scoreEnsae", "DESC");
        }
        return $qb->getQuery();
    }

    // liste des AR non affecté à un superviseur pour un DEPT.
    public function findCcrcaCandidats(CommunesArrCommunautesRurales $commune)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.commune = :posting_district OR a.temporal_district_residence = :posting_district')
            ->andWhere('a.isAffected = :affected')

            ->setParameter('posting_district', $commune)
            ->setParameter('affected', 1)
            ->orderBy("a.nom", "ASC")
            ->getQuery()
            ->getResult();
    }

    function getApplicationsTracking($COD_LGA, $CODE_DISTRICT = NULL)
    {
        $qb =  $this->createQueryBuilder('u');

        if ($COD_LGA != null && $COD_LGA == 'gambia') {
            $qb->select(
                'lga.name AS lalga, 
                district.name, 
                district.fdcode,
                COUNT(u.id) as nbrCandidacies,
                COALESCE(SUM(district.nb_enum_expected), 0) as nbrExpected'
            )
                ->join('u.district', 'district')
                ->leftJoin('district.lga', 'lga')
                ->groupBy('lalga')
                ->addGroupBy('district.name')
                ->addGroupBy('district.fdcode')
                ->orderBy('district.fdcode', 'ASC');
        } else if ($COD_LGA != null && strlen($COD_LGA) == 1) {
            $qb->select(
                'lga.name AS lalga,
                district.id,
                district.name, district.fdcode,
                COUNT(u.id) as nbrCandidacies'
            )
                ->leftJoin('u.district', 'district')
                ->leftJoin('district.lga', 'lga')
                ->andWhere('district.lga.code = :codeLga')
                ->setParameter('codeLga', $COD_LGA)
                ->orderBy('district.fdcode', 'ASC')
                ->groupBy('district.id')
                ->addGroupBy('lalga')
                ->addGroupBy('district.name')
                ->addGroupBy('district.fdcode');
        } else if ($CODE_DISTRICT != null && strlen($CODE_DISTRICT) == 3) {
            $qb->select(
                'district.name, 
                district.fdcode,
                COUNT(u.id) as nbrCandidacies,
                district.nb_enum_expected'
            )
                ->leftJoin('u.district', 'district')
                ->andWhere('district.fdcode LIKE :codDistrict')
                ->setParameter('codDistrict', $CODE_DISTRICT . "%")
                ->orderBy('district.fdcode', 'ASC')
                ->groupBy('district.id')
                ->addGroupBy('district.name')
                ->addGroupBy('district.fdcode');
        }

        return $qb->getQuery()->getResult();
    }

    public function findCandidatsByNumDossiers($dossiers = [])
    {
        return $this->createQueryBuilder("c")
            ->where("c.submission_number IN (:docs)")
            ->setParameter("docs", $dossiers)
            ->getQuery()
            ->getResult();
    }

    public function findCandidatEigibleGlobal(): array
    {
        $entityManager = $this->getEntityManager();

        /*$query = $entityManager->createQuery(
            'SELECT c
            FROM App\Entity\Applications c, App\Entity\Salles s            
            WHERE s.candidat=c.id AND c.isSelected = :slt '
        )->setParameter('slt', 1); */

        $query = $entityManager->createQuery(
            'SELECT c
            FROM App\Entity\Applications c            
            WHERE  c.isSelected = :slt'
        )->setParameter('slt', 1);

        return $query->getResult();
    }

    public function nbrCandidatsRestantListePrincipale($district)
    {
        return $this->createQueryBuilder("c")
            ->select("COUNT(c.id) AS NBR_AR_NOT_AFFECTED_LISTE_PRINCIPALE")
            ->where('c.district = :dept')
            // ->andWhere('c.isSelected = :isSel')
            ->andWhere('c.onWaitingList = :isReser')
            ->andWhere('c.isAffected = :isAffec')
            ->setParameter("dept", $district)
            // ->setParameter("isSel", 1)
            ->setParameter("isReser", 0)
            ->setParameter("isAffec", 0)
            ->getQuery()
            ->getSingleScalarResult();
    }
    public function getCandidatsSheet($isPrincipale = NULL, $onWaitingList = NULL, $codeDistrictArray = [])
    {
        $qb =  $this->createQueryBuilder('a')
            ->leftJoin('a.posting_district', "posting_district")
            ->where('a.posteSouhaite = :poste');

        if ($isPrincipale != NULL) {
            $qb = $qb->andWhere('a.isSelected = :isP')
                ->setParameter('isP', 1);
        }

        if ($onWaitingList != NULL) {
            $qb = $qb->andWhere('a.onWaitingList = :isSecon')
                ->setParameter('isSecon', 1);
        }

        if (count($codeDistrictArray) > 0) {
            $qb = $qb->andWhere('posting_district.code IN (:codes)')
                ->setParameter('codes', $codeDistrictArray);
        }

        $qb = $qb->setParameter('poste', 'Enumeratorss');

        return $qb->getQuery()->getResult();
    }

    public function findUnSelectedCandidats(CommunesArrCommunautesRurales $posting_district, $district = NULL)
    {
        $qb = $this->createQueryBuilder('a')
            ->where('a.onWaitingList IS NULL')
            // ->andWhere('a.estCopte IS NULL')
            ->andWhere('a.posteSouhaite = :poste')
            ->andWhere('a.posting_district = :com')
            ->andWhere('a.isSelected = :status');

        if ($district != NULL) {
            $qb = $qb->andWhere('a.district = :dept')
                ->setParameter('dept', $district);
        }

        return $qb->setParameter('com', $posting_district)
            ->setParameter('poste', 'Enumeratorss')
            ->setParameter('status', '0')

            ->orderBy('a.scoreEnsae', 'DESC')
            // ->orderBy('a.score', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function  buildDataTablePourConfirmer($tabnumDossier)
    {
        $qb =  $this->createQueryBuilder('a')
            ->select('a')
            ->where('a.isSelected = :isSelected')
            ->andWhere('a.confirmation = :conf')
            ->andWhere('a.submission_number IN (:Dossier)')
            ->setParameter('isSelected', 1)
            ->setParameter('conf', 1)
            ->setParameter('Dossier', $tabnumDossier);

        return $qb->getQuery()->getResult();
    }

    // Todo: Récupère la liste des reservistes n'ayant pas fait l'objet de
    public function buildDataTableReserviste($columns, $orders, Districts $district = NULL, $isSelected = NULL, $isAffected = NULL, Lgas $lga = NULL, $confirmation = NULL, $work_district = NULL)
    {
        $qb =  $this->createQueryBuilder('a')
            ->leftJoin('a.district', "district")
            ->leftJoin('a.lga', "lga")
            ->leftJoin('a.workDistrict', "workDistrict")
            ->leftJoin('a.temporal_district_residence', "temporal_district_residence")
            ->andWhere('a.onWaitingList = :status');

        // filtrer les communes si c'est un Arrnd. 

        if ($district != NULL) {
            $qb = $qb->andWhere('a.district = :district');
        }

        if ($lga != NULL) {
            $qb = $qb->andWhere('a.lga = :lga');
        }

        if ($work_district != NULL) {
            $qb = $qb->andWhere('a.workDistrict = :com');
        }

        if ($isAffected != NULL) {
            $qb = $qb->andWhere('a.isAffected = :stat');
        }

        // Parameter
        if ($district != NULL) {
            $qb = $qb->setParameter('district', $district);
        }

        if ($lga != NULL) {
            $qb = $qb->setParameter('lga', $lga);
        }

        if ($work_district != NULL) {
            $qb = $qb->setParameter('com', $work_district);
        }

        $qb = $qb->setParameter('status', $isSelected);

        if ($isAffected != NULL) {
            $qb = $qb->setParameter('stat', $isAffected);
        }

        if ($columns != null && !empty($columns)) {
            $exprOr = [];
            $i = 0;
            foreach ($columns as $column) {

                $data = $column['data'];
                $value = $column['search']['value'];
                if ($value == NULL || !$value) {
                    continue;
                }
                ++$i;
                $exprOr[] = $qb->expr()->like(!strpos($data, ".") ? "a.$data" : $data, ":" . str_replace('.', '', $data));
                $qb->setParameter(str_replace('.', '', $data), '%' . $value . '%');
            }
            if ($i > 0) {
                $qb->andWhere($qb->expr()->orX(...$exprOr));
            }
        }

        if ($orders != null && count($orders) > 0) {
            foreach ($orders as $order) {
                $colName = $columns[$order['column']]['data'];
                if (!strpos($colName, ".")) {
                    $qb->orderBy("a.$colName", strtolower($order['dir']));
                } else {
                    $qb->orderBy("$colName", strtolower($order['dir']));
                }
            }
        } else {
            $qb->orderBy("a.score", "DESC");
        }
        return $qb->getQuery();
    }

    public function  buildDataTableConfirmer($columns, $orders, Districts $district = NULL, $isSelected = NULL, Lgas $lga = NULL, $poste = 'Enumeratorss', $confirmation = NULL, $cav = NULL, $posting_district = NULL, $codeDistrictArray = [], $onWaitingList = NULL)
    {

        $qb =  $this->createQueryBuilder('a')
            ->leftJoin('a.district', "district")
            ->leftJoin('a.lga', "lga")
            ->leftJoin('a.cav', "cav")
            ->leftJoin('a.posting_district', "posting_district")
            ->leftJoin('a.commune', "commune")
            ->leftJoin('a.temporal_district_residence', "temporal_district_residence");

        if ($isSelected != NULL) {
            $qb = $qb->where('a.isSelected = :status');
        }

        if ($district != NULL) {
            $qb = $qb->andWhere('a.district = :district')
                ->setParameter('district', $district);
        }

        if ($lga != NULL) {
            $qb = $qb->andWhere('district.code LIKE :lga')
                ->setParameter('lga', $lga->getCode() . "%");
        }

        if ($poste != NULL) {
            $qb = $qb->andWhere('a.posteSouhaite = :posteSouhaite')
                ->setParameter('posteSouhaite', $poste);
        }

        if ($confirmation != NULL) {
            $qb = "NULL" === $confirmation ? $qb->andWhere('a.confirmation IS NULL') : $qb->andWhere('a.confirmation = :confirmation')->setParameter('confirmation', $confirmation);
        }

        if ($onWaitingList != NULL) {
            $qb = $qb->andWhere('a.onWaitingList IS NULL');
        }

        if ($cav != NULL) {
            $qb = $qb->andWhere('a.cav = :cav')
                ->setParameter('cav', $cav);
        }

        if ($posting_district != NULL) {
            $qb = $qb->andWhere('a.posting_district = :com')
                ->setParameter('com', $posting_district);
        }

        if ($isSelected != NULL) {
            $qb = $qb->setParameter('status', $isSelected);
        }

        if (count($codeDistrictArray) > 0) {
            $qb = $qb->andWhere('posting_district.code IN (:codes)')
                ->setParameter('codes', $codeDistrictArray);
        }

        $qb = $qb->andWhere('a.estCopte IS NULL');

        if ($columns != null && !empty($columns)) {
            $exprOr = [];
            $i = 0;
            foreach ($columns as $column) {

                $data = $column['data'];
                $value = $column['search']['value'];
                $value = $value === 'true' ? TRUE : ($value === 'false' ? false :  $value);
                if (($value == NULL || !$value)  || $data === '11') { // ignorer recherche pour la colonnes confirmation car elle est deja en parametre de la foction
                    continue;
                }
                ++$i;
                if (\is_bool($value)) {
                    $exprOr[] = $qb->expr()->eq(!strpos($data, ".") ? "a.$data" : $data, ":" . str_replace('.', '', $data));
                    $qb->setParameter(str_replace('.', '', $data),  $value);
                } else {
                    $exprOr[] = $qb->expr()->like(!strpos($data, ".") ? "a.$data" : $data, ":" . str_replace('.', '', $data));
                    $qb->setParameter(str_replace('.', '', $data), '%' . $value . '%');
                }
            }
            if ($i > 0) {
                $qb->andWhere($qb->expr()->orX(...$exprOr));
            }
        }

        if ($orders != null && count($orders) > 0) {
            foreach ($orders as $order) {
                $colName = $columns[$order['column']]['data'];
                if (!strpos($colName, ".")) {
                    $qb->orderBy("a.$colName", strtolower($order['dir']));
                } else {
                    $qb->orderBy("$colName", strtolower($order['dir']));
                }
            }
        }
        return $qb->getQuery();
    }

    public function findCandidatEigibleR($code, $datedeb, $datefin, $tranche): array
    {
        /*$entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT c, count(p) as nbj
             FROM App\Entity\Applications c, App\Entity\Salles s, App\Entity\PresentielAgents p
             WHERE s.candidat=c.id AND p.agent=s.id  AND p.payer= :slt AND p.isPresent=1 AND p.etatPaiement is NULL AND c.lga = :reg 
             AND p.createAt BETWEEN :dated AND :datef
             GROUP BY c
             HAVING nbj >= :tranche'
        )->setParameter('slt', 0)
         ->setParameter('reg', $code)
         ->setParameter('dated', $datedeb)
         ->setParameter('datef', $datefin)
         ->setParameter('tranche', $tranche);

        return $query->getResult();*/

        return $this->createQueryBuilder('c')
            ->select('c')
            // ->select(
            //     'c.id, 
            //     c.prenom, 
            //     c.nom, 
            //     reg.nom AS lalga, 
            //     dept.nom AS ledistrict, 
            //     salle.telephone, 
            //     c.nin, 
            //     salle.modePaiement AS modeP,
            //     workDistrict.nom AS lacommune,
            //     sup.nom AS supnom,
            //     sup.prenom AS supprenom
            // '
            // )
            // ->leftJoin('c.lga', 'reg')
            // ->leftJoin('c.district', 'dept')
            // ->leftJoin('c.workDistrict', 'workDistrict')
            // ->leftJoin('salle.superviseur', 'sup')
            ->join('c.salles', 'salle')
            ->join('salle.presentielAgents', 'presentiel')
            ->where('presentiel.payer = :payer')
            ->andWhere('presentiel.isPresent = :isP')
            ->andWhere('presentiel.etatPaiement IS NULL')
            ->andWhere('presentiel.createAt BETWEEN :from AND :to')
            ->andWhere('c.lga = :codeReg')

            ->groupBy('c')
            ->having('COUNT(presentiel.id) >= :tranche')

            ->setParameter('payer', 0)
            ->setParameter('isP', 1)
            ->setParameter('from', $datedeb)
            ->setParameter('to', $datefin)
            ->setParameter('codeReg', $code)
            ->setParameter('tranche', $tranche)

            ->getQuery()
            ->getResult();
    }


    public function findCandidatEigibleR_bis($code, $datedeb, $datefin, $tranche, $mode): array
    {
        $bqb = " c.id IS NOT NULL ";
        if ($mode != "aucun") {
            $bqb = " salle.modePaiement = '$mode' ";
        } else {
            $bqb = " salle.modePaiement IS NULL ";
        }

        if ($tranche == 1) {
            return $this->createQueryBuilder('c')
                ->select('c.id, 
                        c.prenom, 
                        c.nom, 
                        reg.nom AS lalga, 
                        dept.nom AS ledistrict, 
                        salle.telephone, 
                        c.nin,            
                        salle.modePaiement AS modeP,           
                        workDistrict.nom AS lacommune,           
                        sup.nom AS supNom,            
                        sup.prenom AS supPrenom ,
                        count(presentiela.id) as nbj           
                    ')
                ->leftJoin('c.lga', 'reg')
                ->leftJoin('c.district', 'dept')
                ->leftJoin('c.workDistrict', 'workDistrict')
                ->join('c.salles', 'salle')
                ->leftJoin('salle.superviseur', 'sup')
                ->join('salle.presentielAgents', 'presentiela')
                ->join('presentiela.presentiel', 'presentiel')
                ->leftJoin('c.etatCandidats', 'etatCandidat')
                ->where('presentiela.payer = :payer')
                ->andWhere('presentiela.isPresent = :isP')
                ->andWhere('presentiela.etatPaiement IS NULL')
                ->andWhere('etatCandidat IS NULL')
                ->andWhere('presentiel.createAt BETWEEN :from AND :to')
                ->andWhere('c.lga = :codeReg')
                ->andWhere($bqb)
                ->groupBy('
                            c.id, 
                            c.prenom, 
                            c.nom, 
                            reg.nom, 
                            dept.nom, 
                            salle, 
                            c.nin,            
                            salle.modePaiement,           
                            workDistrict,           
                            sup.nom,            
                            sup.prenom
                        ')
                ->having('COUNT(presentiela.id) >= :tranche')
                ->setParameter('payer', 0)
                ->setParameter('isP', 1)
                ->setParameter('from', $datedeb)
                ->setParameter('to', $datefin)
                ->setParameter('codeReg', $code)
                ->setParameter('tranche', $tranche)
                //->setParameter('mode', $mode)

                ->getQuery()
                ->getResult();
        } else {

            // Récupération des Candidat présent durant les trois premier jours
            /*$qBPresent = $this->createQueryBuilder('c1')
            //->select('c1.id')
            ->join('c1.salles', 'salle1')
            ->join('salle1.presentielAgents', 'presentiela1')
            ->join('presentiela1.presentiel', 'presentiel1')
            ->where('presentiela1.isPresent = :isP1')
            ->andWhere('presentiela1.etatPaiement IS NOT NULL')
            ->andWhere('presentiel1.createAt BETWEEN :from1 AND :to1')
            ->andWhere('salle1.typeRemplacement NOT IN (3) ')
            ;*/

            $qCpercu = $this->createQueryBuilder('c2')
                ->join('c2.etatCandidats', 'etat')
                ->where('etat.nbjour > 5000');

            return $this->createQueryBuilder('c')
                ->select('c.id, 
                                    c.prenom, 
                                    c.nom, 
                                    reg.nom AS lalga, 
                                    dept.nom AS ledistrict, 
                                    salle.telephone, 
                                    c.nin,            
                                    salle.modePaiement AS modeP,           
                                    workDistrict.nom AS lacommune,           
                                    sup.nom AS supNom,            
                                    sup.prenom AS supPrenom ,
                                    ec.nbjour AS mnt_a_payer,
                                    count(presentiela.id) as nbj           
                                ')
                ->leftJoin('c.lga', 'reg')
                ->leftJoin('c.district', 'dept')
                ->leftJoin('c.workDistrict', 'workDistrict')
                ->join('c.salles', 'salle')
                ->leftJoin('salle.superviseur', 'sup')
                ->join('salle.presentielAgents', 'presentiela')
                ->join('presentiela.presentiel', 'presentiel')
                ->leftJoin('c.etatCandidats', 'ec')
                ->where('presentiela.payer = :payer')
                ->andWhere('presentiela.isPresent = :isP')
                ->andWhere('presentiel.createAt > :from')
                ->andWhere('presentiela.etatPaiement IS NULL')
                ->andWhere('salle.typeRemplacement IN (0,2)')
                ->andWhere('c.lga = :codeReg')
                ->andWhere($bqb)
                //->andwhere('c.id IN (' . $qBPresent->getDQL() . ') ')
                ->andwhere('c.id NOT IN (' . $qCpercu->getDQL() . ') ')

                ->groupBy('
                        c.id, 
                        c.prenom, 
                        c.nom, 
                        reg.nom, 
                        dept.nom, 
                        salle, 
                        c.nin,            
                        salle.modePaiement,           
                        workDistrict,           
                        sup.nom,            
                        sup.prenom,
                        ec.nbjour
                        ')
                ->having('COUNT(presentiela.id) >= :tranche')

                ->setParameter('payer', 0)
                ->setParameter('isP', 1)
                //->setParameter('isP1', 1)
                ->setParameter('from', $datedeb)
                //->setParameter('to', $datefin)
                //->setParameter('from1', $datedeb)
                //->setParameter('to1', $datefin)
                ->setParameter('codeReg', $code)
                ->setParameter('tranche', $tranche)

                ->getQuery()
                ->getResult();
        }
    }


    public function findCandidatEigibleD($code, $datedeb, $datefin, $tranche, $mode): array
    {

        $bqb = " c.id IS NOT NULL ";
        if ($mode != "aucun") {
            $bqb = " salle.modePaiement = '$mode' ";
        } else {
            $bqb = " salle.modePaiement IS NULL ";
        }

        if ($tranche == 1) {
            return $this->createQueryBuilder('c')
                ->select('c.id, 
                                        c.prenom, 
                                        c.nom, 
                                        reg.nom AS lalga, 
                                        dept.nom AS ledistrict, 
                                        salle.telephone, 
                                        c.nin,            
                                        salle.modePaiement AS modeP,           
                                        workDistrict.nom AS lacommune,           
                                        sup.nom AS supNom,            
                                        sup.prenom AS supPrenom ,
                                        count(presentiela.id) as nbj           
                                    ')
                ->leftJoin('c.lga', 'reg')
                ->leftJoin('c.district', 'dept')
                ->leftJoin('c.workDistrict', 'workDistrict')
                ->join('c.salles', 'salle')
                ->leftJoin('salle.superviseur', 'sup')
                ->join('salle.presentielAgents', 'presentiela')
                ->join('presentiela.presentiel', 'presentiel')
                ->leftJoin('c.etatCandidats', 'etatCandidat')
                ->where('presentiela.payer = :payer')
                ->andWhere('presentiela.isPresent = :isP')
                ->andWhere('presentiela.etatPaiement IS NULL')
                ->andWhere('etatCandidat IS NULL')
                ->andWhere('presentiel.createAt BETWEEN :from AND :to')
                ->andWhere('c.district = :codeDep')
                ->andWhere($bqb)

                ->groupBy('
                                    c.id, 
                                    c.prenom, 
                                    c.nom, 
                                    reg.nom, 
                                    dept.nom, 
                                    salle, 
                                    c.nin,            
                                    salle.modePaiement,           
                                    workDistrict,           
                                    sup.nom,            
                                    sup.prenom
                                    ')
                ->having('COUNT(presentiela.id) >= :tranche')

                ->setParameter('payer', 0)
                ->setParameter('isP', 1)
                ->setParameter('from', $datedeb)
                ->setParameter('to', $datefin)
                ->setParameter('codeDep', $code)
                ->setParameter('tranche', $tranche)
                // ->setParameter('mode', $mode)

                ->getQuery()
                ->getResult();
        } else {

            // Récupération des Candidat présent durant les trois premier jours
            /* $qBPresent = $this->createQueryBuilder('c1')
                    //->select('c1.id')
                    ->join('c1.salles', 'salle1')
                    ->join('salle1.presentielAgents', 'presentiela1')
                    ->join('presentiela1.presentiel', 'presentiel1')
                    ->where('presentiela1.isPresent = :isP1')
                    ->andWhere('presentiela1.etatPaiement IS NOT NULL')
                    ->andWhere('presentiel1.createAt BETWEEN :from1 AND :to1')
                    ->andWhere('salle1.typeRemplacement NOT IN (3,1) ')
                    ;*/

            $qCpercu = $this->createQueryBuilder('c2')
                ->join('c2.etatCandidats', 'etat')
                ->where('etat.nbjour > 5000');

            return $this->createQueryBuilder('c')
                ->select('c.id, 
                                    c.prenom, 
                                    c.nom, 
                                    reg.nom AS lalga, 
                                    dept.nom AS ledistrict, 
                                    salle.telephone, 
                                    c.nin,            
                                    salle.modePaiement AS modeP,           
                                    workDistrict.nom AS lacommune,           
                                    sup.nom AS supNom,            
                                    sup.prenom AS supPrenom ,
                                    ec.nbjour AS mnt_a_payer,
                                    count(presentiela.id) as nbj           
                                ')
                ->leftJoin('c.lga', 'reg')
                ->leftJoin('c.district', 'dept')
                ->leftJoin('c.workDistrict', 'workDistrict')
                ->join('c.salles', 'salle')
                ->leftJoin('salle.superviseur', 'sup')
                ->join('salle.presentielAgents', 'presentiela')
                ->join('presentiela.presentiel', 'presentiel')
                ->leftJoin('c.etatCandidats', 'ec')
                ->where('presentiela.payer = :payer')
                ->andWhere('presentiela.isPresent = :isP')
                ->andWhere('presentiel.createAt > :from')
                ->andWhere('presentiela.etatPaiement IS NULL')
                ->andWhere('salle.typeRemplacement IN (0,2)')
                ->andWhere('c.district = :codeDep')
                ->andWhere($bqb)
                //->andwhere('c.id IN (' . $qBPresent->getDQL() . ') ')
                ->andwhere('c.id NOT IN (' . $qCpercu->getDQL() . ') ')
                ->groupBy('
                                c.id, 
                                c.prenom, 
                                c.nom, 
                                reg.nom, 
                                dept.nom, 
                                salle, 
                                c.nin,            
                                salle.modePaiement,           
                                workDistrict,           
                                sup.nom,            
                                sup.prenom,
                                ec.nbjour
                                ')
                ->having('COUNT(presentiela.id) >= :tranche')

                ->setParameter('payer', 0)
                ->setParameter('isP', 1)
                //->setParameter('isP1', 1)
                ->setParameter('from', $datedeb)
                //->setParameter('to', $datefin)
                // ->setParameter('from1', $datedeb)
                // ->setParameter('to1', $datefin)
                ->setParameter('codeDep', $code)
                ->setParameter('tranche', $tranche)

                ->getQuery()
                ->getResult();
        }
    }

    public function findCandidatEigibleC($code, $datedeb, $datefin, $tranche, $mode): array
    {

        $bqb = " c.id IS NOT NULL ";
        if ($mode != "aucun") {
            $bqb = " salle.modePaiement = '$mode' ";
        } else {
            $bqb = " salle.modePaiement IS NULL ";
        }

        if ($tranche) {
            return $this->createQueryBuilder('c')
                ->select('c.id, 
                    c.prenom, 
                    c.nom, 
                    reg.nom AS lalga, 
                    dept.nom AS ledistrict, 
                    salle.telephone, 
                    c.nin,            
                    salle.modePaiement AS modeP,           
                    workDistrict.nom AS lacommune,           
                    sup.nom AS supNom,            
                    sup.prenom AS supPrenom ,
                    count(presentiela.id) as nbj           
                ')
                ->leftJoin('c.lga', 'reg')
                ->leftJoin('c.district', 'dept')
                ->leftJoin('c.workDistrict', 'workDistrict')
                ->join('c.salles', 'salle')
                ->leftJoin('salle.superviseur', 'sup')
                ->join('salle.presentielAgents', 'presentiel')
                ->join('presentiela.presentiel', 'presentiel')
                ->leftJoin('c.etatCandidats', 'etatCandidat')
                ->where('presentiela.payer = :payer')
                ->andWhere('presentiela.isPresent = :isP')
                ->andWhere('presentiela.etatPaiement IS NULL')
                ->andWhere('etatCandidat IS NULL')
                ->andWhere('presentiel.createAt BETWEEN :from AND :to')
                ->andWhere('c.workDistrict = :codCacr')
                ->andWhere($bqb)
                ->groupBy('
                            c.id, 
                            c.prenom, 
                            c.nom, 
                            reg.nom, 
                            dept.nom, 
                            salle, 
                            c.nin,            
                            salle.modePaiement,           
                            workDistrict,           
                            sup.nom,            
                            sup.prenom
                        ')
                ->having('COUNT(presentiela.id) >= :tranche')
                ->setParameter('payer', 0)
                ->setParameter('isP', 1)
                ->setParameter('from', $datedeb)
                ->setParameter('to', $datefin)
                ->setParameter('codCacr', $code)
                ->setParameter('tranche', $tranche)
                //->setParameter('mode', $mode)

                ->getQuery()
                ->getResult();
        } else {

            // Récupération des Candidat présent durant les trois premier jours
            /*  $qBPresent = $this->createQueryBuilder('c1')
           // ->select('c1.id')
            ->join('c1.salles', 'salle1')
            ->join('salle1.presentielAgents', 'presentiela1')
            ->join('presentiela1.presentiel', 'presentiel1')
            ->where('presentiela1.isPresent = :isP1')
            ->andWhere('presentiela1.etatPaiement IS NOT NULL')
            ->andWhere('presentiel1.createAt BETWEEN :from1 AND :to1')
            ->andWhere('salle1.typeRemplacement NOT IN (3) ')
            ;*/

            $qCpercu = $this->createQueryBuilder('c2')
                ->join('c2.etatCandidats', 'etat')
                ->where('etat.nbjour > 5000');

            return $this->createQueryBuilder('c')
                ->select('c.id, 
                                    c.prenom, 
                                    c.nom, 
                                    reg.nom AS lalga, 
                                    dept.nom AS ledistrict, 
                                    salle.telephone, 
                                    c.nin,            
                                    salle.modePaiement AS modeP,           
                                    workDistrict.nom AS lacommune,           
                                    sup.nom AS supNom,            
                                    sup.prenom AS supPrenom ,
                                    ec.nbjour AS mnt_a_payer,
                                    count(presentiela.id) as nbj           
                                ')
                ->leftJoin('c.lga', 'reg')
                ->leftJoin('c.district', 'dept')
                ->leftJoin('c.workDistrict', 'workDistrict')
                ->join('c.salles', 'salle')
                ->leftJoin('salle.superviseur', 'sup')
                ->join('salle.presentielAgents', 'presentiela')
                ->join('presentiela.presentiel', 'presentiel')
                ->leftJoin('c.etatCandidats', 'ec')
                ->where('presentiela.payer = :payer')
                ->andWhere('presentiela.isPresent = :isP')
                ->andWhere('presentiel.createAt > :from')
                ->andWhere('presentiela.etatPaiement IS NULL')
                ->andWhere('salle.typeRemplacement IN (0,2)')
                ->andWhere('c.workDistrict = :codCacr')
                ->andWhere($bqb)
                //->andwhere('c.id IN (' . $qBPresent->getDQL() . ') ')
                ->andwhere('c.id NOT IN (' . $qCpercu->getDQL() . ') ')
                ->groupBy('
                        c.id, 
                        c.prenom, 
                        c.nom, 
                        reg.nom, 
                        dept.nom, 
                        salle, 
                        c.nin,            
                        salle.modePaiement,           
                        workDistrict,           
                        sup.nom,            
                        sup.prenom,
                        ec.nbjour
                        ')
                ->having('COUNT(presentiela.id) >= :tranche')

                ->setParameter('payer', 0)
                ->setParameter('isP', 1)
                ->setParameter('from', $datedeb)
                //->setParameter('to', $datefin)
                //->setParameter('isP1', 1)
                //->setParameter('from1', $datedeb)
                //->setParameter('to1', $datefin)
                ->setParameter('codCacr', $code)
                ->setParameter('tranche', $tranche)

                ->getQuery()
                ->getResult();
        }
    }

    public function findCandidatEigibleS($code, $datedeb, $datefin, $tranche, $mode): array
    {
        $bqb = " c.id IS NOT NULL ";
        if ($mode != "aucun") {
            $bqb = " salle.modePaiement = '$mode' ";
        } else {
            $bqb = " salle.modePaiement IS NULL ";
        }

        if ($tranche == 1) {
            return $this->createQueryBuilder('c')
                ->select('c.id, 
                    c.prenom, 
                    c.nom, 
                    reg.nom AS lalga, 
                    dept.nom AS ledistrict, 
                    salle.telephone, 
                    c.nin,            
                    salle.modePaiement AS modeP,           
                    workDistrict.nom AS lacommune,           
                    sup.nom AS supNom,            
                    sup.prenom AS supPrenom ,
                    count(presentiela.id) as nbj           
                ')
                ->leftJoin('c.lga', 'reg')
                ->leftJoin('c.district', 'dept')
                ->leftJoin('c.workDistrict', 'workDistrict')
                ->join('c.salles', 'salle')
                ->leftJoin('salle.superviseur', 'sup')
                ->join('salle.presentielAgents', 'presentiel')
                ->join('presentiela.presentiel', 'presentiel')
                ->leftJoin('c.etatCandidats', 'etatCandidat')
                ->where('presentiela.payer = :payer')
                ->andWhere('presentiela.isPresent = :isP')
                ->andWhere('presentiela.etatPaiement IS NULL')
                ->andWhere('etatCandidat IS NULL')
                ->andWhere('presentiel.createAt BETWEEN :from AND :to')
                ->andWhere('salle.superviseur = :sup')
                ->andWhere($bqb)

                ->groupBy('
                            c.id, 
                            c.prenom, 
                            c.nom, 
                            reg.nom, 
                            dept.nom, 
                            salle, 
                            c.nin,            
                            salle.modePaiement,           
                            workDistrict,           
                            sup.nom,            
                            sup.prenom
                        ')
                ->having('COUNT(presentiela.id) >= :tranche')

                ->setParameter('payer', 0)
                ->setParameter('isP', 1)
                ->setParameter('from', $datedeb)
                ->setParameter('to', $datefin)
                ->setParameter('sup', $code)
                ->setParameter('tranche', $tranche)
                //->setParameter('mode', $mode)

                ->getQuery()
                ->getResult();
        } else {

            // Récupération des Candidat présent durant les trois premier jours
            /* $qBPresent = $this->createQueryBuilder('c1')
            //->select('c1.id')
            ->join('c1.salles', 'salle1')
            ->join('salle1.presentielAgents', 'presentiela1')
            ->join('presentiela1.presentiel', 'presentiel1')
            ->where('presentiela1.isPresent = :isP1')
            ->andWhere('presentiela1.etatPaiement IS NOT NULL')
            ->andWhere('presentiel1.createAt BETWEEN :from1 AND :to1')
            ->andWhere('salle1.typeRemplacement NOT IN (3) ')
            ; */

            $qCpercu = $this->createQueryBuilder('c2')
                ->join('c2.etatCandidats', 'etat')
                ->where('etat.nbjour > 5000');

            return $this->createQueryBuilder('c')
                ->select('c.id, 
                            c.prenom, 
                            c.nom, 
                            reg.nom AS lalga, 
                            dept.nom AS ledistrict, 
                            salle.telephone, 
                            c.nin,            
                            salle.modePaiement AS modeP,           
                            workDistrict.nom AS lacommune,           
                            sup.nom AS supNom,            
                            sup.prenom AS supPrenom ,
                            ec.nbjour AS mnt_a_payer,
                            count(presentiela.id) as nbj           
                        ')
                ->leftJoin('c.lga', 'reg')
                ->leftJoin('c.district', 'dept')
                ->leftJoin('c.workDistrict', 'workDistrict')
                ->join('c.salles', 'salle')
                ->leftJoin('salle.superviseur', 'sup')
                ->join('salle.presentielAgents', 'presentiela')
                ->join('presentiela.presentiel', 'presentiel')
                ->leftJoin('c.etatCandidats', 'ec')
                ->where('presentiela.payer = :payer')
                ->andWhere('presentiela.isPresent = :isP')
                ->andWhere('presentiel.createAt > :from')
                ->andWhere('presentiela.etatPaiement IS NULL')
                ->andWhere('salle.typeRemplacement IN (0,2)')
                ->andWhere('salle.superviseur = :sup')
                ->andwhere('c.id NOT IN (' . $qCpercu->getDQL() . ') ')
                ->andWhere($bqb)
                //->andwhere('c.id IN (' . $qBPresent->getDQL() . ') ')


                ->groupBy('
                                    c.id, 
                                    c.prenom, 
                                    c.nom, 
                                    reg.nom, 
                                    dept.nom, 
                                    salle, 
                                    c.nin,            
                                    salle.modePaiement,           
                                    workDistrict,           
                                    sup.nom,            
                                    sup.prenom,
                                    ec.nbjour
                                    ')
                ->having('COUNT(presentiela.id) >= :tranche')

                ->setParameter('payer', 0)
                ->setParameter('isP', 1)
                ->setParameter('from', $datedeb)
                //->setParameter('to', $datefin)
                //->setParameter('isP1', 1)
                //->setParameter('from1', $datedeb)
                //->setParameter('to1', $datefin)
                ->setParameter('sup', $code)
                ->setParameter('tranche', $tranche)

                ->getQuery()
                ->getResult();
        }
    }

    public function findUnSelectedCandidatsByCodes(array $posting_districts, array $defaultArrndCacrs)
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.posting_district', 'posting_district')
            ->where('a.onWaitingList IS NULL')
            ->andWhere('a.posteSouhaite = :poste');

        if (count($defaultArrndCacrs) > 0) {
            $qb = $qb->andWhere('posting_district.code IN (:codesArrnd)')
                ->andWhere('a.commune2 IN (:codes) OR a.commune3 IN (:codes)');
        }


        $qb = $qb->andWhere('a.isSelected = :status')
            ->andWhere('a.isAffected = :affected')
            ->setParameter('codesArrnd', $defaultArrndCacrs)
            ->setParameter('codes', $posting_districts)
            ->setParameter('poste', 'Enumeratorss')
            ->setParameter('status', 0)
            ->setParameter('affected', 0);

        return $qb->orderBy('a.score', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function buildDataTableForUnSelectedCandidats($columns, $orders, Districts $district = NULL, $codeDistrictArray = [])
    {

        $qb =  $this->createQueryBuilder('a')
            ->leftJoin('a.posting_district', "posting_district")
            ->leftJoin('a.commune2', "commune2")
            ->leftJoin('a.commune3', "commune3")
            ->andWhere('a.onWaitingList IS NULL')
            ->andWhere('a.isSelected = :status')
            ->andWhere('a.posteSouhaite = :posteSouhaite')
            ->andWhere('a.isAffected = :affected');

        // Parameter
        if ($district != NULL) {
            $qb = $qb->andWhere('a.district = :district')
                ->setParameter('district', $district);
        }

        $qb = $qb
            ->setParameter('posteSouhaite', 'Enumeratorss')
            ->setParameter('status', 0)
            ->setParameter('affected', 0);

        if (count($codeDistrictArray) > 0) {
            $qb = $qb
                ->andWhere('posting_district.code IN (:coms)')
                // ->andWhere('commune2.code IN (:codes) OR commune3.code IN (:codes)')
                ->setParameter('coms', $codeDistrictArray)
                // ->setParameter('codes', $codeDistrictArray)
            ;
        }


        if ($columns != null && !empty($columns)) {
            $exprOr = [];
            $i = 0;
            foreach ($columns as $column) {

                $data = $column['data'];
                $value = $column['search']['value'];
                $value = $value === 'true' ? TRUE : ($value === 'false' ? false :  $value);
                if (($value == NULL || !$value)  || $data === '10') { // ignorer recherche pour la colonnes confirmation car elle est deja en parametre de la foction
                    continue;
                }
                ++$i;
                if (\is_bool($value)) {
                    $exprOr[] = $qb->expr()->eq(!strpos($data, ".") ? "a.$data" : $data, ":" . str_replace('.', '', $data));
                    $qb->setParameter(str_replace('.', '', $data),  $value);
                } else {
                    $exprOr[] = $qb->expr()->like(!strpos($data, ".") ? "a.$data" : $data, ":" . str_replace('.', '', $data));
                    $qb->setParameter(str_replace('.', '', $data), '%' . $value . '%');
                }
            }
            if ($i > 0) {
                $qb->andWhere($qb->expr()->orX(...$exprOr));
            }
        }

        if ($orders != null && count($orders) > 0) {
            foreach ($orders as $order) {
                $colName = $columns[$order['column']]['data'];
                if (!strpos($colName, ".")) {
                    $qb->orderBy("a.$colName", strtolower($order['dir']));
                } else {
                    $qb->orderBy("$colName", strtolower($order['dir']));
                }
            }
        }
        return $qb->getQuery();
    }


    public function findCandidatsVague2(CommunesArrCommunautesRurales $posting_district)
    {
        $from = new \DateTime("2022-12-23" . " 00:00:00");
        $to   = new \DateTime("2023-01-31" . " 23:59:59");

        return $this->createQueryBuilder('c')
            ->where('c.createdAt BETWEEN :from AND :to')
            ->andWhere('c.posting_district != :commune')
            ->setParameter('commune', $posting_district)
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->getQuery()
            ->getResult();
    }

    public function findUnselectedOptions23($commune2Or3, $communesCommission, $district)
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.posting_district', 'com1')
            ->leftJoin('a.commune2', 'com2')
            ->leftJoin('a.commune3', 'com3')
            ->where('a.onWaitingList IS NULL OR a.onWaitingList = :status')
            ->andWhere('a.district = :dept')
            ->andWhere('a.posteSouhaite = :poste')
            ->andWhere('com1.code IN (:com)')
            ->andWhere('com2.code = :cod OR com3.code = :cod')
            ->andWhere('a.isSelected = :status')
            ->setParameter('com', $communesCommission)
            ->setParameter('poste', 'Enumeratorss')
            ->setParameter('status', '0')
            ->setParameter("cod", $commune2Or3)
            ->setParameter('dept', $district)
            ->orderBy('a.scoreEnsae', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findSelectedEnumerators($typListe, $code)
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a.submission_number, a.prenom, a.nom, SUBSTRING(a.nin, 1, 8) AS trunctNin')
            ->leftJoin('a.workDistrict', 'cw')
            ->where('cw.code = :code');

        if ($typListe == 'p') {
            $qb->andWhere('a.isSelected = :status');
        } else {
            $qb->andWhere('a.onWaitingList = :status');
        }

        return $qb
            ->setParameter('code', $code)
            ->setParameter('status', 1)
            ->orderBy("a.scoreEnsae", "DESC")
            ->getQuery()
            ->getResult();
    }

    public function  buildDataTableA($columns, $orders)
    {
        $qb =  $this->createQueryBuilder('c')
            ->where('c.isSelected = :status')
            ->setParameter('status', 1);

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
                $exprOr[] = $qb->expr()->like(strpos($data, ".") == FALSE ? "c.$data" : $data, ":" . str_replace('.', '', $data));
                $qb->setParameter(str_replace('.', '', $data), '%' . $value . '%');
            }
            if ($i > 0) {
                $qb->where($qb->expr()->orX(...$exprOr));
            }
        }


        return $qb->getQuery();
    }

    public function findCandidatsNotConfirmed()
    {
        $qb =  $this->createQueryBuilder('a')
            ->where('a.isSelected = :isSelected')
            ->andWhere('a.confirmation = 0')
            ->setParameter('isSelected', 1);

        return $qb->getQuery()->getResult();
    }

    public function findCandidatsPasConfirmer()
    {
        $qb =  $this->createQueryBuilder('a')
            ->where('a.isSelected = :isSelected')
            ->andWhere('a.confirmation IS NULL')
            ->setParameter('isSelected', 1);

        return $qb->getQuery()->getResult();
    }

    public function findCacrDesistement()
    {
        // SELECT COUNT(*) AS NBR,
        //     posting_district.id,
        //     posting_district.nom,
        //     posting_district.code
        // FROM applications c INNER JOIN communes_arr_communautes_rurales posting_district ON c.posting_district_work_id = posting_district.id  
        // WHERE c.confirmation = 0 AND c.poste_souhaite = 'Enumeratorss'
        // GROUP BY posting_district.nom, posting_district.code, posting_district.id

        return $this->createQueryBuilder('a')
            ->select('COUNT(a.id) AS NBR, posting_district.id, posting_district.nom, posting_district.code')
            ->leftJoin('a.workDistrict', 'posting_district')
            ->andWhere('a.isSelected = :isP')
            ->andWhere('a.confirmation = :isDisp')
            ->setParameter('isP', 1)
            ->setParameter('isDisp', 0)
            ->groupBy('posting_district.id, posting_district.nom, posting_district.code')
            ->getQuery()
            ->getResult();
    }

    public function findDesistementParCommune($commune, $limit)
    {
        return $this->createQueryBuilder('a')
            ->where('a.workDistrict = :com')
            ->andWhere('a.onWaitingList = :isRe')
            ->setParameter('com', $commune)
            ->setParameter('isRe', 1)
            ->setMaxResults($limit)
            ->orderBy('a.scoreEnsae', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findListeAttente($columns, $orders, $confirmation, $workDistrict, int $nbrARemplacer)
    {

        $qb =  $this->createQueryBuilder('a')
            ->leftJoin('a.district', "district")
            ->leftJoin('a.lga', "lga")
            ->leftJoin('a.cav', "cav")
            ->leftJoin('a.posting_district', "posting_district")
            ->leftJoin('a.workDistrict', "workDistrict")
            ->leftJoin('a.commune', "commune")
            ->leftJoin('a.temporal_district_residence', "temporal_district_residence")
            ->where('a.workDistrict = :com')
            ->andWhere('a.onWaitingList = :la')
            ->setParameter('com', $workDistrict)
            ->setParameter('la', 1);

        if ($confirmation != NULL) {
            $qb = "NULL" === $confirmation ? $qb->andWhere('a.confirmation IS NULL') : $qb->andWhere('a.confirmation = :confirmation')->setParameter('confirmation', $confirmation);
        }
        if ($columns != null && !empty($columns)) {
            $exprOr = [];
            $i = 0;
            foreach ($columns as $column) {

                $data = $column['data'];
                $value = $column['search']['value'];
                $value = $value === 'true' ? TRUE : ($value === 'false' ? false :  $value);
                if (($value == NULL || !$value)) { // ignorer recherche pour la colonnes confirmation car elle est deja en parametre de la foction
                    continue;
                }
                ++$i;
                if (\is_bool($value)) {
                    $exprOr[] = $qb->expr()->eq(!strpos($data, ".") ? "a.$data" : $data, ":" . str_replace('.', '', $data));
                    $qb->setParameter(str_replace('.', '', $data),  $value);
                } else {
                    $exprOr[] = $qb->expr()->like(!strpos($data, ".") ? "a.$data" : $data, ":" . str_replace('.', '', $data));
                    $qb->setParameter(str_replace('.', '', $data), '%' . $value . '%');
                }
            }
            if ($i > 0) {
                $qb->andWhere($qb->expr()->orX(...$exprOr));
            }
        }

        if ($orders != null && count($orders) > 0) {
            foreach ($orders as $order) {
                $colName = $columns[$order['column']]['data'];
                if (!strpos($colName, ".")) {
                    $qb->orderBy("a.$colName", strtolower($order['dir']));
                } else {
                    $qb->orderBy("$colName", strtolower($order['dir']));
                }
            }
        }
        return $qb
            ->setMaxResults($nbrARemplacer)
            ->orderBy('a.scoreEnsae', 'DESC')
            ->getQuery();
    }

    public function findListeAttentes()
    {
        return $this->createQueryBuilder('a')
            ->Where('a.onWaitingList = :isRe')
            ->setParameter('isRe', 1)
            ->orderBy('a.scoreEnsae', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findCandidatNonEigible($datedeb, $datefin, $me, $myProfil, $columns = null)
    {
        $entityManager = $this->getEntityManager();
        $lga = $me->getDistrict()->getLga()->getId();
        $dep = $me->getDistrict()->getId();
        //dd($dep);
        $bqb = " c.id IS NOT NULL ";
        if ($myProfil == "CTD") {
            $bqb = " c.district =  $dep ";
        } else if ($myProfil == "CTR") {
            $bqb = " c.lga =  $lga";
        } else {
            //
        }

        // Récupération des Candidat présent durant les trois premier jours
        $qBPresent = $this->createQueryBuilder('c1')
            ->join('c1.salles', 'salle1')
            ->join('salle1.presentielAgents', 'presentiela1')
            ->join('presentiela1.presentiel', 'presentiel1')
            ->where('presentiela1.isPresent = :isP')
            ->andWhere('presentiel1.createAt BETWEEN :from AND :to')
            ->andWhere('salle1.typeRemplacement != 3 ');


        // Récupération des Candidat absant durant les trois premier jours
        $qb =   $this->createQueryBuilder('c')
            ->join('c.salles', 'salle')
            ->join('salle.presentielAgents', 'presentiel')
            ->where('c.id NOT IN (' . $qBPresent->getDQL() . ') ')
            ->andWhere('salle.typeRemplacement != 3')
            ->andWhere($bqb)
            ->setParameter('isP', 1)
            ->setParameter('from', $datedeb)
            ->setParameter('to', $datefin);

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
                $exprOr[] = $qb->expr()->like(strpos($data, ".") == FALSE ? "c.$data" : $data, ":" . str_replace('.', '', $data));
                $qb->setParameter(str_replace('.', '', $data), '%' . $value . '%');
            }
            if ($i > 0) {
                $qb->where($qb->expr()->orX(...$exprOr));
            }
        }

        return $qb->getQuery();
    }

    public function findCandidatNonEigibleM($me, $myProfil, $columns = null)
    {
        $entityManager = $this->getEntityManager();
        $lga = $me->getDistrict()->getLga()->getId();
        $dep = $me->getDistrict()->getId();
        //dd($dep);
        $bqb = " c.id IS NOT NULL ";
        if ($myProfil == "CTD") {
            $bqb = " c.district =  $dep ";
        } else if ($myProfil == "CTR") {
            $bqb = " c.lga =  $lga";
        } else {
            //
        }



        // Récupération des Candidat qui ont mise à jour leurs information
        $qBModep = $this->createQueryBuilder('c2')
            ->join('c2.salles', 'salle2')
            ->where('salle2.modePaiement IS NOT NULL')
            ->andWhere('salle2.telephone IS NOT NULL')
            ->andWhere('salle2.typeRemplacement != 3');

        // Récupération des Candidat qui n'ont pas été mise à jour leurs information
        $qb =   $this->createQueryBuilder('c')
            ->join('c.salles', 'salle')
            ->join('salle.presentielAgents', 'presentiel')
            ->where(' c.id NOT IN (' . $qBModep->getDQL() . ')')
            ->andWhere('salle.typeRemplacement != 3')
            ->andWhere($bqb);


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
                $exprOr[] = $qb->expr()->like(strpos($data, ".") == FALSE ? "c.$data" : $data, ":" . str_replace('.', '', $data));
                $qb->setParameter(str_replace('.', '', $data), '%' . $value . '%');
            }
            if ($i > 0) {
                $qb->where($qb->expr()->orX(...$exprOr));
            }
        }

        return $qb->getQuery();
    }

    public function findCandidatNonEigibleM_XLS($me, $myProfil, $columns = null)
    {
        $entityManager = $this->getEntityManager();
        $lga = $me->getDistrict()->getLga()->getId();
        $dep = $me->getDistrict()->getId();
        //dd($dep);
        $bqb = " c.id IS NOT NULL ";
        if ($myProfil == "CTD") {
            $bqb = " c.district =  $dep ";
        } else if ($myProfil == "CTR") {
            $bqb = " c.lga =  $lga";
        } else {
            //
        }



        // Récupération des Candidat qui ont mise à jour leurs information
        $qBModep = $this->createQueryBuilder('c2')
            ->join('c2.salles', 'salle2')
            ->where('salle2.modePaiement IS NOT NULL')
            ->andWhere('salle2.telephone IS NOT NULL')
            ->andWhere('salle2.typeRemplacement != 3');

        // Récupération des Candidat qui n'ont pas été mise à jour leurs information
        $qb =   $this->createQueryBuilder('c')
            ->join('c.salles', 'salle')
            ->join('salle.presentielAgents', 'presentiel')
            ->where(' c.id NOT IN (' . $qBModep->getDQL() . ')')
            ->andWhere('salle.typeRemplacement != 3')
            ->andWhere($bqb)
            ->getQuery();
        return $qb->getResult();
    }

    public function findCandidatNonEigibleA_xls($datedeb, $datefin, $me, $myProfil, $columns = null)
    {
        $entityManager = $this->getEntityManager();
        $lga = $me->getDistrict()->getLga()->getId();
        $dep = $me->getDistrict()->getId();
        //dd($dep);
        $bqb = " c.id IS NOT NULL ";
        if ($myProfil == "CTD") {
            $bqb = " c.district =  $dep ";
        } else if ($myProfil == "CTR") {
            $bqb = " c.lga =  $lga";
        } else {
            //
        }

        // Récupération des Candidat présent durant les trois premier jours
        $qBPresent = $this->createQueryBuilder('c1')
            ->join('c1.salles', 'salle1')
            ->join('salle1.presentielAgents', 'presentiela1')
            ->join('presentiela1.presentiel', 'presentiel1')
            ->where('presentiela1.isPresent = :isP')
            ->andWhere('presentiel1.createAt BETWEEN :from AND :to')
            ->andWhere('salle1.typeRemplacement != 3');


        // Récupération des Candidat absant durant les trois premier jours
        $qb =   $this->createQueryBuilder('c')
            ->join('c.salles', 'salle')
            ->join('salle.presentielAgents', 'presentiel')
            ->where('c.id NOT IN (' . $qBPresent->getDQL() . ') ')
            ->andWhere('salle.typeRemplacement != 3')
            ->andWhere($bqb)
            ->setParameter('isP', 1)
            ->setParameter('from', $datedeb)
            ->setParameter('to', $datefin)
            ->getQuery();


        return $qb->getResult();
    }


    public function findCandidatEtatPaie($datedeb, $datefin, $sup): array
    {
        //dd($sup);
        return $this->createQueryBuilder('c')
            ->select('c.id, 
                    c.prenom, 
                    c.nom, 
                    c.nin,
                     count(presentiela.id) as nbj           
                ')
            ->join('c.salles', 'salle')
            ->join('salle.presentielAgents', 'presentiela')
            ->join('presentiela.presentiel', 'presentiel')
            ->where('presentiela.isPresent = :isP')
            // ->andWhere('presentiel.createAt BETWEEN :from AND :to')
            ->andWhere('salle.superviseur = :sup')
            // ->andWhere('salle.typeRemplacement IN (0, 2)')
            ->groupBy('
                        c.id, 
                        c.prenom, 
                        c.nom,  
                        c.nin
                    ')
            // ->having('COUNT(presentiela.id) >= :tranche')

            ->setParameter('isP', 1)
            // ->setParameter('from', $datedeb)
            // ->setParameter('to', $datefin)
            // ->setParameter( 'tranche', $tranche)
            ->setParameter('sup', $sup)
            ->getQuery()
            ->getResult();
    }


    // liste des AR coptés non affecté à un superviseur pour un DEPT.
    public function  buildDataTableARCoptesNotAffected($columns, $orders, Districts $district, $arrndCacrsCodes = [], $searchCarc = NULL)
    {
        $qb =  $this->createQueryBuilder('a')
            ->leftJoin('a.district', "district")
            ->leftJoin('a.cav', "cav")
            ->leftJoin('a.workDistrict', "workDistrict")
            ->leftJoin('a.posting_district', "posting_district")
            ->leftJoin('a.commune', "commune")
            ->leftJoin('a.temporal_district_residence', "temporal_district_residence")
            ->where('a.estCopte = :status')
            ->andWhere('a.isAffected = :affected')
            ->andWhere('a.onWaitingList = :res')
            ->andWhere('a.district = :district')
            // ->andWhere('a.confirmation IS NULL OR a.confirmation = :isdispo') // ne prendre que les candidats ayant confirmé leur disponibilité
        ;

        $qb = $qb->setParameter('status', 1)
            ->setParameter('district', $district)
            ->setParameter('affected', 0)
            ->setParameter('res', 1)
            // ->setParameter('isdispo', 1) // ne prendre que les candidats ayant confirmé leur disponibilité
        ;

        if (count($arrndCacrsCodes) > 0) {
            $qb = $qb->andWhere('workDistrict.code IN (:codes)')
                ->setParameter('codes', $arrndCacrsCodes);
        }

        if ($searchCarc != NULL) {
            $qb = $qb->andWhere('workDistrict.id = :myposting_district')
                ->setParameter('myposting_district', $searchCarc);
        }

        if ($columns != null && !empty($columns)) {
            $exprOr = [];
            $i = 0;
            foreach ($columns as $column) {

                $data = $column['data'];
                $value = $column['search']['value'];
                if ($value == NULL || !$value || !$searchCarc == NULL) {
                    continue;
                }
                ++$i;
                $exprOr[] = $qb->expr()->like(!strpos($data, ".") ? "a.$data" : $data, ":" . str_replace('.', '', $data));
                $qb->setParameter(str_replace('.', '', $data), '%' . $value . '%');
            }
            if ($i > 0) {
                $qb->andWhere($qb->expr()->orX(...$exprOr));
            }
        }

        if ($orders != null && count($orders) > 0) {
            foreach ($orders as $order) {
                $colName = $columns[$order['column']]['data'];
                if (!strpos($colName, ".")) {
                    $qb->orderBy("a.$colName", strtolower($order['dir']));
                } else {
                    $qb->orderBy("$colName", strtolower($order['dir']));
                }
            }
        } else {
            $qb->orderBy("a.scoreEnsae", "DESC");
        }
        return $qb->getQuery();
    }


    public function findCandidatEtatPaieTranche2($datedeb, $mode): array
    {
        //dd($sup);
        $bqb = " ";
        if ($mode == "Wave") {
            $bqb = " WHERE s.mode_paiement = '$mode' ";
        } else {
            $bqb = " WHERE s.mode_paiement != 'Wave' ";
        }

        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(" select zz.id,
                                                        zz.nom,
                                                        zz.prenom,
                                                        zz.telephone,
                                                        zz.lalga,
                                                        zz.ledistrict,
                                                        zz.nin,                                                                           
                                                        zz.mode_paiement AS modeP,           
                                                        zz.lacommune,           
                                                        zz.supNom,            
                                                        zz.supPrenom , 
                                                        zz.mnt_a_payer
                                                        from ( select c.id,
                                                                            c.nom,
                                                                            c.prenom,
                                                                            s.telephone,
                                                                            reg.nom AS lalga,
                                                                            dept.nom AS ledistrict,
                                                                            c.nin,                                                                           
                                                                            s.mode_paiement AS modeP,           
                                                                            posting_district.nom AS lacommune,           
                                                                            sup.nom AS supNom,            
                                                                            sup.prenom AS supPrenom ,
                                                                            COALESCE(sum(ec.nbjour),0) somme,
                                                                            CASE WHEN COALESCE(sum(ec.nbjour),0)=0 THEN 15000
                                                                                WHEN COALESCE(sum(ec.nbjour),0)=5000 THEN 10000
                                                                                WHEN COALESCE(sum(ec.nbjour),0)=10000 THEN 5000
                                                                                ELSE 0
                                                                                END 'mnt_a_payer'
                                                                    FROM  applications c
                                                                    JOIN salles s ON c.id=s.candidat_id
                                                                    LEFT JOIN lgas reg       ON reg.id = c.lga_id
                                                                    LEFT JOIN districts dept ON dept.id = c.district_id
                                                                    LEFT JOIN communes_arr_communautes_rurales posting_district    ON posting_district.id = c.posting_district_work_id                                                                 
                                                                    LEFT JOIN utilisateur sup  ON sup.id= s.superviseur_id
                                                                    LEFT JOIN etat_candidat ec ON c.id=ec.candidat_id
                                                                    JOIN (SELECT lev.ext_notes_ar_id, rec.note_final
                                                                            FROM [ext_notes_ar_cases] cas
                                                                            JOIN [dbo].[ext_notes_ar_level-1] lev ON cas.id = lev.[case-id]
                                                                            JOIN [dbo].[ext_notes_ar_ext_notes_ar_rec] rec ON lev.[level-1-id] = rec.[level-1-id]
                                                                            WHERE cas.deleted = 0) sb
                                                                    ON s.login=sb.ext_notes_ar_id
                                                                    $bqb 
                                                                    
                                                                    GROUP BY c.id,
                                                                            c.nom,
                                                                            c.prenom,
                                                                            s.telephone,
                                                                            reg.nom,
                                                                            dept.nom,
                                                                            c.nin,                                                                           
                                                                            s.mode_paiement,           
                                                                            posting_district.nom,           
                                                                            sup.nom,            
                                                                            sup.prenom

                                                                                                                    
                                                            ) zz WHERE mnt_a_payer != 0
            
            
            ");

        return $query->getResult();
    }

    public function findCandidatEtatPaieTrancheR2($datedeb, $reg, $mode): array
    {
        //dd($sup);
        $bqb = " ";
        if ($mode == "Wave") {
            if ($reg == "Tous")
                $bqb = " WHERE s.mode_paiement = '$mode' ";
            else {
                $id = $reg->getId();
                $bqb = " WHERE s.mode_paiement = '$mode' AND reg.id = $id ";
            }
        } else {
            if ($reg == "Tous")
                $bqb = " WHERE s.mode_paiement != '$mode' ";
            else {
                $id = $reg->getId();
                $bqb = " WHERE s.mode_paiement != '$mode' AND reg.id = $id";
            }
        }

        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery("select zz.id,
                                                        zz.nom,
                                                        zz.prenom,
                                                        zz.telephone,
                                                        zz.lalga,
                                                        zz.ledistrict,
                                                        zz.nin,                                                                           
                                                        zz.mode_paiement AS modeP,           
                                                        zz.lacommune,           
                                                        zz.supNom,            
                                                        zz.supPrenom , 
                                                        zz.mnt_a_payer
                                                         from ( select c.id,
                                                                            c.nom,
                                                                            c.prenom,
                                                                            s.telephone,
                                                                            reg.nom AS lalga,
                                                                            dept.nom AS ledistrict,
                                                                            c.nin,                                                                           
                                                                            s.mode_paiement AS modeP,           
                                                                            posting_district.nom AS lacommune,           
                                                                            sup.nom AS supNom,            
                                                                            sup.prenom AS supPrenom ,
                                                                            COALESCE(sum(ec.nbjour),0) somme,
                                                                            CASE WHEN COALESCE(sum(ec.nbjour),0)=0 THEN 15000
                                                                                WHEN COALESCE(sum(ec.nbjour),0)=5000 THEN 10000
                                                                                WHEN COALESCE(sum(ec.nbjour),0)=10000 THEN 5000
                                                                                ELSE 0
                                                                                END 'mnt_a_payer'
                                                                    FROM  applications c
                                                                    JOIN salles s ON c.id=s.candidat_id
                                                                    LEFT JOIN lgas reg       ON reg.id = c.lga_id
                                                                    LEFT JOIN districts dept ON dept.id = c.district_id
                                                                    LEFT JOIN communes_arr_communautes_rurales posting_district    ON posting_district.id = c.posting_district_work_id                                                                 
                                                                    LEFT JOIN utilisateur sup  ON sup.id= s.superviseur_id
                                                                    LEFT JOIN etat_candidat ec ON c.id=ec.candidat_id
                                                                    JOIN (SELECT lev.ext_notes_ar_id, rec.note_final
                                                                            FROM [ext_notes_ar_cases] cas
                                                                            JOIN [dbo].[ext_notes_ar_level-1] lev ON cas.id = lev.[case-id]
                                                                            JOIN [dbo].[ext_notes_ar_ext_notes_ar_rec] rec ON lev.[level-1-id] = rec.[level-1-id]
                                                                            WHERE cas.deleted = 0) sb
                                                                    ON s.login=sb.ext_notes_ar_id
                                                                    $bqb 
                                                                    
                                                                    GROUP BY c.id,
                                                                                c.nom,
                                                                                c.prenom,
                                                                                s.telephone,
                                                                                reg.nom,
                                                                                dept.nom,
                                                                                c.nin,                                                                           
                                                                                s.mode_paiement,           
                                                                                posting_district.nom,           
                                                                                sup.nom,            
                                                                                sup.prenom

                                                                                                                    
                                                            ) zz WHERE mnt_a_payer != 0
            ");

        return $query->getResult();
    }

    public function isNinAlreadyUse($nin, $myNumber)
    {
        return $this->createQueryBuilder('a')
            ->select('COUNT(a)')
            ->Where('a.nin = :nin')
            ->andWhere('a.submission_number != :numdossier')
            ->setParameter('nin', $nin)
            ->setParameter('numdossier', $myNumber)
            ->getQuery()
            ->getSingleScalarResult() > 0;
    }

    // TODO: Bouna DRAME
    public function  getUnAffectedCandidat($columns, $orders, $isAffected = NULL)
    {
        $qb =  $this->createQueryBuilder('a')
            ->leftJoin('a.lga', 'lga')
            ->leftJoin('a.workDistrict', 'workDistrict')
            ->where('a.isAffected IS NULL OR a.isAffected = :fa')
            ->setParameter('fa', 0);

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

    public function dtCandidats($columns, $orders)
    {
        $qb =  $this->createQueryBuilder('a')
            ->leftJoin('a.lga', 'lga')
            ->leftJoin('a.district', 'district')
        ;

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
    
    public function cptPostulants()
    {
        return $this->createQueryBuilder('a')
            ->select('COUNT(a)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
