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
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $source = $input->getOption('source');
        $goal = $input->getOption('goal');

        // connect to mssql
        $sourceConnection = $this->getConnection($source);
        $sourceConnection->connect();

        // get tablenames
//        $tables = $sourceConnection->fetchArray('SELECT name FROM sys.Tables WHERE name = \'D_Adressen\' order by name');
        $tables = $sourceConnection->fetchAll('SELECT name, object_id FROM sys.Tables order by name');

        // get columnnames
        $tableColumns = array();
        foreach ($tables as $table) {
            $sql = 'SELECT name FROM sys.columns WHERE object_id = OBJECT_ID(\'' . $table . '\')';
            $tableColumn = $sourceConnection->fetchArray($sql);
            $this->createGoalTable($goal, $table, $tableColumn);
            $tableColumns[$table] = $tableColumn;
        }

        // get data by tablename and columnnames
        $tableData = array();
        foreach ($tableColumns as $table => $columns) {
            $sql = 'SELECT ' . implode(',', $columns) . ' FROM ' . $table . '';
            $tableData[$table] = $sourceConnection->fetchArray($sql);
        }

        $sourceConnection->close();

        // connect to mysql
        // write tablenames
        // write column names per table
        // write data
    }

    private function createGoalTable($goal, $table, $tableColumns)
    {
        $goalConnection = $this->getConnection($goal);
        $goalConnection->beginTransaction();
        try {

            $columnString = '';
            foreach ($tableColumns as $column) {
                $columnString .= $column . ' varchar(255),';
            }

            $columnString = substr_replace($columnString, '', -1);
            $sql = 'CREATE TABLE ' . $table . ' (' . $columnString . ')';

            $goalConnection->exec($sql);

            $goalConnection->commit();
        } catch (\Exception $e) {
            $goalConnection->rollBack();
            throw $e;
        }

        $goalConnection->close();
    }

    /**
     * @param $config
     *
     * @return \Doctrine\DBAL\Connection
     */
    private function getConnection($config)
    {
        $array = Yaml::parse(file_get_contents(__DIR__ . '/../Resources/config/config.yml'));

        $connectionParams = $array['doctrine']['dbal']['connections'][$config];

        $config = new Configuration();
        $connection = DriverManager::getConnection($connectionParams, $config);

        return $connection;
    }
}
 