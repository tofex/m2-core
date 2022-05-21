<?php

namespace Tofex\Core\Model\Config\Source\Order;

use Magento\Framework\Option\ArrayInterface;
use Magento\Sales\Model\Order\Config;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2022 Tofex UG (http://www.tofex.de)
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class State
    implements ArrayInterface
{
    /** @var Config */
    protected $orderConfig;

    /**
     * @param Config $orderConfig
     */
    public function __construct(Config $orderConfig)
    {
        $this->orderConfig = $orderConfig;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray(): array
    {
        $options = [];

        foreach ($this->toOptions() as $value => $label) {
            $options[] = ['value' => $value, 'label' => $label];
        }

        return $options;
    }

    /**
     * @return array
     */
    public function toOptions(): array
    {
        return $this->orderConfig->getStates();
    }
}
