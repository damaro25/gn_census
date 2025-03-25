<?php

namespace App\Command;

use App\Repository\ClassroomRepository;
use App\Repository\UserRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'csweb:add-dict',
    description: "Permet d'ajouter dans un dictionnaire csweb",
)]
class WriteDictCommand extends Command
{
    use LockableTrait;
    private HttpClientInterface $client;
    private $usersRepository;
    private $classroomRepository;
    private $defaultEntityManager;
    private string $token;
    private string $cswebBaseUrl;
    private string $password;

    public function __construct(
        string $csweb_base_url,
        string $gbos_csweb7_key,
        \Doctrine\ORM\EntityManagerInterface $defaultEntityManager = NULL,
        HttpClientInterface $client,
        UserRepository $userRepository,
        ClassroomRepository $classroomRepository,
    ) {
        parent::__construct();

        $this->cswebBaseUrl = $csweb_base_url;
        $this->password = $gbos_csweb7_key;

        $this->defaultEntityManager = $defaultEntityManager;
        $this->classroomRepository = $classroomRepository;
        $this->usersRepository = $userRepository;
        $this->client = $client;
        $this->token = $this->getToken();
    }
    protected function configure(): void
    {
        $this->addArgument('username', InputArgument::REQUIRED, 'supervisor username')
            ->addArgument('enumUserName', InputArgument::OPTIONAL, 'Login Enumerator');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        ini_set('memory_limit', '4096M');
        set_time_limit(0);

        $userName = $input->getArgument('username');
        $enumeratorUserName = NULL;

        if ($input->hasArgument('enumUserName')) {
            $enumeratorUserName = $input->getArgument('enumUserName');
        }

        $supervisor = $this->usersRepository->findOneBy(['username' => $userName]);
        $spEnumerators = $this->classroomRepository->getSpProfileEnumerator($userName, $enumeratorUserName);
        $io->note(count($spEnumerators) . " Enumerators find");
        if (count($spEnumerators) == 0) {
            return 0;
        }

        $this->defaultEntityManager->beginTransaction();

        try {
            $io->note("Token est " . $this->token);
            foreach ($spEnumerators as $eq) {
                $uuid = $eq->getUuid();
                $casesIds = $eq->getUsername();
                $userLogin = $eq->getUsername();
                $userPassword = $eq->getPassword();
                $firstName = $eq->getEnumerator()->getName();
                $lastName = $eq->getEnumerator()->getSurname();
                $phone = $eq->getEnumerator()->getPhone();
                $status = 4;
                $isDeleted = $eq->getDeleted() == 1 ? true : false;

                // $io->note($uuid);
                $buildBody = [
                    "id" => "$uuid",
                    "caseids" => "$casesIds",
                    "label" => "",
                    "deleted" => $isDeleted,
                    "verified" => false,
                    "clock" => [
                        [
                            "deviceId" => "d1fedfbf-4ccc-4fd9-86e6-b64b052431ab",
                            "revision" => 1
                        ]
                    ],
                    "notes" => [],
                    "level-1" => "{\"id\":{\"USER_LOGIN\":\"$userLogin\"},\"EXT_LOG_REC\":{\"USER_PASS\":\"$userPassword\",\"USER_FIRSTNAME\":\"$firstName\",\"USER_LASTNAME\":\"$lastName\",\"USER_LASTCONNECT\":0,\"USER_STATUS\":1,\"USER_PHONERGPH\":$phone,\"USER_TYPE\":$status,\"TYPE_REMPLACEMENT\":0}}"
                ];
                $code = $this->addToDict("AGEROUTE_EXT_LOGIN_DICT", $buildBody);
                $io->note("[$userLogin]: Insert into DICT AGEROUTE_EXT_LOGIN_DICT with code [$code]");

                $eq->setCswebResponse($code);
                $this->defaultEntityManager->persist($eq);
            }

            // insert supervisor data
            $uuid = $supervisor->getUuid();
            $casesIds = $supervisor->getUsername();
            $userLogin = $supervisor->getUsername();
            $userPassword = $supervisor->getPassword();
            $firstName = $supervisor->getName();
            $lastName = $supervisor->getSurname();
            $phone = $supervisor->getPhone();
            $status = 4;
            // $io->note($uuid);
            $spBody = [
                "id" => "$uuid",
                "caseids" => "$casesIds",
                "label" => "",
                "deleted" => false,
                "verified" => false,
                "clock" => [
                    [
                        "deviceId" => "d1fedfbf-4ccc-4fd9-86e6-b64b052431ab",
                        "revision" => 1
                    ]
                ],
                "notes" => [],
                "level-1" => "{\"id\":{\"USER_LOGIN\":\"$userLogin\"},\"EXT_LOG_REC\":{\"USER_PASS\":\"$userPassword\",\"USER_FIRSTNAME\":\"$firstName\",\"USER_LASTNAME\":\"$lastName\",\"USER_LASTCONNECT\":0,\"USER_STATUS\":1,\"USER_PHONERGPH\":$phone,\"USER_TYPE\":$status,\"TYPE_REMPLACEMENT\":0}}"
            ];
            $code = $this->addToDict("AGEROUTE_EXT_LOGIN_DICT", $spBody);
            $io->note("[$userName]: Insert into DICT AGEROUTE_EXT_LOGIN_DICT with code [$code]");

            $this->defaultEntityManager->flush();
            $this->defaultEntityManager->commit();
        } catch (\Exception $e) {
            $this->defaultEntityManager->rollback();
            $io->error($e->getMessage() . '  ' . $e->getTraceAsString());
        } finally {
            $this->release();
        }


        return 0;
    }

    public function getToken(): string
    {
        $baseUrl = $this->cswebBaseUrl;
        $phrase_pass = $this->password;

        $response = $this->client->request(
            'POST',
            "$baseUrl/api/token",
            [
                'body' => [
                    'client_id'  => 'cspro_android',
                    'client_secret' => 'cspro',
                    'grant_type' => 'password',
                    'username' => 'admin',
                    'password' => "$phrase_pass"
                ]
            ]
        );

        $statusCode = $response->getStatusCode();
        if ($statusCode != 200) {
            new \Exception("Failed to get $baseUrl statusCode: $statusCode");
        }
        $contentType = $response->getHeaders()['content-type'][0];
        $content = $response->getContent();
        // return  $response->toArray();
        return  $response->toArray()['access_token'];
    }

    public function addToDict($dictionnary, $body = []): int
    {

        try {
            $response = $this->client->request(
                'POST',
                $this->cswebBaseUrl . '/api/dictionaries/' . $dictionnary . '/cases',
                [
                    'auth_bearer' => $this->token,
                    'json' => [$body]
                ]
            );

            $statusCode = $response->getStatusCode();

            // var_dump($response);
            return $statusCode;
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
