<?php declare(strict_types=1);

namespace App\Controller;

use App\Service\ApiService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var ApiService */
    private $nasaApiService;

    /**
     * @param EntityManagerInterface $entityManager
     * @param ApiService $apiService
     */
    public function __construct(EntityManagerInterface $entityManager, ApiService $apiService)
    {
        $this->entityManager = $entityManager;
        $this->nasaApiService = $apiService;
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
