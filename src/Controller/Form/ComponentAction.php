<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Controller\Form;

use Exception;
use MageHx\HtmxActions\Controller\HtmxAction;
use MageHx\HtmxActions\Controller\Result\HtmxRawFactory as HtmxRawResultFactory;
use MageHx\MahxCheckout\Model\Theme\CheckoutThemeInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\View\Result\LayoutFactory;
use Rkt\MageData\Exceptions\ValidationException;
use MageHx\MahxCheckout\Block\Notifications as NotificationsBlock;
use MageHx\MahxCheckout\Controller\Form\ComponentAction\Context;
use MageHx\MahxCheckout\Model\CheckoutDataStorage;
use MageHx\MahxCheckout\Service\HtmxHeaderManager;
use MageHx\MahxCheckout\Service\StepSessionManager;
use MageHx\MahxCheckout\Service\StepValidationService;

abstract class ComponentAction extends HtmxAction
{
    protected array $layouts = [];
    protected array $components = [];
    protected HtmxRawResultFactory $rawFactory;
    protected CheckoutDataStorage $checkoutDataStorage;
    protected LayoutFactory $layoutFactory;
    protected StepValidationService $stepValidationService;
    protected StepSessionManager $stepSessionManager;
    protected HtmxHeaderManager $htmxHeaderManager;
    private ?CheckoutThemeInterface $activeTheme;

    public function __construct(Context $context) {
        parent::__construct($context->htmxActionContext);
        $this->rawFactory = $context->rawFactory;
        $this->layoutFactory = $context->layoutFactory;
        $this->checkoutDataStorage = $context->checkoutDataStorage;
        $this->htmxHeaderManager = $context->htmxHeaderManager;
        $this->stepSessionManager = $context->stepSessionManager;
        $this->stepValidationService = $context->stepValidationService;
        $this->activeTheme = $context->activeTheme;

        $this->updateStepByRequest();
    }

    public function getCheckoutContentResponse(): ResultInterface
    {
        return $this->setHandles($this->getCurrentStepLayoutHandles())
            ->getBlockResponse('checkout.main.content');
    }

    public function getComponentResponse(
        string $componentName,
        bool $withNotification = false,
        string $additionalHtml = ''
    ): ResultInterface {
        $this->setHandles($this->getCurrentStepLayoutHandles());

        if (!$withNotification) {
            return $this->getBlockResponse($componentName, additionalHtml: $additionalHtml);
        }

        $notificationHtml = $this->getNotificationBlock()?->toHtml() ?? '';

        return $this->getBlockResponse($componentName, additionalHtml: $additionalHtml . $notificationHtml);
    }

    public function getMultiComponentResponse(array $componentNames): ResultInterface
    {
        $this->setHandles($this->getCurrentStepLayoutHandles());

        return $this->getMultiBlockResponse($componentNames);
    }

    public function getNotificationsResponse(): ResultInterface
    {
        return $this->setHandles($this->getCurrentStepLayoutHandles())
            ->getBlockResponse('checkout.notifications');
    }

    public function addValidationErrorMessages(array $errors): void
    {
        $this->getNotificationBlock()?->setValidationErrors($errors);
    }

    public function addGenericErrorMessage(string|Phrase $message): void
    {
        $this->getNotificationBlock()?->setGenericError($message);
    }

    public function addSuccessMessage(string|Phrase $message): void
    {
        $this->getNotificationBlock()?->setSuccessMessage($message);
    }

    public function prepareErrorNotificationsWithFormData(array $formData, Exception $exception): void
    {
        if ($exception instanceof ValidationException) {
            $this->addValidationErrorMessages($exception->getErrors());
        } else {
            $this->addGenericErrorMessage($exception->getMessage());
        }

        $this->checkoutDataStorage->setData($formData);
    }

    public function isStepSaveDataRequest(): bool
    {
        return $this->activeTheme->hasSaveDataUrl($this->getRequest()->getRequestUri());
    }

    protected function proceedToNextStep(): self
    {
        $initialStep = $this->activeTheme->getInitialStep();
        $currentStep = $this->stepSessionManager->getStepData() ?? $initialStep;
        $nextStep = $this->activeTheme->getStepAfter($currentStep) ?? $initialStep;
        $this->stepSessionManager->setStepData($nextStep);

        return $this;
    }

    protected function withNoReswapHeader(ResultInterface $response): ResultInterface
    {
        return $this->htmxHeaderManager->setResponseWithNoReSwap($response);
    }

    protected function withCurrentStepPushUrlHeader(ResultInterface $response): ResultInterface
    {
        return $this->htmxHeaderManager->setResponseUrlWithCurrentStep($response);
    }

    protected function getCurrentStepLayoutHandles(): array
    {
        $step = $this->stepSessionManager->getStepData() ?? $this->activeTheme->getInitialStep();
        $layoutHandle = $step?->layoutHandle;

        if (!$layoutHandle) {
            throw new LocalizedException(__('No layout handles available to process the step'));
        }

        return [$layoutHandle];
    }

    private function getNotificationBlock(): ?NotificationsBlock
    {
        return $this->getBlock('checkout.notifications', $this->getCurrentStepLayoutHandles());
    }

    private function updateStepByRequest(): void
    {
        $stepName = $this->getRequest()->getParam('step');
        $stepForRequest = $this->stepValidationService->getValidStepFor($stepName);
        $this->stepSessionManager->setStepData($stepForRequest);
    }
}
