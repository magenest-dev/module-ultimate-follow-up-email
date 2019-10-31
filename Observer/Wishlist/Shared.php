<?php
namespace Magenest\UltimateFollowupEmail\Observer\Wishlist;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Email\Model\TemplateFactory as EmailTemplateModel;

class Shared extends \Magenest\UltimateFollowupEmail\Model\Processor\UltimateFollowupEmail implements ObserverInterface
{

    protected $type = 'wishlist_shared';

    protected $_customerFactory;

    protected $_customer;


    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magenest\UltimateFollowupEmail\Model\ResourceModel\Rule\CollectionFactory $rulesFactory,
        \Magenest\UltimateFollowupEmail\Model\MailFactory $mailFactory,
        \Magenest\UltimateFollowupEmail\Model\MessageFactory $messageFactory,
        \Magenest\UltimateFollowupEmail\Model\SmsFactory $smsFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
        EmailTemplateModel $emailTemplateModel,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Url $urlInterface,
        \Magento\SalesRule\Model\Coupon\Massgenerator $massGenerator,
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
    
        $this->_customerFactory = $customerFactory;
        parent::__construct($context, $rulesFactory, $mailFactory, $messageFactory, $smsFactory, $quoteFactory, $cartRepositoryInterface, $emailTemplateModel, $scopeConfig, $urlInterface, $massGenerator, $appEmulation);
    }


    public function run()
    {
        $this->createFollowUpEmail();
    }


    public function isValidate($rule)
    {
        $isValidate = false;
        $customer = $this->_customer;
        if ($customer instanceof \Magento\Customer\Model\Customer) {
            $isValidate = $this->validateCustomerGroupAndWebsite($customer->getGroupId(), $customer->getWebsiteId());
        }
        return $isValidate;
    }

    public function prepareMail()
    {
        $this->_vars = [
            'customer' => $this->_customer,
            'customerFistName' => $this->_customer->getFirstname(),
            'customerLastName' => $this->_customer->getLastname(),
            'customerName' => $this->_customer->getName(),
        ];
    }


    public function postCreateMail()
    {
        // TODO: Implement postCreateMail() method.
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var  $wishlist \Magento\Wishlist\Model\Wishlist */
        $wishlist = $observer->getEvent()->getWishlist();
        $customerId = $wishlist->getCustomerId();

        /** @var $customer \Magento\Customer\Model\Customer */
        $customer = $this->_customerFactory->create()->load($customerId);
        $firstName = $customer->getFirstname();
        $lastName = $customer->getLastname();
        $customerEmail = $customer->getEmail();

        $mobileNumber = $customer->getData('mobile_number');
        $this->_emailTarget = $wishlist;

        $this->_customer = $customer;

        // Set data used to send email
        $this->_emailTarget->setData('customer_email', $customerEmail);
        $this->_emailTarget->setData('mobile_number', $mobileNumber);

        $this->_emailTarget->setData('customer_firstname', $firstName);
        $this->_emailTarget->setData('customer_lastname', $lastName);
        $this->run();
    }
}
