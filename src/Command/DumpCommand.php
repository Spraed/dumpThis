<?php

namespace Spraed\DumpThis\Command;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
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
     * Defines cli command and its options
     */
    protected function configure()
    {
        $this->setName('spraed:dump')
            ->setDescription('Get MSSQL tables and its content, write to MySQL database')
            ->addOption('source', null, InputOption::VALUE_OPTIONAL, 'Connection to pull from', 'mssql')
            ->addOption('goal', null, InputOption::VALUE_OPTIONAL, 'Connection to dump to', 'mysql');
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
        $sourceConnection = $this->getConnection($source);
        // get tablenames
        // get column names per table
        // get data

        // connect to mysql
        $goalConnection = $this->getConnection($source);
        // write tablenames
        // write column names per table
        // write data
    }

    /**
     * @param $config
     *
     * @return \Doctrine\DBAL\Connection
     */
    private function getConnection($config)
    {
        $array = Yaml::parse(file_get_contents(__DIR__ . '../Resources/config/config.yml'));

        $connectionParams = $array['doctrine']['dbal']['connections'][$config];

        $config = new Configuration();
        $connection = DriverManager::getConnection($connectionParams, $config);

        return $connection;
    }
}
 