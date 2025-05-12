<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Plugin\Checkout\IndexController;

use Magento\Checkout\Controller\Index\Index as CheckoutIndexController;
use Magento\Framework\Controller\ResultFactory;
use MageHx\MahxCheckout\Model\Config;

class UseMahxCheckoutForMagentoCheckout
{
    public function __construct(
        private readonly Config $config,
        private readonly ResultFactory $resultFactory,
    ) {
    }

    public function aroundExecute(CheckoutIndexController $subject, callable $proceed)
    {
        if ($this->config->isEnabled()) {
            $result = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            $result->setModule('mahxcheckout')->setController('index')->forward('index');
        } else {
            $result = $proceed();
        }

        return $result;
    }
}
