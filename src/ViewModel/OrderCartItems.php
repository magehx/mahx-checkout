<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\ViewModel;

use Magento\Checkout\Model\Cart\ImageProvider;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Api\Data\TotalsInterface;
use Magento\Quote\Model\Cart\Totals\Item as CartTotalsItem;
use MageHx\MahxCheckout\Data\OrderSummaryCartItem;
use MageHx\MahxCheckout\Data\ProductImageData;
use MageHx\MahxCheckout\Model\Config;

class OrderCartItems implements ArgumentInterface
{
    private array $cartTotals = [];

    public function __construct(
        private readonly CheckoutSession $checkoutSession,
        private readonly ImageProvider $imageProvider,
        private readonly CartTotalRepositoryInterface $cartTotalRepository,
        private readonly Config $config,
        private readonly PriceCurrencyInterface $priceCurrency,
        private readonly SerializerInterface $serializer,
    ) {
    }

    /**
     * @return list<string, OrderSummaryCartItem>
     */
    public function getCartItems(): array
    {
        $quoteItems = [];
        $images = $this->imageProvider->getImages($this->getQuoteId());

        foreach ($this->getCartTotalsItems() as $cartItem) {
            $quoteItems['item__' . $cartItem->getItemId()] = OrderSummaryCartItem::from([
                'id' => $cartItem->getItemId(),
                'name' => $cartItem->getName(),
                'qty' => $cartItem->getQty(),
                'price' => $this->getCartItemPriceHtml($cartItem),
                'image' => ProductImageData::from($images[$cartItem->getItemId()]) ?? null,
                'options' => $this->getCartItemOptions($cartItem),
            ]);
        }

        return $quoteItems;
    }

    public function getCartItemsTotalQty(): int
    {
        $cartItems = $this->getCartTotalsItems();

        if ($this->config->canShowCartItemsTotalQty()) {
            return (int) array_reduce(
                $cartItems,
                function ($accumulator, $item) {
                    $accumulator += $item->getQty();
                    return $accumulator;
                },
                initial: 0
            );
        }

        return count($cartItems);
    }

    private function getQuoteId(): int
    {
        return (int) $this->checkoutSession->getQuote()->getId();
    }

    /**
     * @return CartTotalsItem[]
     */
    private function getCartTotalsItems(): array
    {
        return $this->getCartTotals($this->getQuoteId())->getItems();
    }

    private function getCartItemPriceHtml(CartTotalsItem $cartItem): string
    {
        if ($this->config->isCartItemPriceIncludesTax()) {
            return $this->priceCurrency->format($cartItem->getPriceInclTax());
        }

        return $this->priceCurrency->format($cartItem->getPriceInclTax() - $cartItem->getTaxAmount());
    }

    private function getCartTotals(int $quoteId): TotalsInterface
    {
        if (isset($this->cartTotals[$quoteId])) {
            return $this->cartTotals[$quoteId];
        }

        $this->cartTotals[$quoteId] = $totals = $this->cartTotalRepository->get($this->getQuoteId());

        return $this->cartTotals[$quoteId];
    }

    private function getCartItemOptions(CartTotalsItem $cartItem): array
    {
        if ($cartItem->getOptions()) {
            return $this->serializer->unserialize($cartItem->getOptions());
        }

        return [];
    }
}
