<?php
/**
 * Author: Eric Quach
 * Date: 4/17/18
 */
namespace Magenest\UltimateFollowupEmail\Block\Adminhtml\Chart;

use Magento\Framework\Stdlib\DateTime;

class AbstractChart extends \Magento\Backend\Block\Widget
{
    public function getPeriodFromParam()
    {
        return $this->getRequest()->getParam('from');
    }

    public function getPeriodToParam()
    {
        return $this->getRequest()->getParam('to');
    }

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
     * @param string|array $dateFields
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection mixed
     */
    protected function applyPeriodToCollection($collection, $dateFields)
    {
        if (!is_array($dateFields)) {
            $dateFields = [$dateFields];
        }
        if ($from = $this->getPeriodFromParam()) {
            $fromFields = [];
            foreach ($dateFields as $field) {
                $fromFields[] = $field . " >= '" . $this->getPhpFormatDate($from) . "'";
            }
            $collection->getSelect()
                ->where(implode(' OR ', $fromFields));
        }
        if ($to = $this->getPeriodToParam()) {
            $toFields = [];
            foreach ($dateFields as $field) {
                $toFields[] = $field . " <= '" . $this->getPhpFormatDate($to) . "'";
            }
            $collection->getSelect()
                ->where(implode(' OR ', $toFields));
        }
        return $collection;
    }

    public function getPhpFormatDate($date)
    {
        return date(DateTime::DATE_PHP_FORMAT, strtotime($date));
    }
}