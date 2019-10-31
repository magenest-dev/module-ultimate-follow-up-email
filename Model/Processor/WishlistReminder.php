<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 30/09/2015
 * Time: 15:36
 */

namespace Magenest\UltimateFollowupEmail\Model\Processor;

use Magento\Catalog\Model\Product;
use Magento\Email\Model\TemplateFactory as EmailTemplateModel;
use Magento\Wishlist\Model\Wishlist;
use Symfony\Component\Config\Definition\Exception\Exception;

class WishlistReminder extends UltimateFollowupEmail
{
    protected $_rulesFactory;

    protected $_activeMailChain;

    /**
     * @var   \Magento\SalesRule\Model\RuleFactory
     */
    protected $_saleRuleFactory;

    /**
     * @var  \Magento\Quote\Model\Quote
     */
    protected $_abandonedCart;

    /**
     * @var \Magento\Wishlist\Model\Wishlist
     */
    protected $wishlist;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Magenest\UltimateFollowupEmail\Helper\WishlistHelper
     */
    protected $_wishlistHelper;

    /**
     * @var \Magento\Wishlist\Model\WishlistFactory
     */
    protected $_wishlistFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \Magento\CatalogRule\Model\RuleFactory
     */
    protected $_catalogRuleFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var Product
     */
    protected $wishlistProduct;

    protected $type = 'wishlist_reminder';

    protected $serialize;

    protected $currentCustomer;



    protected $currentWishlistItem = [];

    public function __construct(
        \Magenest\UltimateFollowupEmail\Model\ResourceModel\Rule\CollectionFactory $rulesFactory,
        \Magenest\UltimateFollowupEmail\Model\MessageFactory $messageFactory,
        \Magenest\UltimateFollowupEmail\Model\SmsFactory $smsFactory,
        EmailTemplateModel $emailTemplateModel,
        \Magenest\UltimateFollowupEmail\Model\Aggregator\AbandonedCart $abandonedCart,
        \Magenest\UltimateFollowupEmail\Model\MailFactory $mailFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
        \Magento\SalesRule\Model\RuleFactory $ruleFactory,
        \Magento\Framework\Url $urlInterface,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\SalesRule\Model\Coupon\Massgenerator $massGenerator,
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Magenest\UltimateFollowupEmail\Helper\WishlistHelper $wishlistHelper,
        \Magento\Wishlist\Model\WishlistFactory $wishlistFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\CatalogRule\Model\RuleFactory $catalogRuleFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magenest\UltimateFollowupEmail\Model\Serializer $serializer
    ) {
        $this->_saleRuleFactory = $ruleFactory;
        $this->_wishlistHelper = $wishlistHelper;
        $this->_wishlistFactory = $wishlistFactory;
        $this->_customerFactory = $customerFactory;
        $this->_catalogRuleFactory = $catalogRuleFactory;
        $this->_productFactory = $productFactory;
        $this->serialize = $serializer;
        $this->_logger = $context->getLogger();
        parent::__construct($context, $rulesFactory, $mailFactory, $messageFactory, $smsFactory, $quoteFactory, $cartRepositoryInterface, $emailTemplateModel, $scopeConfig, $urlInterface, $massGenerator, $appEmulation);
    }


    public function run()
    {
        $wishlistProductReminders = $this->_wishlistHelper->getCustomersProductWishlist();

        foreach ($wishlistProductReminders as $wishlistProductReminder) {
            if (isset($wishlistProductReminder['wishlist_id']) && $wishlistProductReminder['wishlist_id']) {
                /** @var Wishlist $wishlist */
                $this->wishlist = $this->_wishlistFactory->create()->load($wishlistProductReminder['wishlist_id']);
                $this->_emailTarget = $this->wishlist;
                $this->currentWishlistItem = $wishlistProductReminder;
                $customer = $this->_customerFactory->create()->load($this->wishlist->getCustomerId());
                $this->wishlist->setData('customer_firstname', $customer->getFirstname());
                $this->wishlist->setData('customer_lastname', $customer->getLastname());
                $this->wishlist->setData('customer_email', $customer->getEmail());
                $this->currentCustomer = $customer;
                $this->wishlistProduct = $this->_productFactory->create()->load($wishlistProductReminder['product_id']);
                $this->createFollowUpEmail();
            }
        }
    }

    public function getDuplicatedKey()
    {
        return implode('_', [
            $this->wishlist->getId(),
            $this->wishlistProduct->getId(),
            isset($this->currentWishlistItem['added_at'])?$this->currentWishlistItem['added_at']:""
        ]);
    }

    /**
     * @param $rule
     * @return boolean
     */
    public function isValidate($rule)
    {
        $customer = $this->currentCustomer;
        if ($customer instanceof \Magento\Customer\Model\Customer) {
            $isValidate = $this->validateCustomerGroupAndWebsite($customer->getGroupId(), $customer->getWebsiteId());
            if (!$isValidate) {
                return $isValidate;
            }
        }
        $catalogRule = $this->_catalogRuleFactory->create();
        $catalogRule->setData('conditions_serialized', $rule->getData('conditions_serialized'));
        try {
            $catalogRule->getConditions();
        } catch (\InvalidArgumentException $e) {
            $catalogRule->setData('conditions_serialized', json_encode($this->serialize->unserialize($rule->getData('conditions_serialized'))));
        }
        $isValidate = $catalogRule->validate($this->wishlistProduct);

        if ($rule->getData('additional_settings')) {
            try {
                $additionalSettings = $this->serialize->unserialize($rule->getData('additional_settings'));
                if (isset($this->currentWishlistItem['updated_at']) &&
                    isset($additionalSettings['wishlist_reminder_time'])) {
                    $isTime = $this->checkReminderTime($additionalSettings['wishlist_reminder_time'], $this->currentWishlistItem['updated_at']);
                    $isValidate = $isTime && $isValidate;
                }
            } catch (\Exception $e) {
            }
        }
        return $isValidate;
    }

    private function checkReminderTime($wishlistReminderTime, $wishlistItemTime)
    {
        $upperLimit = new \DateTime();
        $modify = '-' . $wishlistReminderTime . ' minutes';
        $upperLimit->modify($modify);

        $downLimit = new \DateTime();
        $wishlistReminderTime = $wishlistReminderTime < 10 ? $wishlistReminderTime * 10 : $wishlistReminderTime * 2;
        $modify = '-' . $wishlistReminderTime . ' minutes';
        $downLimit->modify($modify);

        $wishlistItemTime = new \DateTime($wishlistItemTime);

        return $wishlistItemTime > $downLimit && $wishlistItemTime < $upperLimit;
    }

    public function prepareMail()
    {
        if (!$this->_activeMail) {
            return;
        }

        // get the customer of abandoned cart
        $customer = $this->_customerFactory->create()->load($this->wishlist->getCustomerId());
        $customerFirstName = $customer->getFirstname();
        $customerLastName  = $customer->getLastname();
        $customerName      = $customer->getName();

        $this->_vars = [
            'product' => $this->wishlistProduct,
            'customerFistName' => $customerFirstName,
            'customerLastName' => $customerLastName,
            'customerName'     => $customerName,
            'wishlistProduct' => htmlspecialchars_decode($this->getProductHtml($this->wishlistProduct->getId())),
            'relatedProductsGrid' => htmlspecialchars_decode($this->getRelatedProductsGridHtml($this->wishlistProduct))
        ];
    }

    public function postCreateMail()
    {
    }
}
