<?php
namespace Magenest\UltimateFollowupEmail\Observer\Config;

use Magento\Customer\Model\Logger;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magenest\UltimateFollowupEmail\Helper\Data as Helper;

class Save implements ObserverInterface
{
    protected $logger;
    protected $eavConfig;

    /**
     * Save constructor.
     * @param Logger $logger
     * @param \Magento\Eav\Model\Config $eavConfig
     */
    public function __construct(
        Logger $logger,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->logger               = $logger;
        $this->eavConfig = $eavConfig;
    }


    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var  $configData  \Magento\Framework\App\Config\Value */
        $configData = $observer->getEvent()->getConfigData();
        $path       = $configData->getPath();
        if ($path == Helper::XML_PATH_CONFIG_MOBILE_REQUIRED) {
            $value = $configData->getFieldsetDataValue('is_required');
            $mobileAttribute  = $this->eavConfig->getAttribute(\Magento\Customer\Model\Customer::ENTITY, 'mobile_number');
            if ($value == '1' && !$mobileAttribute->getIsRequired()) {
                $mobileAttribute->addData(['is_required' => 1])->save();
                return;
            }
            if ($value == '0' && $mobileAttribute->getIsRequired()) {
                $mobileAttribute->addData(['is_required' => 0])->save();
                return;
            }
        }
        if ($path == Helper::XML_PATH_CONFIG_MOBILE_ENABLE) {
            $value = $configData->getFieldsetDataValue('enable');
            $mobileAttribute  = $this->eavConfig->getAttribute(\Magento\Customer\Model\Customer::ENTITY, 'mobile_number');
            if ($value == '1' && !$mobileAttribute->getIsUserDefined()) {
                $mobileAttribute->addData(['is_user_defined' => 1])->save();
                return;
            }
            if ($value == '0' && $mobileAttribute->getIsUserDefined()) {
                $mobileAttribute->addData(['is_user_defined' => 0])->save();
                return;
            }
        }
    }
}
