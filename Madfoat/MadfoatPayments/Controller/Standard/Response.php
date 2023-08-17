<?php

namespace Madfoat\MadfoatPayments\Controller\Standard;

class Response extends \Madfoat\MadfoatPayments\Controller\MadfoatPayments {

    public function execute() {
        $order_id = $this->getRequest()->getParam('coid');
        $validateResponse = $this->getMadfoatModel()->validateResponse($order_id);
        if($validateResponse['status']) {
            $returnUrl = $this->getMadfoatHelper()->getUrl('checkout/onepage/success');
        } else {
            $this->messageManager->addError($validateResponse['message']);
            $this->_cancelPayment();
            $this->_checkoutSession->restoreQuote();
            $returnUrl = $this->getMadfoatHelper()->getUrl('checkout/onepage/failure');
        }
        $this->getResponse()->setRedirect($returnUrl);
    }

}
