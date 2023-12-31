<?php

namespace Madfoat\MadfoatPayments\Controller\Standard;

class Redirect extends \Madfoat\MadfoatPayments\Controller\MadfoatPayments {

    public function execute() {
        $order = $this->getOrder();

        if ($order->getBillingAddress()) {
            $payment_url = $this->getMadfoatModel()->buildMadfoatRequest($order);
            if ($payment_url) {
              $ivp_framed = ($this->getMadfoatModel()->getConfig('ivp_framed') == 1 && $this->getMadfoatModel()->isSSL()) ? true : false;

              $ivp_framed = false;
              // Check if Payment mode = framed & SSL is active, else proceed with regular checkout page.
              if($ivp_framed){
                  $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                  $customerSession = $objectManager->get('Magento\Customer\Model\Session');
                  $customerSession->setMadfoatPaymentUrl($payment_url);
                  $_SESSION['madfoat_payment_url'] = $payment_url;
                  $this->getResponse()->setRedirect($this->getMadfoatHelper()->getUrl('madfoat/standard/process') . "?tx=" . time());
              }else{
                  $this->getResponse()->setRedirect($payment_url);
              }
            } else {
              $this->_cancelPayment();
              $this->_checkoutSession->restoreQuote();
              if(isset($_SESSION['madfoat_error_message'])){
                $this->_messageManager->addError(__($_SESSION['madfoat_error_message']));
                unset($_SESSION['madfoat_error_message']);
              }
              $this->_messageManager->addError(__('Sorry, unable to process your transaction at this time.'));
              $this->getResponse()->setRedirect($this->getMadfoatHelper()->getUrl('checkout/cart'));
            }
        } else {
            $this->_cancelPayment();
            $this->_checkoutSession->restoreQuote();
            $this->getResponse()->setRedirect($this->getMadfoatHelper()->getUrl('checkout'));
        }
    }

}
