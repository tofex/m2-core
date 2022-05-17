<?php

namespace Tofex\Core\Model\Config\Source\Order;

use Magento\Sales\Model\Config\Source\Order\Status;
use Magento\Sales\Model\Order\Config;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2022 Tofex UG (http://www.tofex.de)
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Statuses
    extends Status
{
    /**
     * @param Config $orderConfig
     */
    public function __construct(Config $orderConfig)
    {
        parent::__construct($orderConfig);

        $this->_stateStatuses = null;
    }
}
