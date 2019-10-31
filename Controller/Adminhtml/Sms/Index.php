<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 27/06/2016
 * Time: 13:31
 */
namespace Magenest\UltimateFollowupEmail\Controller\Adminhtml\Sms;

use Magento\Framework\Controller\ResultFactory;

class Index extends \Magento\Backend\App\Action
{

    /**
     * Dispatch request ultimatefollowupemail_sms_index
     *
     * @return \Magento\Framework\Controller\ResultInterface|\Magento\Framework\App\ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Magenest_UltimateFollowupEmail::ultimatefollowupemail');
        $resultPage->getConfig()->getTitle()->prepend(__('Follow up Sms'));
        $resultPage->getConfig()->getTitle()->prepend(__('SMS log'));
        $resultPage->addContent($resultPage->getLayout()->createBlock('Magenest\UltimateFollowupEmail\Block\Adminhtml\Sms\Grid'));
        return $resultPage;
    }
}
