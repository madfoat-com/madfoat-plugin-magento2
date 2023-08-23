<?php

namespace Madfoat\MadfoatPayments\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Vault\Api\PaymentTokenManagementInterface;

class MadfoatPaymentsConfigProvider implements ConfigProviderInterface
{
    protected $methodCode = "madfoat_madfoatpayments";

    protected $method;
    protected $paymentTokenManagement;
    protected $customerSession;

    public function __construct(
        PaymentHelper $paymentHelper,
        \Magento\Customer\Model\Session $customerSession,
        PaymentTokenManagementInterface $paymentTokenManagement
    ) {
        $this->customerSession = $customerSession;
        $this->paymentTokenManagement = $paymentTokenManagement;
        $this->method = $paymentHelper->getMethodInstance($this->methodCode);
    }

    public function getConfig()
    {
        $savedCards = array(); 

        if($this->customerSession->isLoggedIn()){
            $customerId = $this->customerSession->getCustomerId();

            //Madfoat Saved Cards
            $savedCards = $this->getMadfoatSavedCards($customerId);

            $savedCardsList = $this->paymentTokenManagement->getListByCustomerId($customerId);
            foreach ($savedCardsList as $currentCard) {
                if($currentCard['is_active'] == 1 && $currentCard['is_visible'] == 1 && $currentCard['payment_method_code'] == 'madfoat_madfoatpayments'){
                    $cardDetails = json_decode($currentCard->getDetails(), true);

                    $cardName = (isset($cardDetails['type'])) ? $cardDetails['type'] : '';
                    $cardEnding = (isset($cardDetails['last4'])) ? $cardDetails['last4'] : '';
                    $cardExpMonth = (isset($cardDetails['expiry_month'])) ? $cardDetails['expiry_month'] : '';
                    $cardExpYear = (isset($cardDetails['expiry_year'])) ? $cardDetails['expiry_year'] : '';

                    $cardObj = array(
                        'txn_id' => $currentCard->getGatewayToken(),
                        'name' => $cardName . " ending with " . $cardEnding . " Expiry(" . $cardExpMonth . "/" . $cardExpYear . ")"
                    );

                    $savedCards[] = $cardObj;
                }   
            }


        }

        return $this->method->isAvailable() ? [
            'payment' => [
                'madfoat_madfoatpayments' => [
                    'redirectUrl' => $this->getRedirectUrl(),
                    'iframeUrl' => $this->getIframeUrl(),
                    'frameMode' => $this->getFramedMode(),
                    'storeId' => $this->method->getConfigData("store_id"),
                    'testMode' => $this->method->getConfigData("sandbox"),
                    'savedCards' => $savedCards,
					'language' => $this->method->getConfigData("madfoat_lang")
                ]
            ]
        ] : [];
    }

    protected function getMadfoatSavedCards($custId)
    {
        $madfoatCards = array();

        $storeId = $this->method->getConfigData("store_id");
        $authKey = $this->method->getConfigData("auth_key");
        $testMode = $this->method->getConfigData("sandbox");

        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://uat-secure.madfoat.dev/gateway/savedcardslist.json",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => "api_storeid=" . $storeId . "&api_authkey=" . $authKey . "&api_testmode=" . $testMode . "&api_custref=" . $custId,
          CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache",
            "content-type: application/x-www-form-urlencoded",
          ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if (!$err) {
            $resp = json_decode($response, true);
            if(isset($resp['SavedCardListResponse']) && $resp['SavedCardListResponse']['Code'] == 200){
                if(isset($resp['SavedCardListResponse']['data'])){
                    foreach ($resp['SavedCardListResponse']['data'] as $key => $row) {
                        $madfoatCards[] = array(
                            'txn_id' => $row['Transaction_ID'],
                            'name' => $row['Name']
                        );
                    }
                }
            }
        }

        return $madfoatCards;
    }

    protected function getRedirectUrl()
    {
        return $this->method->getRedirectUrl();
    }

    protected function getIframeUrl()
    {
        return $this->method->getIframeUrl();
    }

    protected function getFramedMode()
    {
        return $this->method->getFramedMode();
    }
}
