<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
readonly class HealthController
{
    #[Route('/api/_health')]
    public function health(): Response
    {
        return new Response(status: 200);
    }
}
