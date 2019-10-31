<?php
namespace Magenest\UltimateFollowupEmail\Controller\Adminhtml\Mail;

/**
 * Class MassDelete
 */
class MassDelete extends \Magento\Backend\App\Action
{

    public function execute()
    {
        $mailIds = $this->getRequest()->getParam('mail');
        if (!is_array($mailIds) || empty($mailIds)) {
            $this->messageManager->addError(__('Please select mail(s).'));
        } else {
            /** @var \Magenest\UltimateFollowupEmail\Model\Mail $mailModel */
            $mailModel = $this->_objectManager->create('Magenest\UltimateFollowupEmail\Model\Mail');
            $resource = $mailModel->getResource();
            try {
                $ids = [];
                $count = 0;
                $affectedRows = 0;
                foreach ($mailIds as $mailId) {
                    $ids[] = $mailId;
                    $count++;
                    if ($count >= 5000) {
                        $affectedRows += $resource->getConnection()
                            ->delete($resource->getMainTable(), 'id IN ('.implode(',', $ids).')');
                        $count = 0;
                        $ids = [];
                    }
                }
                if (count($ids)) {
                    $affectedRows += $resource->getConnection()
                        ->delete($resource->getMainTable(), 'id IN ('.implode(',', $ids).')');
                }

                $this->messageManager->addSuccess(
                    __('A total of %1 record(s) have been deleted.', $affectedRows)
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/index');
    }

    /**
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_UltimateFollowupEmail::ultimatefollowupemail_mail');
    }
}
