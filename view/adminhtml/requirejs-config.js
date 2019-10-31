/**
 * Copyright © 2016 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    map: {
        '*': {
            categoryForm:       'Magento_Catalog/catalog/category/form',
            newCategoryDialog:  'Magento_Catalog/js/new-category-dialog',
            categoryTree:       'Magento_Catalog/js/category-tree',
            productGallery:     'Magento_Catalog/js/product-gallery',
            baseImage:          'Magento_Catalog/catalog/base-image-uploader',
            mailAttach :         'Magenest_UltimateFollowupEmail/js/attach/upload',
            productAttributes:  'Magento_Catalog/catalog/product-attributes'
        }
    },
    deps: [
        'Magento_Catalog/catalog/product'
    ],
    config: {
        mixins: {
            'Magento_SalesRule/js/form/element/coupon-type': {  // Target module
                'Magenest_UltimateFollowupEmail/js/coupon/coupon-type': true  // Extender module
            }
        }
    }
};