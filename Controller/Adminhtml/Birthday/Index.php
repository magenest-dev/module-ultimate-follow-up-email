<?php
namespace Magenest\UltimateFollowupEmail\Controller\Adminhtml\Birthday;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $resultPage =   $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magenest_UltimateFollowupEmail::ultimatefollowupemail');
        $resultPage->getConfig()->getTitle()->prepend(__('Customer Birthdays'));
        $resultPage->getConfig()->getTitle()->prepend(__('Customer Birthdays'));
//        $resultPage->addContent($resultPage->getLayout()->createBlock('Magenest\UltimateFollowupEmail\Block\Adminhtml\Cart\Grid'));
        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_UltimateFollowupEmail::ultimatefollowupemail_birthday');
    }
}
