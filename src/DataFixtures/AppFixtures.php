<?php

namespace App\DataFixtures;

use App\Entity\Applications;
use App\Entity\Districts;
use App\Entity\Lgas;
use App\Entity\User;
use App\Repository\DistrictsRepository;
use App\Repository\LgasRepository;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    private $lgaRepo;
    private $districtRepo;
    private $accountRepo;
    private $defaultEntityManager;

    const LGAS = [
        ['id' => 1, 'name' => 'Banjul'],
        ['id' => 2, 'name' => 'Kanifing'],
        ['id' => 3, 'name' => 'Brikama'],
        ['id' => 4, 'name' => 'Mansakonko'],
        ['id' => 5, 'name' => 'Kerewan'],
        ['id' => 6, 'name' => 'Kuntaur'],
        ['id' => 7, 'name' => 'Janjanbureh'],
        ['id' => 8, 'name' => 'Basse'],
    ];

    const DISTRICTS =
    [
        ['lga' => 4, 'dcode' => 9, 'name' => 'Foni Bintang Karanai', 'fcode' => 309],
        ['lga' => 4, 'dcode' => 10, 'name' => 'Foni Kansala', 'fcode' => 310],
        ['lga' => 4, 'dcode' => 11, 'name' => 'Foni Bondali', 'fcode' => 311],
        ['lga' => 4, 'dcode' => 12, 'name' => 'Foni Jarrol', 'fcode' => 312],
        ['lga' => 5, 'dcode' => 1, 'name' => 'Kiang West', 'fcode' => 401],
        ['lga' => 5, 'dcode' => 2, 'name' => 'Kiang Central', 'fcode' => 402],
        ['lga' => 5, 'dcode' => 3, 'name' => 'Kiang East', 'fcode' => 403],
        ['lga' => 5, 'dcode' => 4, 'name' => 'Jarra West', 'fcode' => 404],
        ['lga' => 5, 'dcode' => 5, 'name' => 'Jarra central', 'fcode' => 405],
        ['lga' => 5, 'dcode' => 6, 'name' => 'Jarra East', 'fcode' => 406],
        ['lga' => 6, 'dcode' => 1, 'name' => 'Lower Niumi', 'fcode' => 501],
        ['lga' => 6, 'dcode' => 2, 'name' => 'Upper Niumi', 'fcode' => 502],
        ['lga' => 6, 'dcode' => 3, 'name' => 'Jokadu', 'fcode' => 503],
        ['lga' => 6, 'dcode' => 4, 'name' => 'Lower Badibu', 'fcode' => 504],
        ['lga' => 6, 'dcode' => 5, 'name' => 'Central Badibu', 'fcode' => 505],
        ['lga' => 6, 'dcode' => 6, 'name' => 'Illiasa', 'fcode' => 506],
        ['lga' => 6, 'dcode' => 7, 'name' => 'Sabach Sanjal', 'fcode' => 507],
        ['lga' => 7, 'dcode' => 1, 'name' => 'Lower Saloum', 'fcode' => 601],
        ['lga' => 7, 'dcode' => 2, 'name' => 'Upper Saloum', 'fcode' => 602],
        ['lga' => 7, 'dcode' => 3, 'name' => 'Nianija', 'fcode' => 603],
        ['lga' => 7, 'dcode' => 4, 'name' => 'Niani', 'fcode' => 604],
        ['lga' => 7, 'dcode' => 5, 'name' => 'Sami', 'fcode' => 605],
        ['lga' => 8, 'dcode' => 1, 'name' => 'Niamina Dankunku', 'fcode' => 701],
        ['lga' => 8, 'dcode' => 2, 'name' => 'Niamina West', 'fcode' => 702],
        ['lga' => 8, 'dcode' => 3, 'name' => 'Niamina East', 'fcode' => 703],
        ['lga' => 8, 'dcode' => 4, 'name' => 'Lower Fuladu West', 'fcode' => 704],
        ['lga' => 8, 'dcode' => 5, 'name' => 'Janjanbureh', 'fcode' => 705],
        ['lga' => 8, 'dcode' => 6, 'name' => 'Upper Fuladu West', 'fcode' => 706],
        ['lga' => 9, 'dcode' => 1, 'name' => 'Jimara', 'fcode' => 801],
        ['lga' => 9, 'dcode' => 2, 'name' => 'Basse', 'fcode' => 802],
        ['lga' => 9, 'dcode' => 3, 'name' => 'Tumana', 'fcode' => 803],
        ['lga' => 9, 'dcode' => 4, 'name' => 'Kantora', 'fcode' => 804],
        ['lga' => 9, 'dcode' => 5, 'name' => 'Wuli East', 'fcode' => 805],
        ['lga' => 9, 'dcode' => 6, 'name' => 'Wuli West', 'fcode' => 806],
        ['lga' => 9, 'dcode' => 7, 'name' => 'Sandu', 'fcode' => 807],
    ];

    public function __construct(
        LgasRepository $lgaRepo,
        DistrictsRepository $districtRepo,
        UserRepository $userRepository,
        \Doctrine\ORM\EntityManagerInterface $defaultEntityManager,
    ) {
        $this->lgaRepo = $lgaRepo;
        $this->districtRepo = $districtRepo;
        $this->accountRepo = $userRepository;
        $this->defaultEntityManager = $defaultEntityManager;
    }

    public function load(ObjectManager $manager)
    {
        // $lga = $this->defaultEntityManager->getRepository(Lgas::class)->findOneBy(['id' => 1]);
        // $lga = $this->lgaRepo->findOneBy(['id' => 1]);
        // var_dump($lga);


        $this->insertAdmin($manager);
        $this->populateLgas($manager);
        // $lgas = $this->lgaRepo->findAll();
        //var_dump(count($lgas));

        $this->populateDistricts($manager);
        $faker = Factory::create('fr_FR');

        $account = $this->accountRepo->findOneBy(['username' => 'superadmin']);
        $districts = $this->districtRepo->findAll();
        $lga = $this->lgaRepo->findOneBy(['id' => $districts[0]->getLga()]);

        $district = $districts[0];

        for ($u = 0; $u < 1000; $u++) {
            $nin = (1357198) + $u;
            $candidat = new Applications();
            $candidat
                ->setEmail($faker->email)
                ->setNin($nin)
                ->setProfession($faker->title)
                ->setCv($faker->title)
                ->setScore($faker->numberBetween(0, 100))
                ->setIsSelected(true)
                ->setIsAffected(false)
                ->setCreatedAt($faker->dateTime)
                ->setUpdateAt($faker->dateTime)
                ->setCaptcha($faker->numberBetween(1000, 9999))

                ->setLga($lga)
                ->setDistrict($district)

                ->setSubmissionNumber($faker->numberBetween(7000, 9999))
                ->setName($faker->name)
                ->setSurname($faker->name)
                ->setMiddlename($faker->firstName)
                ->setBirthDate($faker->dateTime)

                ->setTemporalDistrictResidence($district)
                ->setUsualDistrictResidence($district)
                ->setCurrentAddress($faker->address)
                ->setPhone($faker->numberBetween(7000, 9999))
                ->setLanguage1($faker->randomElement(Applications::LANGUAGES))
                ->setLanguage2($faker->randomElement(Applications::LANGUAGES))
                ->setLanguage3($faker->randomElement(Applications::LANGUAGES))
                ->setNicCopy($faker->name)
                ->setSex($faker->randomElement(['M', 'F']))

                ->setNbrCensus($faker->numberBetween(0, 2))
                ->setUseOfTablet($faker->randomElement([true, false]))
                ->setComputerKnowledge($faker->randomElement([true, false]))
                ->setWhatsappPhone($faker->numberBetween(7000, 9999))

                ->setAccount($account)
                ->setWorkDistrict($district)
                
                ->setNotificationSubmissionSendAt(new \DateTime());

            $manager->persist($candidat);
        }
        $manager->flush();
    }


    public function populateDistricts(ObjectManager $em)
    {
        $dt = new \DateTime();
        foreach (self::DISTRICTS as $row) {
            $district = $this->districtRepo->findOneBy(['dcode' => $row['dcode']]);
            $lga = $this->lgaRepo->findOneBy(['code' => $row['lga']]);

            if ($district == NULL && $lga) {
                $district = new Districts();
                $district
                    ->setLga($lga)
                    ->setDcode($row['dcode'])
                    ->setName($row['name'])
                    ->setFdcode($row['fcode'])
                    ->setCreatedTime($dt)
                    ->setModifiedTime($dt)
                    ->setIsDeleted(false)
                    ->setNbEnumExpected(0);

                $em->persist($district);
            }
        }

        $em->flush();
    }

    public function populateLgas(ObjectManager $em)
    {
        $dt = new \DateTime();
        foreach (self::LGAS as $row) {
            $lga = $this->lgaRepo->findOneBy(['code' => $row['id']]);
            if ($lga == NULL) {
                $newLga = new Lgas();
                $newLga
                    ->setCode($row['id'])
                    ->setName($row['name'])
                    ->setCreatedTime($dt)
                    ->setModifiedTime($dt)
                    ->setIsDeleted(false);

                $em->persist($newLga);
                $em->flush();
            }
        }
    }

    public function insertAdmin(ObjectManager $em)
    {
        $account = $this->accountRepo->findOneBy(['username' => 'superadmin']);
        if ($account == NULL) {
            $newUser = new User();
            $newUser
                ->setRoles(["ROLE_SUPER_ADMIN", "ROLE_ADMIN", "ROLE_RECRUTEMENT", "ROLE_CTR", "ROLE_CTD", "ROLE_SUPPORT"])
                ->setUsername("superadmin")
                ->setPassword("$2y$13$Y6OtTT5LfW0dJ/8Z/KJisOlxyBc5iLoNpFVwZ5pPlCrAfNC0zEOnC")
                ->setname("admin")
                ->setsurname("admin")
                ->setIsActived(true)
                ->setphone("1111111")
                ->setCreateAt(new \DateTime())
                ->setUpdateAt(new \DateTime());

            $em->persist($newUser);
            $em->flush();
        }
    }
}
