<?php

namespace Tofex\Core\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2022 Tofex UG (http://www.tofex.de)
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class TypeId
    implements ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => '', 'label' => __('--Please Select--')],
            ['value' => 'bundle', 'label' => __('Bundle')],
            ['value' => 'configurable', 'label' => __('Configurable')],
            ['value' => 'downloadable', 'label' => __('Downloadable')],
            ['value' => 'grouped', 'label' => __('Grouped')],
            ['value' => 'simple', 'label' => __('Simple')],
            ['value' => 'virtual', 'label' => __('Virtual')]
        ];
    }

    /**
     * @return array
     */
    public function toOptions(): array
    {
        return [
            'bundle'       => __('Bundle'),
            'configurable' => __('Configurable'),
            'downloadable' => __('Downloadable'),
            'grouped'      => __('Grouped'),
            'simple'       => __('Simple'),
            'virtual'      => __('Virtual')
        ];
    }
}
