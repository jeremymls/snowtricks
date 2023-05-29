<?php

namespace App\DataFixtures;

use App\Entity\Group;
use App\Entity\Image;
use App\Entity\Trick;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class DemoFixtures extends Fixture
{
    private $userPasswordHasher;
    private $slugger;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher, SluggerInterface $slugger)
    {
        $this->userPasswordHasher = $userPasswordHasher;
        $this->slugger = $slugger;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $images = $manager->getRepository(Image::class)->findAll();
        $categories = $manager->getRepository(Group::class)->findAll();

        // users
        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setEmail($faker->email())
                ->setIsVerified(true)
                ->setUsername($faker->name())
                ->setPassword(
                    $this->userPasswordHasher->hashPassword(
                        $user,
                        'pass'
                    )
                )
            ;
            $manager->persist($user);
        }
        $manager->flush();

        $users = $manager->getRepository(User::class)->findAll();
        // Tricks
        for ($i = 0; $i < 100; $i++) {
            $newTrick = new Trick();
            $newTrick
                ->setUser($users[rand(0, 9)])
                ->setName($faker->sentence(3, true))
                ->setSlug($this->slugger->slug($newTrick->getName())->lower())
                ->setDescription($faker->paragraphs(3, true))
                ->setCategory($categories[rand(0, 6)])
                ->setMiniature($images[rand(1, 20)]->getName())
            ;
            $manager->persist($newTrick);
        }

        $manager->flush();
    }
}


