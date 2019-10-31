<?php
namespace Magenest\UltimateFollowupEmail\Controller\Adminhtml\Rule;

class Delete extends \Magenest\UltimateFollowupEmail\Controller\Adminhtml\Rule
{


    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if (!$id) {
            $this->messageManager->addError(__('Please select rule'));
        } else {
            try {
                $post = $this->_objectManager->get('Magenest\UltimateFollowupEmail\Model\Rule')->load($id);
                $post->delete();
                $this->messageManager->addSuccess(
                    __('The rule have been deleted.')
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
