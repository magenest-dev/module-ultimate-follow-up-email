<?php
namespace Magenest\UltimateFollowupEmail\Controller\Adminhtml\Rule;

/**
 * Class MassDelete
 */
class MassDelete extends \Magento\Backend\App\Action
{
    public function execute()
    {
        $ruleIds = $this->getRequest()->getParam('rule');
        if (!is_array($ruleIds) || empty($ruleIds)) {
            $this->messageManager->addError(__('Please select rule(s).'));
        } else {
            /** @var \Magenest\UltimateFollowupEmail\Model\Rule $ruleModel */
            $ruleModel = $this->_objectManager->create('Magenest\UltimateFollowupEmail\Model\Rule');
            $resource = $ruleModel->getResource();
            try {
                $ids = [];
                $count = 0;
                $affectedRows = 0;
                foreach ($ruleIds as $ruleId) {
                    $ids[] = $ruleId;
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
        return $this->_authorization->isAllowed('Magenest_UltimateFollowupEmail::ultimatefollowupemail_rule');
    }
}
