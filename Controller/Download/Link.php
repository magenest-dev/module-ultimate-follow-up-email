<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 10/12/2016
 * Time: 11:26
 */

namespace Magenest\UltimateFollowupEmail\Controller\Download;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;

class Link extends \Magento\Framework\App\Action\Action
{

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        // TODO: Implement execute() method.
        $id = $this->getRequest()->getParam('id');
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $linkModel = $objectManager->create('Magenest\UltimateFollowupEmail\Model\Link')->load($id);

        $linkFile  = $linkModel->getData('link_file');

        $resource = $objectManager->get(
            'Magento\Downloadable\Helper\File'
        )->getFilePath(
            'downloadable/files/links',
            $linkFile
        );
        $resourceType = 'file';

        $this->_processDownload($resource, $resourceType);
    }

    protected function _processDownload($path, $resourceType)
    {
        /* @var $helper DownloadHelper */
        $helper = $this->_objectManager->get('Magento\Downloadable\Helper\Download');

        $helper->setResource($path, $resourceType);
        $fileName = $helper->getFilename();
        $contentType = $helper->getContentType();

        $this->getResponse()->setHttpResponseCode(
            200
        )->setHeader(
            'Pragma',
            'public',
            true
        )->setHeader(
            'Cache-Control',
            'must-revalidate, post-check=0, pre-check=0',
            true
        )->setHeader(
            'Content-type',
            $contentType,
            true
        );

        if ($fileSize = $helper->getFileSize()) {
            $this->getResponse()->setHeader('Content-Length', $fileSize);
        }

        if ($contentDisposition = $helper->getContentDisposition()) {
            $this->getResponse()->setHeader('Content-Disposition', $contentDisposition . '; filename=' . $fileName);
        }

        $this->getResponse()->clearBody();
        $this->getResponse()->sendHeaders();

        $helper->output();
    }

    protected function hasRightToDownload()
    {
    }
}
