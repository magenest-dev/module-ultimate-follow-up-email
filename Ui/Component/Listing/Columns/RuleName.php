<?php
namespace Magenest\UltimateFollowupEmail\Ui\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;
use Magenest\UltimateFollowupEmail\Model\Config\Source\UnsubscriberStatus;

/**
 * Class Status
 * @package Magenest\UltimateFollowupEmail\Ui\Component\Listing\Columns
 */
class RuleName extends Column
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
                if (isset($item['rule_id']) && $item['rule_id'] == 0) {
                    $item['rule_name'] = "Unsubscribe All Events";
                }
            }
        }

        return $dataSource;
    }
}
