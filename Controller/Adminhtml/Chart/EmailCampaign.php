<?php
/**
 * Author: Eric Quach
 * Date: 4/17/18
 */
namespace Magenest\UltimateFollowupEmail\Controller\Adminhtml\Chart;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;

class EmailCampaign extends Action
{
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Magenest_UltimateFollowupEmail::ultimatefollowupemail_campaign_charts');
        $resultPage->getConfig()->getTitle()->prepend(__('Email Campaign Reports'));
        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_UltimateFollowupEmail::ultimatefollowupemail_campaign_charts');
    }
}