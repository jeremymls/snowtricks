<?php

namespace App\DataFixtures;

use App\Data\InitialData;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class DemoFixtures extends Fixture
{
    private $data;

    public function __construct(InitialData $data)
    {
        $this->data = $data;
    }

    public function load(ObjectManager $manager): void
    {
        $this->data->demo();
    }
}


