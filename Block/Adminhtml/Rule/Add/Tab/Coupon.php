<?php
namespace Magenest\UltimateFollowupEmail\Block\Adminhtml\Rule\Add\Tab;

use Magento\Framework\App\ObjectManager;
use Magento\SalesRule\Model\Rule;

class Coupon extends \Magento\Backend\Block\Widget\Form\Generic implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{

    protected $_template = 'rule/add/tab/coupon.phtml';

    protected $_saleRuleFactory;

    protected $serializer;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry             $registry
     * @param \Magento\Framework\Data\FormFactory     $formFactory
     * @param array                                   $data
     */
    public function __construct(
        \Magenest\UltimateFollowupEmail\Model\Serializer $serializer,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\SalesRule\Model\RuleFactory $ruleFactory,
        array $data = []
    ) {
        $this->_coreRegistry    = $registry;
        $this->_formFactory     = $formFactory;
        $this->_saleRuleFactory = $ruleFactory;
        parent::__construct($context, $registry, $formFactory);
        $this->serializer = $serializer;
    }


    /**
     * Return Tab label
     *
     * @return string
     * @api
     */
    public function getTabLabel()
    {
        return __('Coupon');
    }


    /**
     * Return Tab title
     *
     * @return string
     * @api
     */
    public function getTabTitle()
    {
        return __('Coupon');
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     * @api
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     * @api
     */
    public function isHidden()
    {
        return false;
    }

    public function getEnableCoupon()
    {
        $data = $this->_coreRegistry->registry('rule_data');
        if (isset($data['enable_coupon'])) {
            return $data['enable_coupon'];
        }

        return 0;
    }


    public function getIsSelect($value)
    {
        $selected = '';
        $data     = $this->_coreRegistry->registry('rule_data');
        if (isset($data['enable_coupon'])) {
            if ($value == $data['enable_coupon']) {
                return $selected = 'selected="selected"';
            }
        }

        return $selected;
    }


    public function getPromotionId()
    {
        $data = $this->_coreRegistry->registry('rule_data');
        if (isset($data['promotion_rule_id'])) {
            return $data['promotion_rule_id'];
        }

        return '';
    }


    public function getPromotionName()
    {
        $data = $this->_coreRegistry->registry('rule_data');
        if (isset($data['promotion_rule_id'])) {
            $rule = $this->_saleRuleFactory->create()->load($data['promotion_rule_id']);
            return $rule->getName();
        }

        return '';
    }

    public function getAvailableCouponRules()
    {
        $saleRuleCollection = ObjectManager::getInstance()->create('Magento\SalesRule\Model\ResourceModel\Rule\Collection');
        $saleRuleCollection
            ->addFieldToFilter('coupon_type', Rule::COUPON_TYPE_SPECIFIC)
            ->addFieldToFilter('use_auto_generation', 1);

        return $saleRuleCollection;
    }

    public function getCouponTime($unit)
    {
        $data = $this->_coreRegistry->registry('rule_data');
        if (isset($data['coupon_time']) && $data['coupon_time']) {
            $couponTime = $this->serializer->unserialize($data['coupon_time']);

            return $couponTime[$unit] ? $couponTime[$unit] : null;
        }
        return null;
    }
}
