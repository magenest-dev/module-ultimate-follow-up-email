<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 29/01/2016
 * Time: 22:22
 */
namespace Magenest\UltimateFollowupEmail\Block\Form\Element;

class Select extends \Magento\Framework\Data\Form\Element\Select
{


    /**
     * Format an option as Html
     *
     * @param  array $option
     * @param  array $selected
     * @return string
     */
    protected function _optionToHtml($option, $selected)
    {
        if (is_array($option['value'])) {
            $html = '<optgroup label="'.$option['label'].'">'."\n";
            foreach ($option['value'] as $groupItem) {
                $html .= $this->_optionToHtml($groupItem, $selected);
            }

            $html .= '</optgroup>'."\n";
        } else {
            $html  = '<option value="'.$this->_escape($option['value']).'"';
            $html .= isset($option['title']) ? 'title="'.$this->_escape($option['title']).'"' : '';
            $html .= isset($option['style']) ? 'style="'.$option['style'].'"' : '';

            if (isset($option['params'])) {
                foreach ($option['params'] as $key => $value) {
                    $html .= $key.'="'.$value.'"';
                }
            }

            if (in_array($option['value'], $selected)) {
                $html .= ' selected="selected"';
            }

            $html .= '>'.$this->_escape($option['label']).'</option>'."\n";
        }
        return $html;
    }
}
