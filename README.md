# Plugin Jeedom pour la surveillance de la box numericable/sfr.


## Comment développer et tester ?

- Utilisez Docker (recommandé)
- Utilisez un environnement Apache+PHP+Mysql
  - Installez Jeedom
  - [Téléchargez phpunit.phar (v5.x)](https://phpunit.de/getting-started/phpunit-5.html)
  - [Téléchargez Composer.phar](https://getcomposer.org/doc/00-intro.md#locally)


## Tests unitaires

Si vous êtes chez vous avec votre box numericable :
1. Modifiez le login et le mot de passe de l'interface d'administration de votre box avec les valeurs `admin`/`password`
2. Lancez les test phpunit complets ou le `testsuite` _"All tests"_

```bash
# via Apache+PHP+Mysql
php phpunit.phar .
  ou
php phpunit.phar --testsuite "All tests"

# via Docker
docker run --rm -it -v "${PWD}":/app phpunit/phpunit:5.7.12 .
  ou
docker run --rm -it -v "${PWD}":/app phpunit/phpunit:5.7.12 --testsuite "All tests"
```

Sinon, ne lancer que le `testsuite` _"Do not request the box"_

```bash
# via Apache+PHP+Mysql
./phpunit.phar --testsuite "Do not request the box"

# via Docker
docker run --rm -it -v "${PWD}":/app phpunit/phpunit:5.7.12 --testsuite "Do not request the box"
```

## Créer une release

1. Enlevez les dépendances de développement et [optimiser l'autoloading](https://getcomposer.org/doc/articles/autoloader-optimization.md) de Composer

	```bash
	# via Apache+PHP+Mysql
	php composer.phar install --no-dev --classmap-authoritative
	
	# via Docker
	docker run --rm -it -v "${PWD}":/app --user $(id -u):$(id -g) composer install --no-dev --classmap-authoritative
	```

2. Crée une archive zip contenant :
    - Tous les dossiers sauf : tests
    - Seulement les fichiers README.md, LICENSE, composer.json, composer.lock
	