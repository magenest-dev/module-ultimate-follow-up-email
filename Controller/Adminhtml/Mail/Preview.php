<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 13/11/2015
 * Time: 19:32
 */

namespace Magenest\UltimateFollowupEmail\Controller\Adminhtml\Mail;

use Magento\Framework\Controller\ResultFactory;

use Magento\Backend\App\Action;

class Preview extends Action
{


    /**
     * Preview Follow up Email
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $data = $this->getRequest()->getParams();
        if (empty($data) || !isset($data['id'])) {
            $this->_forward('noroute');
            return;
        }

        // set default value for selected store
        /*
            * @var \Magento\Store\Model\StoreManager $storeManager
        */
        $storeManager = $this->_objectManager->get('Magento\Store\Model\StoreManager');
        $defaultStore = $storeManager->getDefaultStoreView();
        if (!$defaultStore) {
            $allStores = $storeManager->getStores();
            if (isset($allStores[0])) {
                $defaultStore = $allStores[0];
            }
        }

        $data['preview_store_id'] = $defaultStore ? $defaultStore->getId() : null;

        $this->_view->getLayout()->getBlock('preview_form')->setFormData($data);
        $this->_view->getLayout()->getUpdate()->removeHandle('default');
        // $this->_view->getPage()->setTemplate('b');
        $this->_view->renderLayout();
    }

    /**
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_UltimateFollowupEmail::ultimatefollowupemail_mail');
    }
}
