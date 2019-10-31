<?php
namespace Magenest\UltimateFollowupEmail\Controller\Adminhtml\Mail;

use Magento\Framework\Controller\ResultFactory;

use Magento\Backend\App\Action;

class Send extends Action
{
    public function execute()
    {
        $ids = $this->getRequest()->getParam('id');

        if (is_numeric($ids)) {
            $tmp = $ids;
            $ids = [];
            $ids[] = $tmp;
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (!is_array($ids)) {
            $this->messageManager->addWarning('Please select item(s)');
        } else {
            try {
                foreach ($ids as $id) {
                    /** @var  $mailModel \Magenest\UltimateFollowupEmail\Model\Mail */
                    $mailModel = $this->_objectManager->create('Magenest\UltimateFollowupEmail\Model\Mail')->load($id);
                    $mailModel->send();
                }

                $this->messageManager->addSuccess(__('Total of %1 record(s) were processed', count($ids)));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }

        $resultRedirect->setPath('*/*/index');
        return $resultRedirect;
    }

    /**
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_UltimateFollowupEmail::ultimatefollowupemail_mail');
    }
}
