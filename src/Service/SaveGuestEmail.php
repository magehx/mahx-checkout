<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Service;

use Exception;
use Magento\Checkout\Helper\Data as CheckoutHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Psr\Log\LoggerInterface;
use MageHx\MahxCheckout\Data\GuestEmailData;
use MageHx\MahxCheckout\Model\QuoteDetails;

class SaveGuestEmail
{
    public function __construct(
        private readonly QuoteDetails $quote,
        private readonly LoggerInterface $logger,
        private readonly CheckoutHelper $checkoutHelper,
        private readonly CartRepositoryInterface $quoteRepository,
    ) {
    }

    /**
     * @param GuestEmailData $guestEmailData
     * @return void
     * @throws LocalizedException
     */
    public function execute(GuestEmailData $guestEmailData): void
    {
        try {
            $quote = $this->quote->getInstance();
            if (!$this->checkoutHelper->isAllowedGuestCheckout($quote)) {
                throw new LocalizedException(
                    __(
                        'Guest checkout is not allowed. ' .
                        'Register a customer account or login with existing one.'
                    )
                );
            }
            $quote->setCustomerEmail($guestEmailData->email);
            $this->quoteRepository->save($quote);
        } catch (LocalizedException $exception) {
            throw $exception;
        } catch (Exception $e) {
            $this->logger->error('MahxCheckout::saveGuestEmail exception', ['exception' => $e]);
            throw new LocalizedException(__('Guest email save encountered some issue. Please try again'), $e);
        }
    }
}
