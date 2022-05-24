<?php

namespace Tofex\Core\Model\Config\Source\Attribute;

use Magento\Catalog\Model\Config;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2022 Tofex UG (http://www.tofex.de)
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class SortBy
    implements OptionSourceInterface
{
    /** @var Config */
    protected $catalogConfig;

    /**
     * @param Config $catalogConfig
     */
    public function __construct(Config $catalogConfig)
    {
        $this->catalogConfig = $catalogConfig;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray(): array
    {
        $attributeOrder = [['value' => '', 'label' => '-- Please select --']];

        foreach ($this->catalogConfig->getAttributeUsedForSortByArray() as $attributeCode => $attributeLabel) {
            $attributeOrder[] = ['value' => $attributeCode, 'label' => $attributeLabel];
        }

        return $attributeOrder;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array
     */
    public function toOptions(): array
    {
        $attributeOrder = [];

        foreach ($this->catalogConfig->getAttributeUsedForSortByArray() as $attributeCode => $attributeLabel) {
            $attributeOrder[ $attributeCode ] = $attributeLabel;
        }

        return $attributeOrder;
    }
}
