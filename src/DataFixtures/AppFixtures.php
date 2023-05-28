<?php

namespace App\DataFixtures;

use App\Entity\Group;
use App\Entity\Image;
use App\Entity\Trick;
use App\Entity\User;
use App\Entity\Video;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class AppFixtures extends Fixture
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
        // Admin
        $admin = new User();
        $admin->setEmail('admin@snowtricks.fr')
            ->setRoles(['ROLE_ADMIN'])
            ->setIsVerified(true)
            ->setUsername('admin')
            ->setPassword(
                $this->userPasswordHasher->hashPassword(
                    $admin,
                    'pass'
                )
            )
        ;
        $adminAvatar = new Image();
        $adminAvatar->setName('069c24c962c912e1f446239591e30cae.webp');
        $manager->persist($adminAvatar);

        $admin->setImage($adminAvatar);
        $manager->persist($admin);
        $manager->flush();

        // Groups
        foreach ($this->getGroups() as $group) {
            $newGroup = new Group();
            $newGroup->setName($group);

            $manager->persist($newGroup);
        }
        $manager->flush();

        // Tricks
        foreach ($this->getTricks() as $trick) {
            $newTrick = new Trick();
            $newTrick
                ->setUser($admin)
                ->setName($trick['name'])
                ->setSlug($this->slugger->slug($newTrick->getName())->lower())
                ->setDescription($trick['description'])
                ->setCategory($manager->getRepository(Group::class)->findOneBy(['name' => $trick['group']]))
                ->setMiniature($trick['miniature'])
            ;
            $manager->persist($newTrick);

            $manager->flush();


            // Trick images
            foreach ($trick['images'] as $image) {
                $newImage = new Image();
                $newImage->setName($image['name']);
                $newImage->setTrick($newTrick);

                $manager->persist($newImage);
            }

            // Trick videos
            if (isset($trick['videos'])) {
                foreach ($trick['videos'] as $video) {
                    $newVideo = new Video();
                    $newVideo
                        ->setProvider($video['provider'])
                        ->setVideoId($video['video_id'])
                        ->setTrick($newTrick)
                    ;

                    $manager->persist($newVideo);
                    $manager->flush();
                }
            }

            $manager->persist($newTrick);
            $manager->flush();
        }
    }

    public function getGroups(): array
    {
        return ['Grabs', 'Rotations', 'Flips', 'Rotations désaxées', 'Slides', 'One foot', 'Old school'];
    }

    public function getTricks(): array
    {
        return [
            [
                'name' => 'Nose grab',
                'description' => '<p>Saisie de la partie avant de la planche, avec la main avant</p>',
                'miniature' => '8484b70a7c20b38c64ec560517afa475.webp',
                'group' => 'Grabs',
                'images' => [
                    [
                        'name' => '2319f6a71fdbd791cb126bad6e6e19fc.webp',
                    ],
                    [
                        'name' => '38dcd61ec5f975c646e67657a465baf7.webp',
                    ],
                    [
                        'name' => '8484b70a7c20b38c64ec560517afa475.webp',
                    ],
                    [
                        'name' => '5666b2ce024eba3f5137d684dbebd61c.webp',
                    ],
                ],
            ],
            [
                'name' => '720',
                'description' => '<p><em>Sept deux</em>&nbsp;pour deux tours complets</p>',
                'miniature' => 'abaac349a0450df3a891df881d3b7e4f.webp',
                'group' => 'Rotations',
                'images' => [
                    [
                        'name' => 'abaac349a0450df3a891df881d3b7e4f.webp',
                    ],
                    [
                        'name' => '168fb85f5d88473d6cbb1885a0d379ab.webp',
                    ],
                ],
            ],
            [
                'name' => 'Back flips',
                'description' => '<p>Un&nbsp;<strong>flip</strong>&nbsp;est une rotation verticale. On distingue les&nbsp;<strong>front flips</strong>, rotations en avant, et les&nbsp;<strong>back flips</strong>, rotations en arri&egrave;re.</p>',
                'miniature' => '981166c3ecb90d663a00328a9fdc5665.webp',
                'group' => 'Flips',
                'images' => [
                    [
                        'name' => 'a298d01e1c93b5785a9975ba399c950b.webp',
                    ],
                    [
                        'name' => '34d6b278da6f25bcef65fcc281349efb.webp',
                    ],
                    [
                        'name' => '981166c3ecb90d663a00328a9fdc5665.webp',
                    ],
                    [
                        'name' => 'f513f145ced0700e12fe3b6facfcafc4.webp',
                    ],
                    [
                        'name' => '2ac252e60c4e9ca45555732c5799524d.webp',
                    ],
                ],
            ],
            [
                'name' => 'Corkscrew',
                'description' => '<p>Une rotation d&eacute;sax&eacute;e est une rotation initialement horizontale mais lanc&eacute;e avec un mouvement des &eacute;paules particulier qui d&eacute;saxe la rotation. Il existe diff&eacute;rents types de rotations d&eacute;sax&eacute;es (<em>corkscrew</em>&nbsp;ou&nbsp;<em>cork</em>,&nbsp;<em>rodeo</em>,&nbsp;<em>misty</em>, etc.) en fonction de la mani&egrave;re dont est lanc&eacute; le buste. Certaines de ces rotations, bien qu&#39;initialement horizontales, font passer la t&ecirc;te en bas.</p>\r\n\r\n<p>Bien que certaines de ces rotations soient plus faciles &agrave; faire sur un certain nombre de tours (ou de demi-tours) que d&#39;autres, il est en th&eacute;orie possible de d&#39;att&eacute;rir n&#39;importe quelle rotation d&eacute;sax&eacute;e avec n&#39;importe quel nombre de tours, en jouant sur la quantit&eacute; de d&eacute;saxage afin de se retrouver &agrave; la position verticale au moment voulu.</p>\r\n\r\n<p>Il est &eacute;galement possible d&#39;agr&eacute;menter une rotation d&eacute;sax&eacute;e par un grab.</p>',
                'miniature' => '83f7240cd3f2cf62f704ffe8a4333666.webp',
                'group' => 'Rotations désaxées',
                'images' => [
                    [
                        'name' => '83f7240cd3f2cf62f704ffe8a4333666.webp',
                    ],
                ],
                'videos' => [
                    [
                        'provider' => 'youtube',
                        'video_id' => 'j4NfAsszIOk',
                    ],
                ],
            ],
            [
                'name' => 'Nose slide',
                'description' => '<p>Un&nbsp;<strong>slide</strong>&nbsp;consiste &agrave; glisser sur une&nbsp;<a href=\"https://fr.wikipedia.org/wiki/Barre_de_slide\">barre de slide</a>. Le slide se fait soit avec la planche dans l&#39;axe de la barre, soit perpendiculaire, soit plus ou moins d&eacute;sax&eacute;.</p>\r\n\r\n<p>On peut slider avec la planche centr&eacute;e par rapport &agrave; la barre (celle-ci se situe approximativement au-dessous des pieds du rideur), mais aussi en&nbsp;<strong>nose slide</strong>, c&#39;est-&agrave;-dire l&#39;avant de la planche sur la barre, ou en&nbsp;<strong>tail slide</strong>, l&#39;arri&egrave;re de la planche sur la barre.</p>',
                'miniature' => 'b6eca5610507cf58c31643726e184e72.webp',
                'group' => 'Slides',
                'images' => [
                    [
                        'name' => 'b6eca5610507cf58c31643726e184e72.webp',
                    ],
                    [
                        'name' => '04e6f7df2dfea03f648307e8666a10af.webp',
                    ]
                ],
                'videos' => [
                    [
                        'provider' => 'youtube',
                        'video_id' => 'oAK9mK7wWvw',
                    ],
                ],
            ],
            [
                'name' => 'Stalefish',
                'description' => '<p>Saisie de la carre&nbsp;<em>backside</em>&nbsp;de la planche entre les deux pieds avec la main arri&egrave;re</p>',
                'miniature' => '8c93455f92919608d29b4e5ae524f2cd.webp',
                'group' => 'Grabs',
                'images' => [
                    [
                        'name' => '8c93455f92919608d29b4e5ae524f2cd.webp',
                    ],
                    [
                        'name' => '4f830f4430049531b0f88137b42ce060.webp',
                    ],
                    [
                        'name' => 'ceb9647e2c6a15b7c478930304ddb9c3.webp',
                    ],
                    [
                        'name' => '0dd62aacc9961a7629274c1e2fa66f02.webp',
                    ],
                    [
                        'name' => '19d62ce3474995fe3d1b892159548058.webp',
                    ],
                ],
            ],
            [
                'name' => 'Japan air',
                'description' => '<p>Saisie de l&#39;avant de la planche, avec la main avant, du c&ocirc;t&eacute; de la carre&nbsp;<em>frontside</em></p>',
                'miniature' => '18921702cb38a228bcb78746d490ab23.webp',
                'group' => 'Grabs',
                'images' => [
                    [
                        'name' => '938fced8d58cd6fc1f0fd4dbe15e29b2.webp',
                    ],
                    [
                        'name' => 'b669168443a27cacb874e303a4699c21.webp',
                    ],
                    [
                        'name' => '18921702cb38a228bcb78746d490ab23.webp',
                    ],
                ],
            ],
            [
                'name' => 'Seat belt',
                'description' => '<p>Saisie du carre frontside &agrave; l&#39;arri&egrave;re avec la main avant</p>',
                'miniature' => '7c84164e371b2f7727a450663cfc005d.webp',
                'group' => 'Grabs',
                'images' => [
                    [
                        'name' => '5a43fbc3fa00bddaed784e7e2161ea27.webp',
                    ],
                    [
                        'name' => '7c84164e371b2f7727a450663cfc005d.webp',
                    ],
                    [
                        'name' => '0259ad58545652b61b7cb1273369242f.webp',
                    ],
                ],
            ],
        ];
    }
}


