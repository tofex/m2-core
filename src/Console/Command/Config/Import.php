<?php

namespace Tofex\Core\Console\Command\Config;

use Magento\Framework\App\Area;
use Symfony\Component\Console\Input\InputOption;
use Tofex\Core\Console\Command\Command;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2022 Tofex UG (http://www.tofex.de)
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Import
    extends Command
{
    /**
     * @return string
     */
    protected function getCommandName(): string
    {
        return 'config:import-json';
    }

    /**
     * @return string
     */
    protected function getCommandDescription(): string
    {
        return 'Import JSON file with configuration';
    }

    /**
     * @return array
     */
    protected function getCommandDefinition(): array
    {
        return [
            new InputOption('file', null, InputOption::VALUE_REQUIRED, 'The file to import')
        ];
    }

    /**
     * @return string
     */
    protected function getClassName(): string
    {
        return Script\Import::class;
    }

    /**
     * @return string
     */
    protected function getArea(): string
    {
        return Area::AREA_ADMINHTML;
    }
}
