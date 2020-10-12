<?php

namespace App\Command;

use App\Entity\Task;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

class ProcessAllTasksCommand extends Command
{
    public const THREADS = 16;
    public const PACK = 64;

    protected static $defaultName = 'app:process:allTasks';

    /**
     * @var \Doctrine\DBAL\Connection
     */
    private Connection $connection;

    public function __construct(Connection $connection, string $name = null)
    {
        parent::__construct($name);
        $this->connection = $connection;
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
        $uids = $this->connection->createQueryBuilder()
            ->select('uid')
            ->from('task')
            ->distinct()
            ->setMaxResults($this::PACK)
            ->execute()
            ->fetchFirstColumn();

        $processes = [];

        while ($uids || $processes) {
            $processes = array_filter($processes, fn ($process) => $process->isRunning());
            while ($uids && (count($processes) < $this::THREADS)) {
                $uid = array_shift($uids);
                $io->note($uid.' process started');
                $processes[] = $process = new Process(['bin/console', 'app:process:task', $uid]);
                $process->start();
            }
            sleep(1);
        };

        $io->success('Chunk processed');

        return Command::SUCCESS;
    }
}
