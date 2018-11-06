<?php declare(strict_types=1);

namespace App\Controller;

use App\Service\NasaApiService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var NasaApiService */
    private $nasaApiService;

    /**
     * @param EntityManagerInterface $entityManager
     * @param NasaApiService $nasaApiService
     */
    public function __construct(EntityManagerInterface $entityManager, NasaApiService $nasaApiService)
    {
        $this->entityManager = $entityManager;
        $this->nasaApiService = $nasaApiService;
    }

    /**
     * @Route("/index", name="index")
     */
    public function index()
    {
        $response = $this->nasaApiService->request();

        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
            'response' => $response
        ]);
    }
}
