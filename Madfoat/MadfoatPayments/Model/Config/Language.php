<?php

namespace Madfoat\MadfoatPayments\Model\Config;
class Language implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return array (
            'en' => __("English"),
            'ar' => __("Arabic"),
        );
    }
}
