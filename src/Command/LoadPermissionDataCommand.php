<?php

declare(strict_types=1);

namespace Tomsgu\PermissionBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Tomsgu\PermissionBundle\Loader\PermissionSynchronizer;

class LoadPermissionDataCommand extends Command
{
    public function __construct(private PermissionSynchronizer $synchronizer)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('tomsgu:permission:load')
            ->setDescription('Synchronizes the stored permissions with the ones declared in the configuration.')
            ->addOption('prune', null, InputOption::VALUE_NONE, 'Delete stored permissions that are no longer declared.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $result = $this->synchronizer->synchronize((bool) $input->getOption('prune'));

        foreach (['Created' => $result->created, 'Updated' => $result->updated, 'Removed' => $result->removed] as $action => $names) {
            if ($names !== []) {
                $output->writeln(sprintf('%s (%d): %s', $action, count($names), implode(', ', $names)));
            }
        }
        if ($result->undeclared !== []) {
            $output->writeln(sprintf(
                '<comment>No longer declared (%d), run again with --prune to delete them: %s</comment>',
                count($result->undeclared),
                implode(', ', $result->undeclared)
            ));
        }

        $output->writeln('<info>Permissions were successfully synchronized.</info>');

        return Command::SUCCESS;
    }
}
