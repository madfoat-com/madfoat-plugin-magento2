<?php

namespace Madfoat\MadfoatPayments\Controller\Standard;

class Iframe extends \Madfoat\MadfoatPayments\Controller\MadfoatPayments {

    public function execute() {
        $order = $this->getOrder();

        if ($order->getBillingAddress()) {
            $payment_url = $this->getMadfoatModel()->buildMadfoatRequest($order);
            echo $payment_url; exit;              
        } else {
        }
    }
}
