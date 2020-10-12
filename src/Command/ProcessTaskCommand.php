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
            ->setDescription('Process messages for specific user')
            ->addArgument('uid', InputArgument::REQUIRED, 'User id');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $uid = $input->getArgument('uid');
        $io->note($uid.' started');

        try {
            $tasks = $this->em->getRepository(Task::class)->findBy(['user' => $uid], ['id' => 'ASC']);
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
                $this->em->flush();
            }
            $io->note($uid.' processed');
        } catch (\Exception $e) {
            $io->error($uid.'::'.$e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
