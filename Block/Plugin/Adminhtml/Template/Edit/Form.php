<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 30/01/2016
 * Time: 20:25
 */
namespace Magenest\UltimateFollowupEmail\Block\Plugin\Adminhtml\Template\Edit;

class Form
{


    public function aroundGetVariables(
        \Magento\Email\Block\Adminhtml\Template\Edit\Form $subject,
        \Closure $proceed
    ) {
    
        $variables = $proceed();
        $count = count($variables);

        $variables[$count]['label'] = __('Follow Up Email Variables');

        $variables[$count]['value'] = [];

        $variables[$count]['value'][] = [
            'value' => '{{var cart}}',
            'label' => __('Abandoned Cart Products HTML'),
        ];
        $variables[$count]['value'][] = [
            'value' => '{{var resumeLink}}',
            'label' => __('Resume Link'),
        ];
        $variables[$count]['value'][] = [
            'value' => '{{var customerName}}',
            'label' => __('Customer Name'),
        ];
        $variables[$count]['value'][] = [
            'value' => '{{var customerFistName}}',
            'label' => __('Customer First Name'),
        ];
        $variables[$count]['value'][] = [
            'value' => '{{var customerLastName}}',
            'label' => __('Customer Last Name'),
        ];
        $variables[$count]['value'][] = [
            'value' => '{{var coupon_code}}',
            'label' => __('Coupon Code'),
        ];
        $variables[$count]['value'][] = [
            'value' => '{{var downloadLink}}',
            'label' => __('Link to download updated item'),
        ];
        $variables[$count]['value'][] = [
            'value' => '{{var orderProductsGrid}}',
            'label' => __('Order Products Grid HTML'),
        ];
        $variables[$count]['value'][] = [
            'value' => '{{var wishlistProduct}}',
            'label' => __('Wishlist Reminder Product HTML'),
        ];
        $variables[$count]['value'][] = [
            'value' => '{{var wishlistProduct}}',
            'label' => __('Wishlist Back In Stock Product HTML'),
        ];
        $variables[$count]['value'][] = [
            'value' => '{{var wishlistOnSaleProduct}}',
            'label' => __('Wishlist On Sale Product HTML'),
        ];
        $variables[$count]['value'][] = [
            'value' => '{{var relatedProductsGrid}}',
            'label' => __('Related Products HTML Grid'),
        ];
        $variables[$count]['value'][] = [
            'value' => '{{var unsubscribeLink}}',
            'label' => __('Unsubscribe Link (All Followup Email)'),
        ];
        $variables[$count]['value'][] = [
            'value' => '{{var resubscribeLink}}',
            'label' => __('Resubscribe Link (All Followup Email)'),
        ];
        $variables[$count]['value'][] = [
            'value' => '{{var unsubscribeRuleLink}}',
            'label' => __('Unsubscribe Link (This Trigger Rule only)'),
        ];
        $variables[$count]['value'][] = [
            'value' => '{{var resubscribeRuleLink}}',
            'label' => __('Resubscribe Link (This Trigger Rule only)'),
        ];
        return $variables;
    }
}
