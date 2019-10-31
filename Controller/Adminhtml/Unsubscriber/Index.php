<?php
namespace Magenest\UltimateFollowupEmail\Controller\Adminhtml\Unsubscriber;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Index
 * @package Magenest\Xero\Controller\Adminhtml\Log
 */
class Index extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Index constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magenest_UltimateFollowupEmail::unsubscriber');
        $resultPage->addBreadcrumb(__('Log'), __('Log'));
        $resultPage->addBreadcrumb(__('Manage Log'), __('Manage Log'));
        $resultPage->getConfig()->getTitle()->prepend(__('Customers who unsubscribed followup email'));

        return $resultPage;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_UltimateFollowupEmail::ultimatefollowupemail_unsubscriber');
    }
}
