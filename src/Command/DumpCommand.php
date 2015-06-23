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
 * Defines command execution and options
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
        $tables = $sourceConnection->fetchAll('SELECT name FROM sys.Tables order by name');

        // get columnnames
        $output->writeln('################## CREATE SCHEMA ##################');
        $tableColumns = array();
        foreach ($tables as $table) {
            $sql = 'SELECT name FROM sys.columns WHERE object_id = OBJECT_ID(\'' . $table['name'] . '\')';
            $tableColumn = $sourceConnection->fetchAll($sql);
            $this->createGoalTable($goal, $table['name'], $tableColumn);
            $tableColumns[$table['name']] = $tableColumn;
        }

        $output->writeln('################## INSERT DATA ##################');
        // get data by tablename and columnnames
        foreach ($tableColumns as $table => $columns) {
            $output->writeln('Table: ' . $table);

            $sql = 'SELECT * FROM ' . $table;
            $data = $sourceConnection->fetchAll($sql);
            $this->insertGoalTable($goal, $table, $data);
        }

        $sourceConnection->close();
        $output->writeln('################## DONE ##################');
    }

    /**
     * @param string $goal
     * @param string $table
     * @param array  $tableColumns
     *
     * @throws \Exception
     */
    private function createGoalTable($goal, $table, array $tableColumns)
    {
        $goalConnection = $this->getConnection($goal);
        $goalConnection->beginTransaction();
        try {
            $columnString = '';
            foreach ($tableColumns as $column) {
                $columnString .= '`' . $column['name'] . '` varchar(255),';
            };

            $columnString = substr_replace($columnString, '', -1);
            $sql = 'CREATE TABLE ' . $goalConnection->getDatabase() . '.' . $table . ' (' . $columnString . ')';

            $goalConnection->exec($sql);

            $goalConnection->commit();
        } catch (\Exception $e) {
            $goalConnection->rollBack();
            throw $e;
        }

        $goalConnection->close();
    }

    /**
     * @param string $goal
     * @param string $table
     * @param array  $datas
     *
     * @throws \Exception
     */
    private function insertGoalTable($goal, $table, array $datas)
    {
        if (0 < count($datas)) {
            foreach ($datas as $data) {
                $goalConnection = $this->getConnection($goal);
                $goalConnection->beginTransaction();
                try {
                    $columnString = '';
                    $valueString = '';

                    foreach ($data as $key => $value) {
                        $columnString .= '`' . $key . '`,';
                        $valueString .= $goalConnection->quote($value) . ',';
                    }

                    $columnString = substr_replace($columnString, '', -1);
                    $valueString = substr_replace($valueString, '', -1);
                    $sql = 'INSERT INTO ' . $goalConnection->getDatabase() . '.' . $table . ' (' . $columnString . ') VALUES (' . $valueString . ')';

                    $goalConnection->exec($sql);

                    $goalConnection->commit();
                } catch (\Exception $e) {
                    $goalConnection->rollBack();
                    throw $e;
                }

                $goalConnection->close();
            };
        }
    }

    /**
     * @param $config
     *
     * @return \Doctrine\DBAL\Connection
     */
    private function getConnection($config)
    {
        $array = Yaml::parse(file_get_contents(__DIR__ . '/../../app/config/config.yml'));

        $connectionParams = $array['doctrine']['dbal']['connections'][$config];

        $config = new Configuration();
        $connection = DriverManager::getConnection($connectionParams, $config);

        return $connection;
    }
}
 