<?php
namespace Magenest\UltimateFollowupEmail\Controller\Adminhtml\Rule;

class MassStatus extends \Magento\Backend\App\Action
{
    public function execute()
    {
        $ruleIds = $this->getRequest()->getParam('rule');
        if (!is_array($ruleIds) || empty($ruleIds)) {
            $this->messageManager->addError(__('Please select rule(s).'));
        } else {
            try {
                $status = (int)$this->getRequest()->getParam('status');
                /** @var \Magenest\UltimateFollowupEmail\Model\Rule $ruleModel */
                $ruleModel = $this->_objectManager->get('Magenest\UltimateFollowupEmail\Model\Rule');
                $resource = $ruleModel->getResource();
                $ids = [];
                $count = 0;
                $affectedRows = 0;
                $updateData = ['status' => $status];
                foreach ($ruleIds as $ruleId) {
                    $ids[] = $ruleId;
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
        return $this->_authorization->isAllowed('Magenest_UltimateFollowupEmail::ultimatefollowupemail_rule');
    }
}
