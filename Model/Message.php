<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 14/06/2016
 * Time: 13:23
 */

namespace Magenest\UltimateFollowupEmail\Model;

class Message extends \Magento\Framework\Model\AbstractModel
{

    protected $scopeConfig;

    protected $storeManager;


    /**
     * @param \Magento\Framework\Model\Context                   $context
     * @param \Magento\Framework\Registry                        $registry
     * @param ResourceModel\Message                                   $resource
     * @param ResourceModel\Message\Collection                        $resourceCollection
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface         $storeManager
     * @param array                                              $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magenest\UltimateFollowupEmail\Model\ResourceModel\Message $resource,
        \Magenest\UltimateFollowupEmail\Model\ResourceModel\Message\Collection $resourceCollection,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;

        $this->storeManager = $storeManager;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }
}
