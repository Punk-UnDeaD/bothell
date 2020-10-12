<?php

namespace App\Command;

use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateTasksCommand extends Command
{
    protected static $defaultName = 'app:generate:tasks';

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em, string $name = null)
    {
        parent::__construct($name);
        $this->em = $em;
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->text('Start generation.');

        $total = 10000;
        $users = 1000;

        do {
            $sub = rand(1, 11);
            $total -= $sub;
            $user = rand(1, $users + 1);
            do {
                $sub--;
                $task = new Task($user, "-={$sub}=-");
                $this->em->persist($task);
            } while ($sub);
            $this->em->flush();
        } while ($total > 0);

        $io->success('All done');

        return Command::SUCCESS;
    }
}
