<?php

namespace App\Controller;

use App\Entity\Application;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ApplicationController extends AbstractController
{

    #[Route('/applications/create', name: 'create_application', methods: 'POST')]
    public function create(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, EntityManagerInterface $entityManager): JsonResponse
    {
        // here is jwt required
//        $this->denyAccessUnlessGranted('ROLE_CLIENT');

        $applicationData = $request->getContent();
        $application = $serializer->deserialize($applicationData, Application::class, 'json');

        $errors = $validator->validate($application);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new JsonResponse(['error' => $errorsString], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->getUser();
        $application->setClient($user);

        try {
            $entityManager->persist($application);
            $entityManager->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e], Response::HTTP_CONFLICT);
        }

        $responseContent = $serializer->serialize($application, 'json');
        return new JsonResponse([$responseContent, Response::HTTP_CREATED, [], true]);
    }

    /**
     * @Route("/applications/{id}", name="get_application", methods={"GET"})
     */
    public function read(Application $application): JsonResponse
    {
        // Реализация чтения заявления
    }

    /**
     * @Route("/applications/{id}", name="update_application", methods={"PUT"})
     */
    public function update(Request $request, Application $application, SerializerInterface $serializer, ValidatorInterface $validator): JsonResponse
    {
        // Реализация обновления заявления
    }

    /**
     * @Route("/applications/{id}", name="delete_application", methods={"DELETE"})
     */
    public function delete(Application $application): JsonResponse
    {
        // Реализация удаления заявления
    }
}
