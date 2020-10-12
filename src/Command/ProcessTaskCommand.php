<?php

namespace App\Command;

use App\Entity\ProcessedTask;
use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ProcessTaskCommand extends Command
{
    protected static $defaultName = 'app:process:task';

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
        $arg1 = $input->getArgument('arg1');
        $tasks = $this->em->getRepository(Task::class)->findBy(['user' => $arg1], ['id' => 'ASC']);
        /** @var Task $task */
        foreach ($tasks as $task) {
            $processedTask = new ProcessedTask(
                $task->getUser(),
                $task->getCreated(),
                $task->getMessage()
            );
            sleep(1);
            $this->em->persist($processedTask);
            $this->em->remove($task);
        }


        $this->em->flush();

        return Command::SUCCESS;
    }
}
