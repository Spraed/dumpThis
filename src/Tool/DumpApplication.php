<?php

namespace Spraed\DumpThis\Tool;

use Spraed\DumpThis\Command\DumpCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\HelpCommand;
use Symfony\Component\Console\Command\ListCommand;
use Symfony\Component\Console\Input\InputInterface;

/**
 * DumpCommand
 *
 * @author DerStoffel <derstoffel@posteo.de>
 */
class DumpApplication extends Application
{
    /**
     * add DumpCommand to available commands
     *
     * @return array|\Symfony\Component\Console\Command\Command[]
     */
    protected function getDefaultCommands()
    {
        $defaultCommands = parent::getDefaultCommands();

        $defaultCommands[] = new DumpCommand();

        return $defaultCommands;
    }
}
 