<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Block;

use Logicexception;
use MageHx\MahxCheckout\Data\PaymentMethodData;
use MageHx\MahxCheckout\Model\QuoteDetails;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;
use Magento\Quote\Api\PaymentMethodManagementInterface;

class PaymentMethods extends Template
{
    private ?array $methods = null;
    private ?array $dataClassList = null;

    public function __construct(
        private readonly QuoteDetails $quote,
        private readonly Json $jsonSerializer,
        private readonly PaymentMethodManagementInterface $paymentMethodManagement,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return PaymentMethodData[]
     */
    public function getPaymentMethods(): array
    {
        $this->prepareDataClassFromPaymentRenderers();

        if (!$this->methods) {
            $this->preparePaymentMethods();
        }

        return $this->methods;
    }

    public function getPaymentHtml(PaymentMethodData $paymentMethodData): string
    {
        return $this->getPaymentRendererBlockByCode($paymentMethodData->code)
            ->setPaymentData($paymentMethodData)
            ->toHtml();
    }

    // @todo before after events to modify rules
    public function getValidationJson(): string
    {
        $this->prepareDataClassFromPaymentRenderers();
        $selectedMethod = $this->quote->getPaymentMethod();
        $dataClass = $this->getDataClassByCode($selectedMethod->getMethod() ?? '');
        $paymentData = $dataClass::from(['code' => '']);

        return $this->jsonSerializer->serialize($paymentData->rules());
    }

    public function isVirtualCart(): bool
    {
        return $this->quote->isVirtualQuote();
    }

    public function prepareDataClassFromPaymentRenderers(): void
    {
        if ($this->dataClassList) {
            return;
        }

        foreach ($this->paymentMethodManagement->getList($this->quote->getId()) as $paymentMethod) {
            $renderer = $this->getPaymentRendererBlockByCode($paymentMethod->getCode());
            $dataClass = $renderer->getData('data_class') ?? PaymentMethodData::class;

            $this->dataClassList[$paymentMethod->getCode()] = $dataClass;
        }
    }

    public function preparePaymentMethods(): void
    {
        foreach($this->paymentMethodManagement->getList($this->quote->getId()) as $paymentMethod) {
            $dataClass = $this->getDataClassByCode($paymentMethod->getCode());
            $paymentData = $dataClass::from([
                'code' => $paymentMethod->getCode(),
                'title' => $paymentMethod->getTitle()
            ]);
            $this->methods[$paymentMethod->getCode()] = $paymentData;
        }
    }

    private function getPaymentRendererBlockByCode(string $methodCode): PaymentRendererBlockInterface|bool
    {
        $renderer = $this->getChildBlock($methodCode) ?: $this->getChildBlock('default');

        if (!$renderer instanceof PaymentRendererBlockInterface) {
            throw new Logicexception('Payment renderer must implement PaymentRendererBlockInterface');
        }

        return $renderer;
    }

    private function getDataClassByCode(string $methodCode): string
    {
        return $this->dataClassList[$methodCode] ?? PaymentMethodData::class;
    }
}
