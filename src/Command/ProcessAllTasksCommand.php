<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

class ProcessAllTasksCommand extends Command
{
    public const THREADS = 16;

    public const PACK = 512;

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
            ->setDescription('All tasks processor')
            ->addOption('threads', 't', InputOption::VALUE_OPTIONAL, 'Threads count, default='.$this::THREADS)
            ->addOption('pack', 'p', InputOption::VALUE_OPTIONAL, 'Pack size, default='.$this::PACK);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $threads = (int)$input->getOption('threads') ?: $this::THREADS;
        $pack = (int)$input->getOption('pack') ?: $this::PACK;
        $io->note("Chunk process started, pack={$pack}, threads={$threads}");
        $uids = $this->connection->createQueryBuilder()
            ->select('uid')
            ->from('task')
            ->distinct()
            ->setMaxResults($pack)
            ->execute()
            ->fetchFirstColumn();

        $processes = [];

        while ($uids || $processes) {
            $processes = array_filter(
                $processes,
                function ($process) use ($io, &$uids) {
                    /** @var Process $process */

                    ['uid' => $uid, 'process' => $process] = $process;
                    if (!$process->isRunning()) {
                        if ($process->getExitCode()) {
                            $io->error($uid.' failed and added again');
                            $uids[] = $uid;
                        } else {
                            $io->note($uid.' processed');
                        }
                    }

                    return $process->isRunning();
                }
            );
            while ($uids && (count($processes) < $threads)) {
                $uid = array_shift($uids);
                $io->note($uid.' process started');
                $processes[]
                    = [
                    'uid'     => $uid,
                    'process' => $process = new Process(['bin/console', 'app:process:task', $uid]),
                ];
                $process->start();
            }
            sleep(1);
        };

        $io->success('Pack processed');

        return Command::SUCCESS;
    }
}
