<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 09/12/2016
 * Time: 10:57
 */

namespace Magenest\UltimateFollowupEmail\Observer\Downloadable;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class LinkAfter implements ObserverInterface
{
    protected $linkFactory;

    public function __construct(
        \Magenest\UltimateFollowupEmail\Model\LinkFactory $linkFactory
    ) {
        $this->linkFactory = $linkFactory;
    }
    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $downloadableLink = $observer->getEvent()->getDataObject();
        if ($downloadableLink instanceof \Magento\Downloadable\Model\Link) {
            $linkCollection = $this->linkFactory->create()->getColletion()->addFieldToFilter('link_file', $downloadableLink->getData('link_if'))
            ->addFieldToFilter('state', 'new');

            if ($linkCollection->getSize() > 0) {
                $linkBean = $linkCollection->getFirstItem();

                //generate email to the customers who already bought it
                $this->generateEmail($linkBean);

                $linkBean->addData(['state' =>'pending',
                                    'link_id' =>$downloadableLink->getId()
                ])->save();
            }
        }
    }

    public function generateEmail($linkBean)
    {
        /**
         * get the list customer
         * $rows
         *
         */
    }
}
