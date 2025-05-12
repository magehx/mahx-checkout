<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Controller\Billing;

use Exception;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\ResultInterface;
use MageHx\MahxCheckout\Controller\Form\ComponentAction;
use MageHx\MahxCheckout\Controller\Form\ComponentAction\Context;
use MageHx\MahxCheckout\Data\AddressData;
use MageHx\MahxCheckout\Model\QuoteDetails;
use MageHx\MahxCheckout\Service\SaveBillingAddress;

class BillingAddressPost extends ComponentAction
{
    public function __construct(
        Context $context,
        private readonly QuoteDetails $quote,
        private readonly SaveBillingAddress $saveBillingAddressService,
    ) {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        // prepare billing address data and validate
        $billingAddressData = $this->getBillingAddressData();
        try {
            if (!$billingAddressData->same_as_billing) {
                $billingAddressData->validate();
            }
            $this->saveBillingAddressService->execute($billingAddressData);
            return $this->getCheckoutContentResponse();
        } catch (Exception $e) {
            $this->prepareErrorNotificationsWithFormData($billingAddressData->toArray(), $e);
            return $this->getNotificationsResponse()->setHeader('HX-Reswap', 'none');
        }
    }

    public function getBillingAddressData(): AddressData
    {
        $shippingAddress = $this->quote->getShippingAddress();
        $isSameAsShipping = (bool) $this->getPostData('is_billing_same');
        $data = $isSameAsShipping ? $shippingAddress->getData() : (array)$this->getRequest()->getPost();
        $data['street'] = $isSameAsShipping ? $shippingAddress->getStreet() : ($data['street'] ?? []);

        return AddressData::from([
            'firstname' => $data['firstname'] ?? '',
            'lastname' => $data['lastname'] ?? '',
            'street' => $data['street'] ?? [],
            'city' => $data['city'] ?? '',
            'country_id' => $data['country_id'] ?? '',
            'postcode' => $data['postcode'] ?? '',
            'telephone' => $data['telephone'] ?? '',
            'region' => $data['region'] ?? '',
            'same_as_billing' => $isSameAsShipping,
        ]);
    }
}
