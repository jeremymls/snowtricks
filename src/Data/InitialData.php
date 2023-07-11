<?php

namespace App\Data;

use App\Entity\Comment;
use App\Entity\Group;
use App\Entity\Image;
use App\Entity\Parameters;
use App\Entity\Trick;
use App\Entity\User;
use App\Entity\Video;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class InitialData
{
    private $userPasswordHasher;
    private $slugger;
    private $em;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher, SluggerInterface $slugger, EntityManagerInterface $em)
    {
        $this->userPasswordHasher = $userPasswordHasher;
        $this->slugger = $slugger;
        $this->em = $em;
    }

    public function load(): bool
    {
        if ($this->em->getRepository(Parameters::class)->findOneBy(['name' => 'initialization'])) {
            return false;
        }

        // Parameters
        foreach ($this->getParameters() as $parameter) {
            $newParameter = new Parameters();
            $newParameter
                ->setName($parameter['name'])
                ->setValue($parameter['value'])
                ->setDescription($parameter['description'])
            ;

            $this->em->persist($newParameter);
        }
        $this->em->flush();

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
        $this->em->persist($adminAvatar);

        $admin->setImage($adminAvatar);
        $this->em->persist($admin);
        $this->em->flush();

        // Groups
        foreach ($this->getGroups() as $group) {
            $newGroup = new Group();
            $newGroup->setName($group);

            $this->em->persist($newGroup);
        }
        $this->em->flush();

        // Tricks
        foreach ($this->getTricks() as $trick) {
            $newTrick = new Trick();
            $newTrick
                ->setUser($this->em->getRepository(User::class)->findOneBy(['username' => 'admin']))
                ->setName($trick['name'])
                ->setSlug($this->slugger->slug($newTrick->getName())->lower())
                ->setDescription($trick['description'])
                ->setCategory($this->em->getRepository(Group::class)->findOneBy(['name' => $trick['group']]))
                ->setMiniature($trick['miniature'])
            ;
            $this->em->persist($newTrick);

            $this->em->flush();


            // Trick images
            foreach ($trick['images'] as $image) {
                $newImage = new Image();
                $newImage->setName($image['name']);
                $newImage->setTrick($newTrick);

                $this->em->persist($newImage);
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

                    $this->em->persist($newVideo);
                    $this->em->flush();
                }
            }

            $this->em->persist($newTrick);
            $this->em->flush();
        }
        return true;
    }

    public function demo(): void
    {
        $this->load();
        $faker = Factory::create('fr_FR');
        $images = $this->em->getRepository(Image::class)->findAll();
        $categories = $this->em->getRepository(Group::class)->findAll();

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
            $this->em->persist($user);
        }
        $this->em->flush();

        $users = $this->em->getRepository(User::class)->findAll();
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
                ->setCreatedAt($faker->dateTimeBetween('-12 months', '-6 months'))
            ;
            $this->em->persist($newTrick);
        }
        $this->em->flush();

        // Comments
        $tricks = $this->em->getRepository(Trick::class)->findAll();
        for ($i = 0; $i < 2000; $i++) {
            $trick = $tricks[rand(0, 99)];
            $comment = new Comment();
            $comment
                ->setUser($users[rand(0, 9)])
                ->setTrick($trick)
                ->setText($faker->sentences(1, true))
                ->setCreatedAt($faker->dateTimeBetween('-7 months', 'today'))
            ;
            $this->em->persist($comment);
        }

        $this->em->flush();

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
                'description' => '<p>Une rotation d&eacute;sax&eacute;e est une rotation initialement horizontale mais lanc&eacute;e avec un mouvement des &eacute;paules particulier qui d&eacute;saxe la rotation. Il existe diff&eacute;rents types de rotations d&eacute;sax&eacute;es (<em>corkscrew</em>&nbsp;ou&nbsp;<em>cork</em>,&nbsp;<em>rodeo</em>,&nbsp;<em>misty</em>, etc.) en fonction de la mani&egrave;re dont est lanc&eacute; le buste. Certaines de ces rotations, bien qu&#39;initialement horizontales, font passer la t&ecirc;te en bas.</p><p>Bien que certaines de ces rotations soient plus faciles &agrave; faire sur un certain nombre de tours (ou de demi-tours) que d&#39;autres, il est en th&eacute;orie possible de d&#39;att&eacute;rir n&#39;importe quelle rotation d&eacute;sax&eacute;e avec n&#39;importe quel nombre de tours, en jouant sur la quantit&eacute; de d&eacute;saxage afin de se retrouver &agrave; la position verticale au moment voulu.</p><p>Il est &eacute;galement possible d&#39;agr&eacute;menter une rotation d&eacute;sax&eacute;e par un grab.</p>',
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
                'description' => '<p>Un&nbsp;<strong>slide</strong>&nbsp;consiste &agrave; glisser sur une&nbsp;<a href=\"https://fr.wikipedia.org/wiki/Barre_de_slide\">barre de slide</a>. Le slide se fait soit avec la planche dans l&#39;axe de la barre, soit perpendiculaire, soit plus ou moins d&eacute;sax&eacute;.</p><p>On peut slider avec la planche centr&eacute;e par rapport &agrave; la barre (celle-ci se situe approximativement au-dessous des pieds du rideur), mais aussi en&nbsp;<strong>nose slide</strong>, c&#39;est-&agrave;-dire l&#39;avant de la planche sur la barre, ou en&nbsp;<strong>tail slide</strong>, l&#39;arri&egrave;re de la planche sur la barre.</p>',
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
            [
                'name' => '180',
                'description' => '<p>On d&eacute;signe par le mot &laquo;&nbsp;rotation&nbsp;&raquo; uniquement des rotations horizontales&nbsp;; les rotations verticales sont des&nbsp;<em>flips</em>. Le principe est d&#39;effectuer une rotation horizontale pendant le saut, puis d&#39;att&eacute;rir en position switch ou normal. La nomenclature se base sur le nombre de degr&eacute;s de rotation effectu&eacute;s &nbsp;:</p><ul>	<li>un&nbsp;<em>180</em>&nbsp;d&eacute;signe un demi-tour, soit 180 degr&eacute;s d&#39;angle&nbsp;;</li>	<li><em>360</em>,&nbsp;<em>trois six</em>&nbsp;pour un tour complet&nbsp;;</li>	<li><em>540</em>,&nbsp;<em>cinq quatre</em>&nbsp;pour un tour et demi&nbsp;;</li>	<li><em>720</em>,&nbsp;<em>sept deux</em>&nbsp;pour deux tours complets&nbsp;;</li>	<li><em>900</em>&nbsp;pour deux tours et demi&nbsp;;</li>	<li><em>1080</em>&nbsp;ou&nbsp;<em>big foot</em>&nbsp;pour trois tours&nbsp;;</li>	<li>etc.</li></ul><p>Une rotation peut &ecirc;tre&nbsp;<em>frontside</em>&nbsp;ou&nbsp;<em>backside</em>&nbsp;: une rotation&nbsp;<em>frontside</em>&nbsp;correspond &agrave; une rotation orient&eacute;e vers la carre&nbsp;<em>backside</em>. Cela peut para&icirc;tre incoh&eacute;rent mais l&#39;origine &eacute;tant que dans un&nbsp;<em>halfpipe</em>&nbsp;ou une&nbsp;<a href=\"https://fr.wikipedia.org/w/index.php?title=Rampe_de_skateboard&amp;action=edit&amp;redlink=1\">rampe de skateboard</a>, une rotation&nbsp;<em>frontside</em>&nbsp;se d&eacute;clenche naturellement depuis une position&nbsp;<em>frontside</em>&nbsp;(<em>i.e.</em>&nbsp;l&#39;appui se fait sur la carre frontside), et vice-versa. Ainsi pour un rider qui a une position&nbsp;<em>regular</em>&nbsp;(pied gauche devant), une rotation&nbsp;<em>frontside</em>&nbsp;se fait dans le sens inverse des aiguilles d&#39;une montre.</p><p>Une rotation peut &ecirc;tre agr&eacute;ment&eacute;e d&#39;un grab, ce qui rend le saut plus esth&eacute;tique mais aussi plus difficile car la position tweak&eacute;e a tendance &agrave; d&eacute;s&eacute;quilibrer le rideur et d&eacute;saxer la rotation. De plus, le sens de la rotation a tendance &agrave; favoriser un sens de grab plut&ocirc;t qu&#39;un autre. Les rotations de plus de trois tours existent mais sont plus rares, d&#39;abord parce que les modules assez gros pour lancer un tel saut sont rares, et ensuite parce que la vitesse de rotation est tellement &eacute;lev&eacute;e qu&#39;un grab devient difficile, ce qui rend le saut consid&eacute;rablement moins esth&eacute;tique.</p>',
                'miniature' => 'fab9a4be08e7bb8df3aaf5fa530230c7.webp',
                'group' => 'Rotations',
                'images' => [
                    [
                        'name' => "fab9a4be08e7bb8df3aaf5fa530230c7.webp",
                    ],
                ],
            ],
            [
                'name' => '360',
                'description' => '<p>On d&eacute;signe par le mot &laquo;&nbsp;rotation&nbsp;&raquo; uniquement des rotations horizontales&nbsp;; les rotations verticales sont des&nbsp;<em>flips</em>. Le principe est d&#39;effectuer une rotation horizontale pendant le saut, puis d&#39;att&eacute;rir en position switch ou normal. La nomenclature se base sur le nombre de degr&eacute;s de rotation effectu&eacute;s &nbsp;:</p><ul>	<li>un&nbsp;<em>180</em>&nbsp;d&eacute;signe un demi-tour, soit 180 degr&eacute;s d&#39;angle&nbsp;;</li>	<li><em>360</em>,&nbsp;<em>trois six</em>&nbsp;pour un tour complet&nbsp;;</li>	<li><em>540</em>,&nbsp;<em>cinq quatre</em>&nbsp;pour un tour et demi&nbsp;;</li>	<li><em>720</em>,&nbsp;<em>sept deux</em>&nbsp;pour deux tours complets&nbsp;;</li>	<li><em>900</em>&nbsp;pour deux tours et demi&nbsp;;</li>	<li><em>1080</em>&nbsp;ou&nbsp;<em>big foot</em>&nbsp;pour trois tours&nbsp;;</li>	<li>etc.</li></ul><p>Une rotation peut &ecirc;tre&nbsp;<em>frontside</em>&nbsp;ou&nbsp;<em>backside</em>&nbsp;: une rotation&nbsp;<em>frontside</em>&nbsp;correspond &agrave; une rotation orient&eacute;e vers la carre&nbsp;<em>backside</em>. Cela peut para&icirc;tre incoh&eacute;rent mais l&#39;origine &eacute;tant que dans un&nbsp;<em>halfpipe</em>&nbsp;ou une&nbsp;<a href=\"https://fr.wikipedia.org/w/index.php?title=Rampe_de_skateboard&amp;action=edit&amp;redlink=1\">rampe de skateboard</a>, une rotation&nbsp;<em>frontside</em>&nbsp;se d&eacute;clenche naturellement depuis une position&nbsp;<em>frontside</em>&nbsp;(<em>i.e.</em>&nbsp;l&#39;appui se fait sur la carre frontside), et vice-versa. Ainsi pour un rider qui a une position&nbsp;<em>regular</em>&nbsp;(pied gauche devant), une rotation&nbsp;<em>frontside</em>&nbsp;se fait dans le sens inverse des aiguilles d&#39;une montre.</p><p>Une rotation peut &ecirc;tre agr&eacute;ment&eacute;e d&#39;un grab, ce qui rend le saut plus esth&eacute;tique mais aussi plus difficile car la position tweak&eacute;e a tendance &agrave; d&eacute;s&eacute;quilibrer le rideur et d&eacute;saxer la rotation. De plus, le sens de la rotation a tendance &agrave; favoriser un sens de grab plut&ocirc;t qu&#39;un autre. Les rotations de plus de trois tours existent mais sont plus rares, d&#39;abord parce que les modules assez gros pour lancer un tel saut sont rares, et ensuite parce que la vitesse de rotation est tellement &eacute;lev&eacute;e qu&#39;un grab devient difficile, ce qui rend le saut consid&eacute;rablement moins esth&eacute;tique.</p>',
                'miniature' => 'aaaa20ea6010b25b6920550e33e2d26b.webp',
                'group' => 'Rotations',
                'images' => [
                    [
                        'name' => 'aaaa20ea6010b25b6920550e33e2d26b.webp',
                    ],
                ],
            ],
            [
                'name' => '720',
                'description' => '<p>On d&eacute;signe par le mot &laquo;&nbsp;rotation&nbsp;&raquo; uniquement des rotations horizontales&nbsp;; les rotations verticales sont des&nbsp;<em>flips</em>. Le principe est d&#39;effectuer une rotation horizontale pendant le saut, puis d&#39;att&eacute;rir en position switch ou normal. La nomenclature se base sur le nombre de degr&eacute;s de rotation effectu&eacute;s &nbsp;:</p><ul>	<li>un&nbsp;<em>180</em>&nbsp;d&eacute;signe un demi-tour, soit 180 degr&eacute;s d&#39;angle&nbsp;;</li>	<li><em>360</em>,&nbsp;<em>trois six</em>&nbsp;pour un tour complet&nbsp;;</li>	<li><em>540</em>,&nbsp;<em>cinq quatre</em>&nbsp;pour un tour et demi&nbsp;;</li>	<li><em>720</em>,&nbsp;<em>sept deux</em>&nbsp;pour deux tours complets&nbsp;;</li>	<li><em>900</em>&nbsp;pour deux tours et demi&nbsp;;</li>	<li><em>1080</em>&nbsp;ou&nbsp;<em>big foot</em>&nbsp;pour trois tours&nbsp;;</li>	<li>etc.</li></ul><p>Une rotation peut &ecirc;tre&nbsp;<em>frontside</em>&nbsp;ou&nbsp;<em>backside</em>&nbsp;: une rotation&nbsp;<em>frontside</em>&nbsp;correspond &agrave; une rotation orient&eacute;e vers la carre&nbsp;<em>backside</em>. Cela peut para&icirc;tre incoh&eacute;rent mais l&#39;origine &eacute;tant que dans un&nbsp;<em>halfpipe</em>&nbsp;ou une&nbsp;<a href=\"https://fr.wikipedia.org/w/index.php?title=Rampe_de_skateboard&amp;action=edit&amp;redlink=1\">rampe de skateboard</a>, une rotation&nbsp;<em>frontside</em>&nbsp;se d&eacute;clenche naturellement depuis une position&nbsp;<em>frontside</em>&nbsp;(<em>i.e.</em>&nbsp;l&#39;appui se fait sur la carre frontside), et vice-versa. Ainsi pour un rider qui a une position&nbsp;<em>regular</em>&nbsp;(pied gauche devant), une rotation&nbsp;<em>frontside</em>&nbsp;se fait dans le sens inverse des aiguilles d&#39;une montre.</p><p>Une rotation peut &ecirc;tre agr&eacute;ment&eacute;e d&#39;un grab, ce qui rend le saut plus esth&eacute;tique mais aussi plus difficile car la position tweak&eacute;e a tendance &agrave; d&eacute;s&eacute;quilibrer le rideur et d&eacute;saxer la rotation. De plus, le sens de la rotation a tendance &agrave; favoriser un sens de grab plut&ocirc;t qu&#39;un autre. Les rotations de plus de trois tours existent mais sont plus rares, d&#39;abord parce que les modules assez gros pour lancer un tel saut sont rares, et ensuite parce que la vitesse de rotation est tellement &eacute;lev&eacute;e qu&#39;un grab devient difficile, ce qui rend le saut consid&eacute;rablement moins esth&eacute;tique.</p>',
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
                'name' => 'Tail slide',
                'description' => '<p>Un&nbsp;<strong>slide</strong>&nbsp;consiste &agrave; glisser sur une&nbsp;<a href=\"https://fr.wikipedia.org/wiki/Barre_de_slide\">barre de slide</a>. Le slide se fait soit avec la planche dans l&#39;axe de la barre, soit perpendiculaire, soit plus ou moins d&eacute;sax&eacute;.</p><p>On peut slider avec la planche centr&eacute;e par rapport &agrave; la barre (celle-ci se situe approximativement au-dessous des pieds du rideur), mais aussi en&nbsp;<strong>nose slide</strong>, c&#39;est-&agrave;-dire l&#39;avant de la planche sur la barre, ou en&nbsp;<strong>tail slide</strong>, l&#39;arri&egrave;re de la planche sur la barre.</p>',
                'miniature' => '306a1d9800993419ad43e670b0a71ccc.webp',
                'group' => 'Slides',
                'images' => [
                    [
                        'name' => '306a1d9800993419ad43e670b0a71ccc.webp',
                    ],
                ],
            ],
            [
                'name' => 'Backside Air',
                'description' => '<p>Le terme&nbsp;<em>old school</em>&nbsp;d&eacute;signe un style de&nbsp;<em>freestyle</em>&nbsp;caract&eacute;ris&eacute;e par en ensemble de figure et une mani&egrave;re de r&eacute;aliser des figures pass&eacute;e de mode, qui fait penser au freestyle des ann&eacute;es 1980 - d&eacute;but 1990 (par opposition &agrave;&nbsp;<em>new school</em>)&nbsp;:</p><ul>	<li>figures d&eacute;su&egrave;tes&nbsp;:&nbsp;<em>Japan air</em>,&nbsp;<em>rocket air</em>, ...</li>	<li>rotations effectu&eacute;es avec le corps tendu</li>	<li>figures saccad&eacute;es, par opposition au style&nbsp;<em>new school</em>&nbsp;qui privil&eacute;gie l&#39;amplitude</li></ul><p>&Agrave; noter que certains tricks&nbsp;<em>old school</em>&nbsp;restent ind&eacute;modables&nbsp;:</p><ul>	<li><em>Backside Air</em></li>	<li><em>Method Air</em></li></ul>',
                'miniature' => '2dd7698ba1d703a276d6b91fed9acd9c.webp',
                'group' => 'Old school',
                'images' => [
                    [
                        'name' => '2dd7698ba1d703a276d6b91fed9acd9c.webp',
                    ],
                ],
            ],
            [
                'name' => 'Method Air',
                'description' => '<p>Le terme&nbsp;<em>old school</em>&nbsp;d&eacute;signe un style de&nbsp;<em>freestyle</em>&nbsp;caract&eacute;ris&eacute;e par en ensemble de figure et une mani&egrave;re de r&eacute;aliser des figures pass&eacute;e de mode, qui fait penser au freestyle des ann&eacute;es 1980 - d&eacute;but 1990 (par opposition &agrave;&nbsp;<em>new school</em>)&nbsp;:</p><ul>	<li>figures d&eacute;su&egrave;tes&nbsp;:&nbsp;<em>Japan air</em>,&nbsp;<em>rocket air</em>, ...</li>	<li>rotations effectu&eacute;es avec le corps tendu</li>	<li>figures saccad&eacute;es, par opposition au style&nbsp;<em>new school</em>&nbsp;qui privil&eacute;gie l&#39;amplitude</li></ul><p>&Agrave; noter que certains tricks&nbsp;<em>old school</em>&nbsp;restent ind&eacute;modables&nbsp;:</p><ul>	<li><em>Backside Air</em></li>	<li><em>Method Air</em></li></ul>',
                'miniature' => 'f3263f1bdaff5f76994601e39913f990.webp',
                'group' => 'Old school',
                'images' => [
                    [
                        'name' => 'f3263f1bdaff5f76994601e39913f990.webp',
                    ],
                ],
            ],
            [
                'name' => 'Misty',
                'description' => '<p>Une rotation d&eacute;sax&eacute;e est une rotation initialement horizontale mais lanc&eacute;e avec un mouvement des &eacute;paules particulier qui d&eacute;saxe la rotation. Il existe diff&eacute;rents types de rotations d&eacute;sax&eacute;es (<em>corkscrew</em>&nbsp;ou&nbsp;<em>cork</em>,&nbsp;<em>rodeo</em>,&nbsp;<em>misty</em>, etc.) en fonction de la mani&egrave;re dont est lanc&eacute; le buste. Certaines de ces rotations, bien qu&#39;initialement horizontales, font passer la t&ecirc;te en bas.</p><p>Bien que certaines de ces rotations soient plus faciles &agrave; faire sur un certain nombre de tours (ou de demi-tours) que d&#39;autres, il est en th&eacute;orie possible de d&#39;att&eacute;rir n&#39;importe quelle rotation d&eacute;sax&eacute;e avec n&#39;importe quel nombre de tours, en jouant sur la quantit&eacute; de d&eacute;saxage afin de se retrouver &agrave; la position verticale au moment voulu.</p><p>Il est &eacute;galement possible d&#39;agr&eacute;menter une rotation d&eacute;sax&eacute;e par un grab.</p>',
                'miniature' => 'be6f72d415f76f035701e4506cd6a7c8.webp',
                'group' => 'Rotations désaxées',
                'images' => [
                    [
                        'name' => 'be6f72d415f76f035701e4506cd6a7c8.webp',
                    ],
                ],
                'videos' => [
                    [
                        'provider' => 'youtube',
                        'video_id' => 'DHWlxQ90ZCI',
                    ],
                ],
            ],
        ];
    }

    public function getParameters(): array
    {
        return [
            [
                'name' => 'tricksPerPage',
                'value' => '15',
                'description' => 'Nombre de tricks par page',
            ],
            [
                'name' => 'commentsPerPage',
                'value' => '10',
                'description' => 'Nombre de commentaires par page',
            ],
            [
                'name' => 'initialization',
                'value' => true,
                'description' => "Statut d'initialisation du site",
            ],
        ];
    }
}
