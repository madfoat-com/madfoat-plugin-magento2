<?php

namespace Madfoat\MadfoatPayments\Controller\Standard;

class Cancel extends \Madfoat\MadfoatPayments\Controller\MadfoatPayments {

    public function execute() {
    	$order_id = $this->getRequest()->getParam('coid');
    	$validateResponse = $this->getMadfoatModel()->validateResponse($order_id);
        if(!$validateResponse['status']) {
		   	$this->messageManager->addError($validateResponse['message']);
        }
        $this->_cancelPayment();
        $this->_checkoutSession->restoreQuote();
        $this->getResponse()->setRedirect(
            $this->getMadfoatHelper()->getUrl('checkout/cart')
        );
    }

}
