<?php

namespace App\Command;

use App\Data\InitialData;
use App\Entity\Parameters;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SnowtricksLoadFixturesCommand extends Command
{
    protected static $defaultName = 'snowtricks:load-fixtures';
    protected static $defaultDescription = 'Load fixtures for Snowtricks project';
    private $em;
    private $data;

    public function __construct(InitialData $data)
    {
        parent::__construct();
        $this->data = $data;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $res = $this->data->load();
        if ($res === true) {
            $io->success('Fixtures loaded');
            return Command::SUCCESS;
        } elseif ($res === false) {
            $io->warning('Fixtures already loaded');
            return Command::FAILURE;
        } else {
            $io->warning($res);
            $io->error('Error loading fixtures');
            return Command::FAILURE;
        }

    }
}
