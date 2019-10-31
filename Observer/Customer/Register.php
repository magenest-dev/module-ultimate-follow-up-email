<?php
namespace Magenest\UltimateFollowupEmail\Observer\Customer;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Logger;
use Magento\Email\Model\TemplateFactory as EmailTemplateModelFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class Register extends \Magenest\UltimateFollowupEmail\Model\Processor\UltimateFollowupEmail implements ObserverInterface
{

    protected $type = 'customer_registration';


    /**
     * Handler for 'customer_logout' event.
     *
     * @param  Observer $observer
     * @return void
     */

    protected $serialize;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magenest\UltimateFollowupEmail\Model\ResourceModel\Rule\CollectionFactory $rulesFactory,
        \Magenest\UltimateFollowupEmail\Model\MailFactory $mailFactory,
        \Magenest\UltimateFollowupEmail\Model\MessageFactory $messageFactory,
        \Magenest\UltimateFollowupEmail\Model\SmsFactory $smsFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
        EmailTemplateModelFactory $templateFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Url $frontendUrl,
        \Magento\SalesRule\Model\Coupon\Massgenerator $massGenerator,
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Magenest\UltimateFollowupEmail\Model\Serializer $serializer)
    {
        parent::__construct($context, $rulesFactory, $mailFactory, $messageFactory, $smsFactory, $quoteFactory, $cartRepositoryInterface, $templateFactory, $scopeConfig, $frontendUrl, $massGenerator, $appEmulation);
        $this->serialize = $serializer;
    }

    public function execute(Observer $observer)
    {
        try {
            /** @var  $customer \Magento\Customer\Model\Data\Customer */
            $customer = $observer->getEvent()->getCustomer();
//            $customer = ObjectManager::getInstance()->create(Customer::class)->setData($customer->__toArray());
            $this->_emailTarget = $customer;
            $this->run();
        } catch (\Exception $e) {
            ObjectManager::getInstance()->get(LoggerInterface::class)->critical($e);
        }
    }

    public function run()
    {
        $this->createFollowUpEmail();
    }

    public function isValidate($rule)
    {
        $isValidate = false;

        $customer = $this->_emailTarget;
        if ($customer instanceof \Magento\Customer\Model\Customer) {
            $isValidate = $this->validateCustomerGroupAndWebsite($customer->getGroupId(), $customer->getWebsiteId());
        }
        if ($isValidate) {
            $gender = $this->_emailTarget->getGender();
            $condition =  $this->serialize->unserialize($this->_activeRule->getConditionsSerialized());

            if ($condition['gender']) {
                if ($gender == $condition['gender']) {
                    $isValidate = true;
                } else {
                    $isValidate = false;
                }
            }
        }

        return $isValidate;
    }


    public function prepareMail()
    {
        if (!$this->_activeMail) {
            return;
        }

        $this->_vars = [
            'customer_id'=>(int)$this->_emailTarget->getData('entity_id'),
            'customerFistName' => $this->_emailTarget->getFirstname(),
            'customerLastName' => $this->_emailTarget->getLastname(),
            'customerName' => $this->_emailTarget->getFirstname() . ' ' . $this->_emailTarget->getLastname(),
        ];
    }


    public function postCreateMail()
    {
    }
}
