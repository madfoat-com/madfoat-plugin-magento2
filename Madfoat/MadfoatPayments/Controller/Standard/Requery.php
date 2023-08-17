<?php

namespace Madfoat\MadfoatPayments\Controller\Standard;

class Requery extends \Madfoat\MadfoatPayments\Controller\MadfoatPayments {

    public function execute() {
        $returnUrl = $this->getMadfoatHelper()->getUrl('checkout/onepage/success');
        echo "<pre>";
        $collection = $this->_orderCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->setOrder('created_at','desc')
            ->addFieldToFilter('status',
                ['eq' => 'pending']
            );
        $collection->getSelect()
            ->join(
                ["sop" => "sales_order_payment"],
                'main_table.entity_id = sop.parent_id',
                array('method')
            )
            ->where('sop.method = ?','madfoat_madfoatpayments');
        $collection->setOrder(
            'created_at',
            'desc'
        );

        foreach ($collection as $order) {
            $orderId = $order->getIncrementId();
            $resp = $this->getMadfoatModel()->validateResponse($orderId);
            echo "Processed Order Id: " . $orderId . "<br/>";
            echo "Response: " . print_r($resp) . "<br/>";
        }
        print_r("Processing Completed."); exit;
    }
}
