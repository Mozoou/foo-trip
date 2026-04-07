<?php

namespace App\Controller\Api;

use App\Entity\Destination;
use App\Repository\DestinationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/destinations')]
class DestinationController extends AbstractController
{
    #[Route('', name: 'api_destination_list', methods: ['GET'])]
    public function list(Request $request, DestinationRepository $repository): JsonResponse
    {
        $name = $request->query->get('name');
        $destinations = $repository->findByFilters($name);

        return $this->json(array_map(fn(Destination $d) => $this->serialize($d), $destinations));
    }

    #[Route('/{id}', name: 'api_destination_show', methods: ['GET'])]
    public function show(Destination $destination): JsonResponse
    {
        return $this->json($this->serialize($destination));
    }

    private function serialize(Destination $destination): array
    {
        return [
            'id' => $destination->getId(),
            'name' => $destination->getName(),
            'description' => $destination->getDescription(),
            'price' => $destination->getPrice(),
            'duration' => $destination->getDuration(),
            'image' => $destination->getImage(),
        ];
    }
}
