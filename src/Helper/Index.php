<?php

namespace Tofex\Core\Helper;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Indexer\StateInterface;
use Magento\Indexer\Model\Indexer;
use Magento\Indexer\Model\Indexer\State;
use Magento\Indexer\Model\ResourceModel\Indexer\StateFactory;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2022 Tofex UG (http://www.tofex.de)
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Index
{
    /** @var LoggerInterface */
    protected $logging;

    /** @var StateFactory */
    protected $stateResourceFactory;

    /**
     * @param LoggerInterface $logging
     * @param StateFactory    $stateResourceFactory
     */
    public function __construct(LoggerInterface $logging, StateFactory $stateResourceFactory)
    {
        $this->logging = $logging;
        $this->stateResourceFactory = $stateResourceFactory;
    }

    /**
     * @param Indexer $indexer
     *
     * @throws AlreadyExistsException
     * @throws Throwable
     */
    public function runIndexProcess(Indexer $indexer)
    {
        if ($indexer->isScheduled()) {
            /** @var State $state */
            $state = $indexer->getState();

            $state->setStatus(StateInterface::STATUS_INVALID);

            $this->stateResourceFactory->create()->save($state);

            return;
        }

        $this->logging->info(sprintf('Starting indexer with id: %s', $indexer->getId()));

        $indexer->reindexAll();

        $this->logging->info(sprintf('Finished indexer with id: %s', $indexer->getId()));
    }
}
