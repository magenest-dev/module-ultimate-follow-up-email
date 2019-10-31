<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 20/05/2016
 * Time: 11:24
 */
namespace Magenest\UltimateFollowupEmail\Block\Adminhtml\System\Config;

class Account extends \Magento\Config\Block\System\Config\Form\Field
{


    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $values = $element->getValues();

        $html = '<ul class="account-information" style="list-style: none">';
        if ($values) {
            foreach ($values as $dat) {
                $html .= "<li> <span class='value-label'>{$dat['value']} </span> <span class='value-label'>{$dat['label']}</span> </li>";
            }
        }

        $html .= '</ul>';

        return $html;
    }
}
