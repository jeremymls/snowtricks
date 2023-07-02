# OpenClassrooms - Projet 6 - Développez de A à Z le site communautaire SnowTricks

## Présentation

Dépôt Git de [SnowTricks](https://snowtricks.jm-projets.fr/).

Ce projet est le sixième projet de la formation Développeur d'application - PHP/Symfony d'OpenClassrooms.

Jimmy Sweat est un entrepreneur ambitieux passionné de snowboard. Son objectif est la création d'un site collaboratif pour faire connaître ce sport auprès du grand public et aider à l'apprentissage des figures (tricks).
Il souhaite capitaliser sur du contenu apporté par les internautes afin de développer un contenu riche et suscitant l’intérêt des utilisateurs du site. Par la suite, Jimmy souhaite développer un business de mise en relation avec les marques de snowboard grâce au trafic que le contenu aura généré.

Pour ce projet je suis en charge du développement de la plateforme SnowTricks en utilisant le framework PHP Symfony.

## Configuration conseillée

Le projet a été développé sur un serveur local avec les versions suivantes :

> - Nginx 1.13.12
> - PHP 7.4.33
> - [MySQL](https://www.mysql.com/fr/) 5.7.42
> - [Composer](https://getcomposer.org/) 2.5.7
> - [Node.js](https://nodejs.org/en/) 14.21.3
> - [Yarn](https://yarnpkg.com/) 1.22.19

## Installation

- Cloner le dépôt Git

```bash
git clone git@github.com:jeremymls/snowtricks.git
```

- Copier le fichier **.env** et le renommer en **.env.local**

```bash
cp .env .env.local
```

- Configurer les variables d'environnement dans le fichier **.env.local**

- Lancer le script d'installation

```bash
sh deploy.sh
```

## Configuration

Une fois installé, vous pouvez vous connecter en tant qu'administrateur avec les identifiants suivants :

```
Identifiant:  admin
Mot de passe: pass
```
:warning: **Pensez à modifier le mot de passe de l'administrateur après la première connexion.**