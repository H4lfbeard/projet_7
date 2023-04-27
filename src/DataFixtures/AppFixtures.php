<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Phone;
use App\Entity\Customer;
use App\Entity\User;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Création d'une dizaine de customers ayant pour titre
        for ($i = 0; $i < 10; $i++) {
            $customer = new Customer;
            $customer->setName('Client n°' . $i);
            $manager->persist($customer); 
            // On sauvegarde les customers créé dans un tableau
            $listCustomer[] = $customer;
        }  

        // Création d'une centaine de users ayant pour titre
        for ($i = 0; $i < 100; $i++) {
            $user = new User;
            $user->setFirstname('Prénom ' . $i);
            $user->setName('Nom ' . $i);
            $user->setEmail('adressemail' . $i . '@gmail.com');
            $user->setCustomer($listCustomer[array_rand($listCustomer)]);
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
