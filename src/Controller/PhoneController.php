<?php

namespace App\Controller;

use App\Entity\Phone;
use App\Repository\PhoneRepository;
use JMS\Serializer\SerializerInterface;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class PhoneController extends AbstractController
{
    /**
     * Cette méthode permet de récupérer l'ensemble des téléphones
     *
     * @OA\Response(
     *     response=200,
     *     description="Retourne la liste des téléphones"
     * )
     *
     * @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="La page que l'on veut récupérer",
     *     @OA\Schema(type="int")
     * )
     *
     * @OA\Parameter(
     *     name="limit",
     *     in="query",
     *     description="Le nombre d'éléments que l'on veut récupérer",
     *     @OA\Schema(type="int")
     * )
     * @OA\Tag(name="Phones")
     *
     * @Route("/api/phones", name="app_phone", methods={"GET"})
     */
    public function getAllPhones(PhoneRepository $phoneRepository, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);

        $idCache = "getAllPhones-" . $page . "-" . $limit;
        $phoneList = $cachePool->get($idCache, function (ItemInterface $item) use ($phoneRepository, $page, $limit) {
            echo ("L\'ELEMENT N\'EST PAS ENCORE EN CACHE !!");
            $item->tag("phonesCache");
            $item->expiresAfter(180);
            return $phoneRepository->findAllWithPagination($page, $limit);
        });

        $phoneList = $phoneRepository->findAllWithPagination($page, $limit);
        $jsonPhoneList = $serializer->serialize($phoneList, 'json');
        return new JsonResponse($jsonPhoneList, Response::HTTP_OK, [], true);
    }

    /**
     * Cette méthode permet de récupérer les détails d'un téléphone en particulier
     * @OA\Tag(name="Phones")
     * @Route("/api/phone/{id}", name="detailPhone", methods={"GET"})
     */
    public function getDetailPhone(Phone $phone, SerializerInterface $serializer): JsonResponse
    {
        $jsonPhone = $serializer->serialize($phone, 'json');
        return new JsonResponse($jsonPhone, Response::HTTP_OK, [], true);
    }
}
