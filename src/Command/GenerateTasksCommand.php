<?php

namespace App\Command;

use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateTasksCommand extends Command
{
    public const TOTAL = 10000;

    public const USERS = 1000;

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
            ->setDescription('Task generator')
            ->addOption('total', 't', InputOption::VALUE_OPTIONAL, 'Total count, default='.$this::TOTAL)
            ->addOption('users', 'u', InputOption::VALUE_OPTIONAL, 'Users count, default='.$this::USERS);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->text('Start generation.');

        $total = $input->getOption('total') ?? $this::TOTAL;
        $users = $input->getOption('users') ?? $this::USERS;
        $io->text("Tasks: {$total} users: {$users}");
        do {
            $sub = rand(1, 10);
            $total -= $sub;
            $user = rand(1, $users);
            do {
                $sub--;
                $task = new Task($user, "-={$sub}=-");
                $this->em->persist($task);
            } while ($sub);
        } while ($total > 0);

        $this->em->flush();
        $io->success('All done');

        return Command::SUCCESS;
    }
}
