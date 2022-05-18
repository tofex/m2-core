<?php

namespace Tofex\Core\Console\Command\Config\Script;

use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tofex\Core\Console\Command\Script;
use Tofex\Core\Helper\Config;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2022 Tofex UG (http://www.tofex.de)
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Export
    extends Script
{
    /** @var Config */
    protected $configHelper;

    /**
     * @param Config $configHelper
     */
    public function __construct(Config $configHelper)
    {
        $this->configHelper = $configHelper;
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws Exception
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $fileName = $input->getOption('file');
        $path = $input->getOption('path');

        if ($path == '/') {
            $path = '';
        }

        $this->configHelper->exportConfigJsonFile($fileName, $path);

        return 0;
    }
}
