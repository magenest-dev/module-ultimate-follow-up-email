<?php
namespace Magenest\UltimateFollowupEmail\Model;

use Magenest\UltimateFollowupEmail\Model\System\Config\Source\Sms\Status as SmsStatus;

/**
 * Class Sms
 *
 * @package Magenest\UltimateFollowupEmail\Model
 */
class Sms extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magenest\UltimateFollowupEmail\Helper\Nexmo
     */
    protected $nexmoHelper;


    /**
     * @param \Magento\Framework\Model\Context                   $context
     * @param \Magento\Framework\Registry                        $registry
     * @param ResourceModel\Sms                                       $resource
     * @param ResourceModel\Sms\Collection                            $resourceCollection
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface         $storeManager
     * @param \Magenest\UltimateFollowupEmail\Helper\Nexmo       $nexmoHelper
     * @param array                                              $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magenest\UltimateFollowupEmail\Model\ResourceModel\Sms $resource,
        \Magenest\UltimateFollowupEmail\Model\ResourceModel\Sms\Collection $resourceCollection,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magenest\UltimateFollowupEmail\Helper\Nexmo $nexmoHelper,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;

        $this->storeManager = $storeManager;

        $this->nexmoHelper = $nexmoHelper;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }


    public function send()
    {
        try {
            $this->nexmoHelper->send($this);
            $this->setStatus(SmsStatus::STATUS_SENT)->save();
        } catch (\Exception $e) {
            $this->setStatus(SmsStatus::STATUS_FAILED)->save();
            \Magento\Framework\App\ObjectManager::getInstance()->create('Psr\Log\LoggerInterface')->debug('Error sending SMS: '.$e->getMessage());
        }
    }


    public function getMobileNumber()
    {
    }


    public function getContent()
    {
    }
}
