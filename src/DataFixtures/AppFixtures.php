<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Phone;
use App\Entity\Costumer;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $costumerPasswordHasher;
    
    public function __construct(UserPasswordHasherInterface $costumerPasswordHasher)
    {
        $this->costumerPasswordHasher = $costumerPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Création d'une dizaine de customers ayant pour titre
        // for ($i = 0; $i < 10; $i++) {
        //     $customer = new Customer;
        //     $customer->setName('Client n°' . $i);
        //     $manager->persist($customer); 
        //     // On sauvegarde les customers créé dans un tableau
        //     $listCustomer[] = $customer;
        // }  

        // Création d'un costumer "normal"
        for ($i = 0; $i < 10; $i++) {
            $costumer = new Costumer();
            $costumer->setName('Client n°' . $i);
            $costumer->setRoles(["ROLE_USER"]);
            $costumer->setPassword($this->costumerPasswordHasher->hashPassword($costumer, "password"));
            $manager->persist($costumer);
            // On sauvegarde les costumers créé dans un tableau
            $listCostumer[] = $costumer;
        } 
         
        // Création d'un costumer admin
        //  $costumerAdmin = new Costumer();
        //  $costumerAdmin->setEmail("admin@api.com");
        //  $costumerAdmin->setRoles(["ROLE_ADMIN"]);
        //  $costumerAdmin->setPassword($this->costumerPasswordHasher->hashPassword($costumerAdmin, "password"));
        //  $manager->persist($costumerAdmin);

        // Création d'une centaine de users ayant pour titre
        for ($i = 0; $i < 100; $i++) {
            $user = new User;
            $user->setFirstname('Prénom ' . $i);
            $user->setName('Nom ' . $i);
            $user->setEmail('adressemail' . $i . '@gmail.com');
            $user->setCostumer($listCostumer[array_rand($listCostumer)]);
            $manager->persist($user); 
        }
                
        // Création d'une cinquantaine de phones ayant pour titre
           for ($i = 0; $i < 50; $i++) {
            $phone = new Phone;
            $phone->setName('Smartphone ' . $i);
            $phone->setPrice('999');
            $phone->setDescription('Description du smartphone numéro : ' . $i);
            $manager->persist($phone);
        }

        $manager->flush();
    }
}
