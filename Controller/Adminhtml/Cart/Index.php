<?php
namespace Magenest\UltimateFollowupEmail\Controller\Adminhtml\Cart;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magenest\UltimateFollowupEmail\Model\Aggregator\AbandonedCart as AbandonedCartAggregator;

class Index extends Action
{
    protected $resultPageFactory;
    protected $abandonedCartAggregator;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        AbandonedCartAggregator $abandonedCartAggregator
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->abandonedCartAggregator = $abandonedCartAggregator;
    }

    public function execute()
    {
        $this->abandonedCartAggregator->collect();
        $resultPage =   $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magenest_UltimateFollowupEmail::ultimatefollowupemail');
        $resultPage->getConfig()->getTitle()->prepend(__('Follow up Emails'));
        $resultPage->getConfig()->getTitle()->prepend(__('Abandoned Cart List'));
        return $resultPage;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_UltimateFollowupEmail::ultimatefollowupemail_cart');
    }
}
