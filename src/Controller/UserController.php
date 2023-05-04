<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\CostumerRepository;
use App\Entity\User;
use App\Entity\Costumer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;


class UserController extends AbstractController
{

    // FONCTIONS LIÉES AUX COSTUMERS

    /**
     * @Route("api/costumer", name="app_costumer", methods={"GET"})
     */
    public function getCostumer(CostumerRepository $costumerRepository, SerializerInterface $serializer, TokenStorageInterface $tokenStorage): JsonResponse
    {
        $user = $tokenStorage->getToken()->getUser();
        $costumerId = $user->getId();
        $costumer = $costumerRepository->find($costumerId);
    
        // Vérifiez que l'entité a bien été trouvée
        if (!$costumer) {
            throw $this->createNotFoundException('Costumer not found');
        }

        $jsonCostumer = $serializer->serialize($costumer, 'json', ['groups' => 'getUsers']);
        return new JsonResponse($jsonCostumer, Response::HTTP_OK, [], true);
    }

    // PAS CERTAIN DE DEVOIR GARDER CES FONCTIONS ?
    // /**
    //  * @Route("api/costumers", name="app_costumers", methods={"GET"})
    //  */
    // public function getAllCostumers(CostumerRepository $costumerRepository, SerializerInterface $serializer): JsonResponse
    // {
    //     $costumerList = $costumerRepository->findAll();

    //     $jsonCostumerList = $serializer->serialize($costumerList, 'json', ['groups' => 'getUsers']);
    //     return new JsonResponse($jsonCostumerList, Response::HTTP_OK, [], true);
    // }

    // /**
    //  * @Route("api/costumer/{id}", name="detailCostumer", methods={"GET"})
    //  */
    // public function getDetailCostumer(Costumer $costumer, SerializerInterface $serializer): JsonResponse 
    // {
    //     $jsonCostumer = $serializer->serialize($costumer, 'json', ['groups' => 'getUsers']);
    //     return new JsonResponse($jsonCostumer, Response::HTTP_OK, [], true);
    // }


    // FONCTIONS LIÉES AUX USERS DES COSTUMER

    /**
     * @Route("api/users", name="app_user", methods={"GET"})
     */
    public function getAllCostumerUsers(CostumerRepository $costumerRepository, SerializerInterface $serializer, TokenStorageInterface $tokenStorage): JsonResponse
    {
        $user = $tokenStorage->getToken()->getUser();
        $costumerId = $user->getId();
        $costumer = $costumerRepository->find($costumerId);

        if (!$costumer) {
            throw $this->createNotFoundException('Costumer not found');
        }

        $users = $costumer->getUsers(); // récupère les utilisateurs liés à ce costumer
        $jsonUsers = $serializer->serialize($users, 'json', ['groups' => 'getUsers']);

        return new JsonResponse($jsonUsers, Response::HTTP_OK, [], true);
    }

    /**
     * @Route("/api/user/{id}", name="app_user_detail", methods={"GET"})
     */
    public function getUserDetails(int $id, UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $user = $userRepository->find($id);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $jsonUser = $serializer->serialize($user, 'json', ['groups' => 'getUsers']);
        return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
    }


    // FONCTIONS LIÉES AUX USERS

    // /**
    //  * @Route("api/users", name="app_user", methods={"GET"})
    //  */
    // public function getAllUsers(UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    // {
    //     $userList = $userRepository->findAll();

    //     $jsonUserList = $serializer->serialize($userList, 'json', ['groups' => 'getUsers']);
    //     return new JsonResponse($jsonUserList, Response::HTTP_OK, [], true);
    // }


    /**
     * @Route("/api/user/{id}", name="deleteUser", methods={"DELETE"})
     * @IsGranted("ROLE_USER", message="Vous n'avez pas les droits suffisants pour supprimer un utilisateur")
     */
    public function deleteUser(User $user, EntityManagerInterface $em): JsonResponse 
    {
        $em->remove($user);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/api/users", name="createUser", methods={"POST"})
     * @IsGranted("ROLE_USER", message="Vous n'avez pas les droits suffisants pour créer un utilisateur")
     */
    public function createUser(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator, TokenStorageInterface $tokenStorage): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = $tokenStorage->getToken()->getUser();
        $costumerId = $user->getId();
        
        if (!$costumerId) {
            return new JsonResponse(['error' => 'costumerId is required'], Response::HTTP_BAD_REQUEST);
        }
        
        $costumer = $em->getRepository(Costumer::class)->find($costumerId);
        
        if (!$costumer) {
            return new JsonResponse(['error' => sprintf('Costumer with ID %d not found', $costumerId)], Response::HTTP_NOT_FOUND);
        }
        
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');
        $user->setCostumer($costumer);

        // On vérifie les erreurs
        $errors = $validator->validate($user);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }
        
        $em->persist($user);
        $em->flush();
        
        $jsonUser = $serializer->serialize($user, 'json', ['groups' => 'getUsers']);

        $location = $urlGenerator->generate('app_user_detail', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        
        return new JsonResponse($jsonUser, Response::HTTP_CREATED, ["Location" => $location], true);
    }

}
