<?php

namespace App\Tests\unit;
use App\Controller\ApplicationController;
use App\Entity\Client;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Application;

final class ApplicationControllerTest extends TestCase
{
    public function testCreateMethod(): void
    {
        $requestMock = $this->createMock(Request::class);
        $serializerMock = $this->createMock(SerializerInterface::class);
        $validatorMock = $this->createMock(ValidatorInterface::class);
        $entityManagerMock = $this->createMock(EntityManagerInterface::class);

        $tokenStorageMock = $this->createMock(TokenStorageInterface::class);
        $userMock = $this->createMock(User::class);

        $tokenMock = $this->createMock(TokenInterface::class);

        $userMock->method('getClient')->willReturn(new Client());
        $tokenMock->method('getUser')->willReturn($userMock);
        $tokenStorageMock->method('getToken')->willReturn($tokenMock);

        $requestMock->method('getContent')->willReturn('{}');
        $serializerMock->method('deserialize')->willReturn(new Application());
        $validatorMock->method('validate')->willReturn(new ConstraintViolationList());
        $entityManagerMock->expects($this->once())->method('persist');
        $entityManagerMock->expects($this->once())->method('flush');

        $controller = new ApplicationController();

        $container = new ContainerBuilder();
        $container->set('serializer', $serializerMock);
        $container->set('validator', $validatorMock);
        $container->set('doctrine.orm.entity_manager', $entityManagerMock);
        $container->set('security.token_storage', $tokenStorageMock);
        $controller->setContainer($container);


        $response = $controller->create($requestMock, $serializerMock, $validatorMock, $entityManagerMock);


        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

    }
}