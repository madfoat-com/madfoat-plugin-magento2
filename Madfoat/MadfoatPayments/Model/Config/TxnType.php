<?php

namespace Madfoat\MadfoatPayments\Model\Config;
class TxnType implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return array (
            'sale' => __("Sale"),
            'auth' => __("Auth"),
        );
    }
}
