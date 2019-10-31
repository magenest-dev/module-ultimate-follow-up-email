<?php
namespace Magenest\UltimateFollowupEmail\Controller\Adminhtml\Mail;

use Magenest\UltimateFollowupEmail\Model\System\Config\Source\Mail\Status;

class MassStatus extends \Magento\Backend\App\Action
{
    public function execute()
    {
        $mailIds = $this->getRequest()->getParam('mail');
        if (!is_array($mailIds) || empty($mailIds)) {
            $this->messageManager->addError(__('Please select mail(s).'));
        } else {
            try {
                $status = (int)$this->getRequest()->getParam('status');
                /** @var \Magenest\UltimateFollowupEmail\Model\Mail $mailModel */
                $mailModel = $this->_objectManager->get('Magenest\UltimateFollowupEmail\Model\Mail');
                $resource = $mailModel->getResource();
                $ids = [];
                $count = 0;
                $affectedRows = 0;
                $updateData = ['status' => $status];
                if ($status == Status::STATUS_QUEUED) {
                    $updateData['log'] = null;
                }
                foreach ($mailIds as $mailId) {
                    $ids[] = $mailId;
                    $count++;
                    if ($count >= 5000) {
                        $affectedRows += $resource
                            ->getConnection()
                            ->update(
                                $resource->getMainTable(),
                                $updateData,
                                'id IN ('.implode(',', $ids).')'
                            );
                        $count = 0;
                        $ids = [];
                    }
                }
                if (count($ids)) {
                    $affectedRows += $resource
                        ->getConnection()
                        ->update(
                            $resource->getMainTable(),
                            $updateData,
                            'id IN ('.implode(',', $ids).')'
                        );
                }

                $this->messageManager->addSuccess(
                    __('A total of %1 record(s) have been updated.', $affectedRows)
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
