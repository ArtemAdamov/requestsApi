<?php

namespace App\Controller;

use App\Entity\Application;
use App\Entity\Client;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use App\Repository\ApplicationRepository;
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

    private function getClient(): Client|null
    {
        return $this->getUser()->getClient();
    }

    #[Route('/api/applications/attachment', name: 'upload_attachment', methods: 'POST')]
    public function upload(Request $request,  SerializerInterface $serializer): JsonResponse
    {
        $uploadedFile = $request->files->get('attachment');
        if (!$uploadedFile) {
            return new JsonResponse(['error' => 'No file provided'], Response::HTTP_BAD_REQUEST);
        }

        $uploadDirectory = $this->getParameter('kernel.project_dir') . '/public/uploads';
        $filename = uniqid() . '.' . $uploadedFile->getClientOriginalExtension();

        try {
            $uploadedFile->move($uploadDirectory, $filename);
        } catch (FileException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(['fileUrl' => '/uploads/' . $filename], Response::HTTP_CREATED);

    }
    #[Route('/api/applications', name: 'create_application', methods: 'POST')]
    public function create(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, EntityManagerInterface $entityManager): JsonResponse
    {
        $applicationData = $request->getContent();
        $application = $serializer->deserialize($applicationData, Application::class, 'json');

        $errors = $validator->validate($application);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new JsonResponse(['error' => $errorsString], Response::HTTP_BAD_REQUEST);
        }

        $client = $this->getClient();
        $application->setCreator($client);
        $application->setCreatedAt(new \DateTimeImmutable());

        try {
            $entityManager->persist($application);
            $entityManager->flush();
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_CONFLICT);
        }

        $responseContent = $serializer->serialize($application, 'json', ['groups' => 'application']);
        return new JsonResponse($responseContent, Response::HTTP_CREATED, [], true);
    }

    #[Route('/api/applications', name: 'list_applications', methods: 'GET')]
    public function list(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager): JsonResponse
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = max(1, $request->query->getInt('limit', 10));
        $offset = ($page - 1) * $limit;
        $user = $this->getUser();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('a')
            ->from(Application::class, 'a')
            ->orderBy('a.created_at', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        if (!in_array('ROLE_ADMIN', $user->getRoles())) {
            $client = $this->getClient();
            $queryBuilder->where('a.creator = :client')
                ->setParameter('client', $client);
        }

        try {
            $result = $queryBuilder->getQuery()->getResult();
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $responseContent = $serializer->serialize($result, 'json', ['groups' => 'application']);
        return new JsonResponse($responseContent, Response::HTTP_OK, [] , true);
    }

    #[Route('/api/applications/{id}', name: 'get_application', methods: 'GET')]
    public function read(int $id, ApplicationRepository $repository, SerializerInterface $serializer): JsonResponse
    {
        $client = $this->getClient();
        $application = $repository->findOneBy(['id' => $id, 'creator' => $client]);

        if (!$application) {
            return new JsonResponse(['error' => 'Application not found'], Response::HTTP_NOT_FOUND);
        }

        $responseContent = $serializer->serialize($application, 'json', ['groups' => 'application']);
        return new JsonResponse($responseContent, Response::HTTP_OK, [], true);
    }

    #[Route('/api/applications/{id}', name: 'update_application', methods: 'PUT')]
    public function update(Request $request, ApplicationRepository $repository, SerializerInterface $serializer, ValidatorInterface $validator, EntityManagerInterface $entityManager): JsonResponse
    {
        $client = $this->getClient();
        $id = $request->attributes->get('id');
        $application = $repository->findOneBy(['id' => $id, 'creator' => $client]);

        if (!$application) {
            return new JsonResponse(['error' => 'Application not found'], Response::HTTP_NOT_FOUND);
        }

        $requestData = $request->getContent();
        $updatedApplication = $serializer->deserialize($requestData, Application::class, 'json', ['object_to_populate' => $application]);

        $errors = $validator->validate($updatedApplication);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new JsonResponse(['error' => $errorsString], Response::HTTP_BAD_REQUEST);
        }

        try {
            $entityManager->flush();
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_CONFLICT);
        }

        $responseContent = $serializer->serialize($updatedApplication, 'json', ['groups' => 'application']);
        return new JsonResponse($responseContent, Response::HTTP_OK, [], true);
    }

    #[Route('/api/applications/{id}', name: 'delete_application', methods: 'DELETE')]
    public function delete(Request $request, ApplicationRepository $repository, EntityManagerInterface $entityManager): JsonResponse
    {
        $client = $this->getClient();
        $id = $request->attributes->get('id');
        $application = $repository->findOneBy(['id' => $id, 'creator' => $client]);

        if (!$application) {
            return new JsonResponse(['error' => 'Application not found'], Response::HTTP_NOT_FOUND);
        }

        try {
            $entityManager->remove($application);
            $entityManager->flush();
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_CONFLICT);
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
