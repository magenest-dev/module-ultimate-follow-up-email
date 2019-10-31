<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 29/09/2015
 * Time: 20:06
 */
namespace Magenest\UltimateFollowupEmail\Model;

use Magenest\UltimateFollowupEmail\Model\ResourceModel\Rule as ResourceModel;
use Magenest\UltimateFollowupEmail\Model\ResourceModel\Rule\Collection;

class Rule extends \Magento\Framework\Model\AbstractModel
{
    protected $serializer;

    protected $_eventPrefix = 'ultimatefollowupemail_rule';


    public function __construct(
        \Magenest\UltimateFollowupEmail\Model\Serializer $serializer,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ResourceModel $resource,
        Collection $resourceCollection,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->serializer = $serializer;
    }


    /**
     * @return array
     */
    public function getMailChain()
    {
        $chain_text = $this->getData('email_chain');

        $emailChains = $this->serializer->unserialize($chain_text);

        return $emailChains;
    }


    /**
     * get the id array of sms
     *
     * @return mixed
     */
    public function getMessageChain()
    {
        $ids = $this->serializer->unserialize($this->getData('sms_chain'));
        return $ids;
    }


    public function isValidateTarget($customerGroupId, $websiteId)
    {
        $isValidate       = false;
        $websiteIds       = $this->serializer->unserialize($this->getWebsiteId());
        $customerGroupIds = $this->serializer->unserialize($this->getCustomerGroupId());

        if (in_array($websiteId, $websiteIds) && in_array($customerGroupId, $customerGroupIds)) {
            $isValidate = true;
        }

        return $isValidate;
    }


    /**
     * get sms bind to the rule
     */
    public function getSMSData()
    {
        $smsData = $this->serializer->unserialize($this->getData('sms_chain'));
        return $smsData;
    }
}
