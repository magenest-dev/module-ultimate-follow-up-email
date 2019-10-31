<?php
namespace Magenest\UltimateFollowupEmail\Ui\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;
use Magenest\UltimateFollowupEmail\Model\Config\Source\UnsubscriberStatus;

/**
 * Class Status
 * @package Magenest\UltimateFollowupEmail\Ui\Component\Listing\Columns
 */
class Status extends Column
{

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['unsubscriber_status']) && $item['unsubscriber_status'] == UnsubscriberStatus::SUBSCRIBED) {
                    $class = 'notice';
                    $label = 'Re-subscribed';
                } else {
                    $class = 'critical';
                    $label = 'Un-subscribed';
                }
                $item['unsubscriber_status'] = '<span class="grid-severity-'
                    . $class .'">'. $label .'</span>';
            }
        }

        return $dataSource;
    }
}
