<?php
namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

final class LoginController
{
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function __invoke(): JsonResponse
    {
        
        throw new \LogicException('This should be intercepted by the security firewall.');
    }
}
