<?php

namespace App\Controller;

use App\Entity\Team;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

#[AsController]
class ReportingsController extends AbstractController
{

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct()
    {
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        $this->serializer = new Serializer($normalizers, $encoders);
    }
    /**
     * @Route("/api/report/json", name="app_report_json", methods={"GET"})
     */
    public function reportJson(ManagerRegistry $doctrine): Response
    {
        $data = $this->getReport($doctrine->getRepository(Team::class)->findAll());
        $jsonContent = json_encode(array('teams' => $data), JSON_PRETTY_PRINT);
        $path = Path::normalize('reports/jsonReport.json');

        try {
            $fileSystem = new Filesystem();

            $fileSystem->dumpFile($path , $jsonContent);
        } catch (IOExceptionInterface $exception) {
            echo "An error occurred while creating your directory at ".$exception->getPath();
        }

        return $this->file($path);
    }

    /**
     * @Route("/api/report/xml", name="app_report_xml", methods={"GET"})
     */
    public function reportXml(ManagerRegistry $doctrine): Response
    {
        $data = $this->getReport($doctrine->getRepository(Team::class)->findAll());
        $xmlContent = $this->serializer
            ->serialize(array('teams' => $data), 'xml', ['xml_format_output' => true]);
        $path = Path::normalize('reports/xmlReport.xml');

        try {
            $fileSystem = new Filesystem();
            $fileSystem->dumpFile($path, $xmlContent);
        } catch (IOExceptionInterface $exception) {
            echo "An error occurred while creating your directory at ".$exception->getPath();
        }

        return $this->file($path);
    }

    /**
     * @param $teams
     * @return array
     */
    protected function getReport($teams): array
    {
        $data = [];

        /**
         * @var Team $team
         */
        foreach ($teams as $team) {
            $accounts = [];
            foreach ($team->getAccounts() as $account) {
                $accounts[] = [
                    'id' => $account->getId(),
                    'name' => $account->getName(),
                ];
            }
            $data[] = [
                'name' => $team->getName(),
                'size' => count($team->getAccounts()),
                'accounts' => $accounts,
            ];
        }

        return $data;
    }
}
