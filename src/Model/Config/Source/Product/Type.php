<?php

namespace Tofex\Core\Model\Config\Source\Product;

use Magento\Framework\Option\ArrayInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2022 Tofex UG (http://www.tofex.de)
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Type
    implements ArrayInterface
{
    /** @var \Magento\Catalog\Model\Product\Type */
    protected $productType;

    /**
     * @param \Magento\Catalog\Model\Product\Type $productType
     */
    public function __construct(\Magento\Catalog\Model\Product\Type $productType)
    {
        $this->productType = $productType;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $options = [['value' => 'all', 'label' => __('All Product Types')]];

        foreach ($this->productType->getOptionArray() as $typeId => $label) {
            $options[] = [
                'value' => $typeId,
                'label' => $label
            ];
        }

        return $options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray(): array
    {
        $options = ['all' => __('All Product Types')];

        foreach ($this->productType->getOptionArray() as $typeId => $label) {
            $options[ $typeId ] = $label;
        }

        return $options;
    }
}
