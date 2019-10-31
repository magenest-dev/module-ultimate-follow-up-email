<?php
namespace Magenest\UltimateFollowupEmail\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;

abstract class Mail extends Action
{

    protected $mailFactory;

    protected $_resultLayoutFactory;

    /**
     * Mail constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
    ) {
        $this->_resultLayoutFactory = $resultLayoutFactory;
        parent::__construct($context);
    }

    public function execute()
    {
    }
    /**
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_UltimateFollowupEmail::ultimatefollowupemail_mail');
    }
}
