<?php

namespace Spraed\DumpThis\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * DumpCommand
 *
 * @author DerStoffel <derstoffel@posteo.de>
 */
class DumpCommand extends Command
{
    /**
     *
     */
    protected function configure()
    {
        $this->setName('spraed:dump')
            ->setDescription('Get MSSQL tables and its content, write to MySQL database')
            ->addOption('source', null, InputOption::VALUE_REQUIRED, 'Connection to pull from')
            ->addOption('goal', null, InputOption::VALUE_REQUIRED, 'Connection to dump to');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $source = $input->getOption('source');
        $goal = $input->getOption('goal');


        // connect to mssql

        // get tablenames
        // get column names per table
        // get data

        // connect to mysql
        // write tablenames
        // write column names per table
        // write data
    }

    private function getConnection($config)
    {
        $array = Yaml::parse(file_get_contents(__DIR__ .'../Resources/config/config.yml'));
    }
}
 