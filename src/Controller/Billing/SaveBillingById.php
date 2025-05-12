<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Controller\Billing;

use Exception;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Quote\Model\Quote\Address;
use MageHx\MahxCheckout\Controller\Form\ComponentAction;
use MageHx\MahxCheckout\Controller\Form\ComponentAction\Context;
use MageHx\MahxCheckout\Data\AddressData;
use MageHx\MahxCheckout\Model\QuoteDetails;
use MageHx\MahxCheckout\Service\CustomerAddressService;
use MageHx\MahxCheckout\Service\SaveBillingAddress as SaveBillingAddressService;

class SaveBillingById extends ComponentAction
{
    public function __construct(
        Context $context,
        private readonly QuoteDetails $quote,
        private readonly CustomerAddressService $customerAddressService,
        private readonly SaveBillingAddressService $saveBillingAddressService,
    ) {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        $addressId = $this->getRequest()->getParam('billing_address_id');
        $shippingAddress = $this->quote->getShippingAddress();

        try {
            $addressData = match($addressId) {
                'shipping' => $this->prepareAddressData($shippingAddress, isSameAsShipping: true),
                default => $this->prepareAddressDataFromCustomerAddressId((int)$addressId),
            };
            $this->saveBillingAddressService->execute($addressData);
            return $this->getCheckoutContentResponse();
        } catch (Exception) {
            return $this->getEmptyResponse()->setHeader('HX-Reswap', 'none');
        }
    }

    public function prepareAddressData(AddressInterface|Address $address, bool $isSameAsShipping): AddressData
    {
        $customerAddressId = $address instanceof AddressInterface ? $address->getId() : $address->getCustomerAddressId();

        return AddressData::from([
            'firstname' => $address->getFirstname(),
            'lastname' => $address->getLastname(),
            'street' => $address->getStreet(),
            'city' => $address->getCity(),
            'country_id' => $address->getCountryId(),
            'postcode' => $address->getPostcode(),
            'telephone' => $address->getTelephone(),
            'region' => $address->getRegionId() ?: $address->getRegion(),
            'same_as_billing' => $isSameAsShipping,
            'customer_address_id' => $customerAddressId,
            'customer_id' => $address->getCustomerId(),
        ]);
    }

    public function prepareAddressDataFromCustomerAddressId(int $customerAddressId): AddressData
    {
        $address = $this->customerAddressService->getCurrentCustomerAddressById($customerAddressId);
        $shippingAddress = $this->quote->getShippingAddress();
        $isSameAsShipping = (int)$shippingAddress->getCustomerAddressId() === (int)$address->getId();

        return $this->prepareAddressData($address, $isSameAsShipping);
    }
}
