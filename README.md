# Configuration
## Requis
PHP >= 8.1.0, Symfony >= 6.2, Composer >= 2.3.7
## Recommandé 
Symfony CLI >= 5.4.13, MySQL 8.0.29, Wampserver64, GitBash, Postman
# Installation du projet  
- Clonez le repo git 
- Lancez la commande "composer install"
- Générez une paire de clés public/privée en créant un dossier config/jwt à partir de la base du projet
```bash
openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
```
- Choisissez une passphrase sécurisée  
```bash
openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
```
- Entrez la passphrase de la clé privée  
- Créez un fichier .env.local à la racine du projet et ajouter le à votre .gitignore  
Copiez/Collez le contenu de .env dans .env.local et modifiez la JWT_PASSPHRASE en fonction de la passphrase que vous avez choisi pour votre clé privée  
- Configurez aussi votre base de données  
- Choisissez aussi le mode que vous souhaitez (dev, prod, test)  
- Créez la base de données  
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```
- Vous pouvez optionnellement ajouter des datafixtures  
```bash
php bin/console doctrine:fixtures:load
```
## La documentation
- Vous avez maintenant accès à l'API et nottamment à la page 127.0.0.1:8000/api/doc qui vous donne accès à la documentation des routes de l'application.
- La première chose à faire sera de vous connecter. Pour se faire, appuyez sur le bouton "Authorize" en haut à droite. Un utilisateur par défaut est utilisé (username: e@mail0.fr, password: password). 
- Si vous n'avez pas chargé de fixtures, cela ne fonctionnera pas directement. Il faudra d'abord créer cet utilisateur. 
- Vous pouvez modifier ces informations dans config/packages/nelmio_api_doc.yaml.  
- Ces credentials ne doivent cependant JAMAIS être correctes pour un environnement de production. 
- En cliquant sur Authorize, vous pouvez voir qu'on vous demande une valeur à remplir. Vous allez devoir d'abord générer un JsonWebToken. 
- Allez sur la route /api/login_check, cliquez sur "Try it out" puis "execute". Vous allez recevoir en réponse un token. 
- Copiez-collez le jwt sans le guillemets et re-cliquez sur "Authorize". Rentrez "bearer [votre JsonWebToken]". Vous êtes maintenant connecté. 
- Vous pouvez maintenant accéder à toutes les routes de l'application. Il est important de comprendre que sans les données de test, la documentation ne pourra pas fonctionner. 
- Vous pourrez supprimer ces données à tout moment si vous en avez besoin.  
# Les tests fonctionnels  
Pour effectuer des tests fonctionnels, vous devrez créer une base de données de test.  
Pour se faire, commencez par créer un fichier .env.test à la racine de votre projet.  
```
# define your env variables for the test env here
KERNEL_CLASS='App\Kernel'
APP_SECRET='$ecretf0rt3st'
SYMFONY_DEPRECATIONS_HELPER=999999
PANTHER_APP_ENV=panther
PANTHER_ERROR_SCREENSHOT_DIR=./var/error-screenshots
JWT_PASSPHRASE=privatekeypassphrase

###> doctrine/doctrine-bundle ###
# Format described at https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#connecting-using-a-url
# IMPORTANT: You MUST configure your server version, either here or in config/packages/doctrine.yaml
#
DATABASE_URL="mysql://username:password@127.0.0.1:3306/bilemo?serverVersion=8.0.29&charset=utf8mb4"
###< doctrine/doctrine-bundle ###
```
Lancez la commande de création de la base de données et du chargement des datafixtures qui sont nécessaires pour tester l'application :  
```
php bin/console doctrine:database:create --env=test
php bin/console doctrine:migrations:migrate --env=test
php bin/console doctrine:fixtures:load --env=test
```
Il vous faudra aussi générer une paire de clés public/privée spécifiquement pour les tests :  
```bash
openssl genrsa -out config/jwt/private-test.pem -aes256 4096
openssl rsa -pubout -in config/jwt/private-test.pem -out config/jwt/public-test.pem
```
Vous pouvez maintenant lancer les tests :  
```bash
php bin/console doctrine:fixtures:load --env=test
php bin/console cache:clear --env=test
php bin/phpunit
```
Il est important de lancer ces 3 commandes à chaque test pour que cela fonctionne correctement. 
# Félicitation !  
L'API devrait maintenant fonctionner correctement. Vous pouvez maintenant utiliser un outil comme Postman pour tester les routes.  
