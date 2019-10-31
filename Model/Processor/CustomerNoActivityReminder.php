<?php
/**
 * Author: Eric Quach
 * Date: 5/7/18
 */
namespace Magenest\UltimateFollowupEmail\Model\Processor;

use Magenest\UltimateFollowupEmail\Model\Aggregator\CustomerNoActivity;
use Magento\Customer\Model\Customer;
use Magento\Email\Model\TemplateFactory as EmailTemplateModel;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Url;

class CustomerNoActivityReminder extends UltimateFollowupEmail
{
    const TYPE = 'customer_no_activity';
    protected $collector;
    protected $type = self::TYPE;

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
        Url $frontendUrl,
        \Magento\SalesRule\Model\Coupon\Massgenerator $massGenerator,
        \Magento\Store\Model\App\Emulation $appEmulation,
        CustomerNoActivity $customerNoActivity
    )
    {
        parent::__construct($context, $rulesFactory, $mailFactory, $messageFactory, $smsFactory, $quoteFactory, $cartRepositoryInterface, $emailTemplateModel, $scopeConfig, $frontendUrl, $massGenerator, $appEmulation);
        $this->collector = $customerNoActivity;
    }

    public function run()
    {
        $customerCollection = $this->collector->collect();
        $customerCollection->setPageSize(500);
        $pageLimit = $customerCollection->getLastPageNumber();

        for ($i = 1; $i <= $pageLimit; $i++) {
            $customerCollection->clear();
            $customerCollection->setCurPage(1);
            /** @var Customer $customer */
            foreach ($customerCollection as $customer) {
                $this->_emailTarget = $customer;
                $this->createFollowUpEmail();
            }
        }
    }

    public function isValidate($rule)
    {
        $objectManager = ObjectManager::getInstance();
        /** @var \Magenest\UltimateFollowupEmail\Model\Serializer $serializer */
        $serializer = $objectManager->get('Magenest\UltimateFollowupEmail\Model\Serializer');
        $isValidate = false;

        $customer = $this->_emailTarget;
        if ($customer instanceof \Magento\Customer\Model\Customer) {
            $isValidate = $this->validateCustomerGroupAndWebsite($customer->getGroupId(), $customer->getWebsiteId());
        }
        if ($isValidate) {
            $gender = $this->_emailTarget->getGender();
            $condition = $serializer->unserialize($this->_activeRule->getConditionsSerialized());

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
            'customerFistName' => $this->_emailTarget->getFirstname(),
            'customerLastName' => $this->_emailTarget->getLastname(),
            'customerName' => $this->_emailTarget->getFirstname() . ' ' . $this->_emailTarget->getLastname(),
        ];
    }

    public function getDuplicatedKey()
    {
        return $this->_emailTarget->getId().'_'.$this->_emailTarget->getLastLoginAt();
    }

    public function postCreateMail()
    {
    }
}