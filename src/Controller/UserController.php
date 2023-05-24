<?php

namespace App\Controller;

use App\Entity\Costumer;
use App\Entity\User;
use App\Repository\CostumerRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends AbstractController
{

    // FONCTIONS LIÉES AUX COSTUMERS

    /**
     * Cette méthode permet de récupérer les détails liée à votre profil client
     * @OA\Tag(name="Costumer")
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

        $context = SerializationContext::create()->setGroups(["getUsers"]);
        $jsonCostumer = $serializer->serialize($costumer, 'json', $context);
        return new JsonResponse($jsonCostumer, Response::HTTP_OK, [], true);
    }

    // FONCTIONS LIÉES AUX USERS DES COSTUMER

    /**
     * Cette méthode permet de récupérer la liste des utilisateurs liée à votre profil client
     * @OA\Tag(name="Users")
     * @Route("api/users", name="app_user", methods={"GET"})
     */
    public function getAllCostumerUsers(CostumerRepository $costumerRepository, SerializerInterface $serializer, TokenStorageInterface $tokenStorage, Request $request, UserRepository $userRepository): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 5);

        $user = $tokenStorage->getToken()->getUser();
        $costumerId = $user->getId();
        $costumer = $costumerRepository->find($costumerId);

        if (!$costumer) {
            throw $this->createNotFoundException('Costumer not found');
        }

        $users = $userRepository->findByWithPagination($costumerId, $page, $limit); // récupère les utilisateurs liés à ce costumer
        $context = SerializationContext::create()->setGroups(["getUsers"]);
        $jsonUsers = $serializer->serialize($users, 'json', $context);

        return new JsonResponse($jsonUsers, Response::HTTP_OK, [], true);
    }

    /**
     * Cette méthode permet de récupérer les détails d'une utilisateur
     * @OA\Tag(name="Users")
     * @Route("/api/user/{id}", name="app_user_detail", methods={"GET"})
     */
    public function getUserDetails(int $id, UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $user = $userRepository->find($id);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $context = SerializationContext::create()->setGroups(["getUsers"]);
        $jsonUser = $serializer->serialize($user, 'json', $context);
        return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
    }

    /**
     * Cette méthode permet de supprimer un utilisateur
     * @OA\Tag(name="Users")
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
     * Cette méthode permet de créer un utilisateur liée à votre profil client
     * @OA\Tag(name="Users")
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

        $context = SerializationContext::create()->setGroups(["getUsers"]);
        $jsonUser = $serializer->serialize($user, 'json', $context);

        $location = $urlGenerator->generate('app_user_detail', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonUser, Response::HTTP_CREATED, ["Location" => $location], true);
    }

}
