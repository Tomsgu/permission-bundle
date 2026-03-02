<?php

declare(strict_types=1);

namespace Tomsgu\PermissionBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tomsgu\PermissionBundle\Loader\PermissionLoaderInterface;

class LoadPermissionDataCommand extends Command
{
    public function __construct(private PermissionLoaderInterface $permissionLoader)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('tomsgu:permission:load')
            ->setDescription('Loads a permission data from config files to the db.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->permissionLoader->loadPermissions();

        $output->writeln('<info>Permissions were successfully loaded.</info>');

        return Command::SUCCESS;
    }
}
