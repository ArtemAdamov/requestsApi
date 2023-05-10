<?php

namespace App\Controller;

use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Attributes as OA;

class ClientController extends AbstractController
{
    #[Route('/api/clients', name: 'create_client', methods: ['POST'])]
    #[OA\Response(
        response: 400,
        description: "Bad request"
    )]
    #[OA\Response(
        response: 409,
        description: "Client could not be created"
    )]
    #[OA\Response(
        response: 201,
        description: 'Client created successfully',
    )]
    public function createClient(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = $request->getContent();
        try {
            $client = $serializer->deserialize($data, Client::class, 'json');
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => 'Invalid data format: ' . $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
        $errors = $validator->validate($client);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new JsonResponse(['error' => $errorsString], Response::HTTP_BAD_REQUEST);
        }
        $user = $this->getUser();
        $client->setUser($user);
        try {
            $entityManager->persist($client);
            $entityManager->flush();
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_CONFLICT);
        }

        return new JsonResponse(['message' => 'Client created successfully'], Response::HTTP_CREATED);
    }

//    #[Route('/client', name: 'create_client', methods: ['POST'])]
//    public function viewClient(Request $request, EntityManagerInterface $entityManager): JsonResponse
//    {
//
//    }
}
