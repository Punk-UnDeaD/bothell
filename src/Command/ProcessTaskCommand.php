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
        $processedTasks = [];
        while (true) {
            try {
                $tasks = $this->em->getRepository(Task::class)->findBy(['user' => $uid], ['id' => 'ASC']);
                $this->em->getConnection()->close();
                /** @var Task $task */
                foreach ($tasks as $task) {
                    $processedTasks[] = new ProcessedTask(
                        $task->getUser(),
                        $task->getCreated(),
                        $task->getMessage()
                    );
                    sleep(1);
                }
                break;
            } catch (\Exception $e) {
            }
        }
        while (true) {
            try {
                $this->em->getConnection()->connect();
                foreach ($processedTasks as $task) {
                    $this->em->persist($task);
                }
                foreach ($tasks as $task) {
                    $this->em->remove($task);
                }
                $this->em->flush();
                break;
            } catch (\Exception $e) {
            }
        }
        $io->note($uid.' processed');

        return Command::SUCCESS;
    }
}
