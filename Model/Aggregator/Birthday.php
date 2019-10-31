<?php

namespace Magenest\UltimateFollowupEmail\Model\Aggregator;

use Magento\Customer\Model\CustomerFactory;

class Birthday
{
    protected $_eavConfig;

    protected $_customerResource;

    protected $customerFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Eav\Model\Config $eavConfig,
        CustomerFactory $customerFactory
    ) {
        $this->_eavConfig        = $eavConfig;
        $this->customerFactory = $customerFactory;
    }


    public function collectCustomersHaveBirthdayToday()
    {
        $customerCollection = $this->customerFactory->create()->getCollection();

        $today = new \DateTime();
        $birthDay = $today->format('m-d');

        $customerCollection->getSelect()
            ->where('DATE_FORMAT(dob, "%m-%d")=?', $birthDay);

        return $customerCollection;
    }
}
