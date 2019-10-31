<?php
namespace Magenest\UltimateFollowupEmail\Controller\Track;

use Magenest\UltimateFollowupEmail\Controller\Track as TrackController;

use Magenest\UltimateFollowupEmail\Model\Processor\UltimateFollowupEmail;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Encryption\Encryptor;
use Magento\Quote\Api\CartRepositoryInterface;

class Restore extends TrackController
{
    protected $_encryptor;

    protected $customerFactory;

    protected $cartRepository;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        CartRepositoryInterface $cartRepository,
        CustomerFactory $customerFactory,
        Encryptor $encryptor
    ) {
    
        parent::__construct($context, $checkoutSession, $customerSession);
        $this->_encryptor = $encryptor;
        $this->customerFactory = $customerFactory;
        $this->cartRepository = $cartRepository;
    }


    public function execute()
    {
        $resumeRequest = $this->getRequest()->getParam('utc');
        $userAutoLoginKey = UltimateFollowupEmail::base64UrlDecode($this->getRequest()->getParam('u'));

        $cartId = $resumeRequest;

        $quote = $this->_objectManager->create('Magento\Quote\Model\Quote')->load($cartId);

        if (!$this->checkoutSession) {
            $this->checkoutSession = $this->_objectManager->create('\Magento\Checkout\Model\Session');
        }

        if ($quote->getReservedOrderId()) {
            $quote = $this->_objectManager->create('Magento\Quote\Model\Quote')->merge($quote);
            if ($this->checkoutSession->getQuote()) {
                $this->checkoutSession->getQuote()->merge($quote);
                $this->cartRepository->save($this->checkoutSession->getQuote());
                $quote = $this->checkoutSession->getQuote();
            } else {
                $this->cartRepository->save($quote);
            }
        }

        if ($userAutoLoginKey) {
            if (!$this->customerSession->isLoggedIn()) {
                $customerKey = $this->_encryptor->decrypt($userAutoLoginKey);
                $customerId = substr($customerKey, 0, 1);
                $customerEmail = substr($customerKey, 1);
                $customer = $this->customerFactory->create()->load($customerId);
                if ($customer->getId() && $customer->getEmail() === $customerEmail) {
                    $this->customerSession->setCustomerAsLoggedIn($customer);
                }
            }
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $this->checkoutSession->replaceQuote($quote);
        $resultRedirect->setPath('checkout/cart/index');
        return $resultRedirect;
    }
}
