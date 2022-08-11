<?php

namespace App\Command;

use App\Entity\Account;
use App\Entity\Team;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class LoadDataCommand extends Command
{
    protected static $defaultName = 'load-data';
    protected static $defaultDescription = 'Loading Dummy data to DB';

    private $manager;
    private $batchCount = 300;

    public function __construct( EntityManagerInterface $manager ) {
        $this->manager = $manager;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'accounts',
                '-a',
                InputOption::VALUE_REQUIRED,
                'Accounts count'
            )
            ->addOption(
                'teams',
                '-t',
                InputOption::VALUE_REQUIRED,
                'Teams count'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $accountsCount = $input->getOption('accounts');
        $teamsCount = $input->getOption('teams');

        $io = new SymfonyStyle($input, $output);

        if($teamsCount < 1) {
            $io->error('Teams option should be more than 1');
            return Command::FAILURE;
        }
        if($accountsCount < 1) {
            $io->error('Accounts option should be more than 1');
            return Command::FAILURE;
        }

        $io->writeln('Generate Teams');

        $teamObjects = [];
        for($i = 0; $i < $teamsCount; $i++){
            $team = new Team();
            $team->setName('team#' . $i);
            $this->manager->persist($team);

            if (($i % $this->batchCount) === 0){
                $this->manager->flush();
                $this->manager->clear();
            }
            $teamObjects[] = $team;
        }

        $this->manager->flush();
        $this->manager->clear();

        $teamIds = [];
        foreach ($teamObjects as $to) {
            $teamIds[] = $to->getId();
        }

        $io->writeln('Teams successfully generated');
        $io->writeln('Generate Accounts');

        for($i = 0; $i < $accountsCount; $i++){
            $account = new Account();
            $account->setName('account#' . $i);
            $team = $this->manager->find('App\Entity\Team', $teamIds[array_rand($teamIds)]);
            $account->setTeam($team);

            $this->manager->persist($account);

            if (($i % $this->batchCount) === 0){
                $this->manager->flush();
                $this->manager->clear();
            }
        }

        $this->manager->flush();

        $io->writeln('Accounts successfully generated');
        $io->success('Data successfully loaded');

        return Command::SUCCESS;
    }
}
