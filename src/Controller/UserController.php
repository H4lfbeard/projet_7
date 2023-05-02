<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\CustomerRepository;
use App\Entity\User;
use App\Entity\Customer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;



class UserController extends AbstractController
{

    // FONCTIONS LIÉES AUX CUSTOMERS
    /**
     * @Route("api/customer", name="app_customer", methods={"GET"})
     */
    public function getAllCustomers(CustomerRepository $customerRepository, SerializerInterface $serializer): JsonResponse
    {
        $customerList = $customerRepository->findAll();

        $jsonCustomerList = $serializer->serialize($customerList, 'json', ['groups' => 'getUsers']);
        return new JsonResponse($jsonCustomerList, Response::HTTP_OK, [], true);
    }

    /**
     * @Route("api/customer/{id}", name="detailCustomer", methods={"GET"})
     */
    public function getDetailCustomer(Customer $customer, SerializerInterface $serializer): JsonResponse 
    {
        $jsonCustomer = $serializer->serialize($customer, 'json', ['groups' => 'getUsers']);
        return new JsonResponse($jsonCustomer, Response::HTTP_OK, [], true);
    }

    // FONCTIONS LIÉES AUX USERS DES CUSTOMER
    /**
     * @Route("api/customer/{id}/users", name="customerUsers", methods={"GET"})
     */
    public function getCustomerUsers(Customer $customer, SerializerInterface $serializer): JsonResponse 
    {
        $userList = $customer->getUsers();
    
        $jsonUserList = $serializer->serialize($userList, 'json', ['groups' => 'getUsers']);
        return new JsonResponse($jsonUserList, Response::HTTP_OK, [], true);
    }

    // NON FONCTIONNEL, A LIER AVEC LE TOKEN D'IDENTIFICATION
    /**
     * @Route("api/customer/users/{id}", name="detailCustomerUser", methods={"GET"})
     */
    public function getDetailCustomerUser(int $userId, Customer $customer, User $user, SerializerInterface $serializer): JsonResponse
    {
        dd($user);
        // On vérifie que le user appartient bien au customer
        if ($user->getCustomer() !== $customer) {
            throw $this->createNotFoundException('Cette utilisateur ne fait pas partie de la liste du client');
        }
    
        $jsonUser = $serializer->serialize($user, 'json', ['groups' => 'getUsers']);
        return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
    }

    // FONCTIONS LIÉES AUX USERS
    /**
     * @Route("api/users", name="app_user", methods={"GET"})
     */
    public function getAllUsers(UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $userList = $userRepository->findAll();

        $jsonUserList = $serializer->serialize($userList, 'json', ['groups' => 'getUsers']);
        return new JsonResponse($jsonUserList, Response::HTTP_OK, [], true);
    }

    /**
     * @Route("/api/users/{id}", name="detailUser", methods={"GET"})
     */
    public function getDetailUser(User $user, SerializerInterface $serializer): JsonResponse 
    {
        $jsonUser = $serializer->serialize($user, 'json', ['groups' => 'getUsers']);
        return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
    }

    /**
     * @Route("/api/users/{id}", name="deleteUser", methods={"DELETE"})
     */
    public function deleteUser(User $user, EntityManagerInterface $em): JsonResponse 
    {
        $em->remove($user);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/api/users", name="createUser", methods={"POST"})
     */
    public function createUser(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $customerId = $data['customerId'] ?? null;
        
        if (!$customerId) {
            return new JsonResponse(['error' => 'customerId is required'], Response::HTTP_BAD_REQUEST);
        }
        
        $customer = $em->getRepository(Customer::class)->find($customerId);
        
        if (!$customer) {
            return new JsonResponse(['error' => sprintf('Customer with ID %d not found', $customerId)], Response::HTTP_NOT_FOUND);
        }
        
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');
        $user->setCustomer($customer);

        // On vérifie les erreurs
        $errors = $validator->validate($user);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }
        
        $em->persist($user);
        $em->flush();
        
        $jsonUser = $serializer->serialize($user, 'json', ['groups' => 'getUsers']);

        $location = $urlGenerator->generate('detailUser', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        
        return new JsonResponse($jsonUser, Response::HTTP_CREATED, ["Location" => $location], true);
    }

}
