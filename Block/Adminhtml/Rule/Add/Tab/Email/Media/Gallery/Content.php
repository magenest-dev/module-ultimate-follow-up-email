<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 24/10/2016
 * Time: 10:37
 */
namespace Magenest\UltimateFollowupEmail\Block\Adminhtml\Rule\Add\Tab\Email\Media\Gallery;

use Magento\Backend\Block\Media\Uploader;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Backend\Block\DataProviders\ImageUploadConfig as ImageUploadConfigDataProvider;
use Magento\Framework\App\ObjectManager;

class Content extends \Magento\Backend\Block\Widget
{
    /**
     * @var string
     */
    protected $_template = 'media/gallery.phtml';

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    protected $_mediaConfig;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    protected $productFactory;
    protected $_coreRegistry;

    protected $serializer;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Catalog\Model\Product\Media\Config $mediaConfig
     * @param array $data
     */
    public function __construct(
        \Magenest\UltimateFollowupEmail\Model\Serializer $serializer,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_mediaConfig = $mediaConfig;
        $this->serializer = $serializer;
        $this->productFactory = $productFactory;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return AbstractBlock
     */
    protected function _prepareLayout()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        if(class_exists(\Magento\Backend\Block\DataProviders\ImageUploadConfig::class)){
            $imageUploadConfigDataProvider = $objectManager->get(\Magento\Backend\Block\DataProviders\ImageUploadConfig::class);
            $this->addChild(
                'uploader',
                \Magento\Backend\Block\Media\Uploader::class,
                ['image_upload_config_data' => $imageUploadConfigDataProvider]
            );
        }elseif (class_exists(\Magento\Backend\Block\DataProviders\UploadConfig::class)){
            $uploadConfigDataProvider = $objectManager->get(\Magento\Backend\Block\DataProviders\UploadConfig::class);
            $this->addChild(
                'uploader',
                \Magento\Backend\Block\Media\Uploader::class,
                ['image_upload_config_data' => $uploadConfigDataProvider]
            );
        }else{
            $this->addChild(
                'uploader',
                \Magento\Backend\Block\Media\Uploader::class
            );
        }

        $this->unsetChild('new-video');

        $this->getUploader()->getConfig()->setUrl(
            $this->_urlBuilder->getUrl('ultimatefollowupemail/rule/upload')
        )->setFileField(
            'image'
        )->setFilters(
            [
                'images' => [
                    'label' => __('Images (.gif, .jpg, .png)'),
                    'files' => ['*.gif', '*.jpg', '*.jpeg', '*.png'],
                ],
            ]
        );

        //  $this->_eventManager->dispatch('catalog_product_gallery_prepare_layout', ['block' => $this]);

        return parent::_prepareLayout();
    }

    /**
     * Retrieve uploader block
     *
     * @return Uploader
     */
    public function getUploader()
    {
        return $this->getChildBlock('uploader');
    }

    /**
     * Retrieve uploader block html
     *
     * @return string
     */
    public function getUploaderHtml()
    {
        return $this->getChildHtml('uploader');
    }

    /**
     * @return string
     */
    public function getJsObjectName()
    {
        return $this->getHtmlId() . 'JsObject';
    }

    /**
     * @return string
     */
    public function getAddImagesButton()
    {
        return $this->getButtonHtml(
            __('Add New Images'),
            $this->getJsObjectName() . '.showUploader()',
            'add',
            $this->getHtmlId() . '_add_images_button'
        );
    }

    public function getImages()
    {
        $value = $this->getElement()->getImages();
        if (is_array($value) &&
            array_key_exists('images', $value) &&
            is_array($value['images']) &&
            count($value['images'])
        ) {
            $directory = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA);
            $images = $this->sortImagesByPosition($value['images']);
            foreach ($images as &$image) {
                $image['url'] = $this->_mediaConfig->getMediaUrl($image['file']);
                $fileHandler = $directory->stat($this->_mediaConfig->getMediaPath($image['file']));
                $image['size'] = $fileHandler['size'];
            }
            return $this->_jsonEncoder->encode($images);
        }
        return '[]';
    }

    public function getImagesJson()
    {
      //$ruleId =
        $model      = $this->_coreRegistry->registry('current_fue_rule');

        if (is_object($model)) {
            $ruleId = $model->getId();
            $imagesString = $model->getData('attached_files');
            $value = $this->serializer->unserialize($imagesString);
            if (is_array($value) &&
                array_key_exists('images', $value) &&
                is_array($value['images']) &&
                count($value['images'])
            ) {
                $directory = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA);
                $images = $this->sortImagesByPosition($value['images']);
                foreach ($images as &$image) {
                    $image['url'] = $this->_mediaConfig->getTmpMediaUrl($image['file']);
                   // $fileHandler = $directory->stat($image['url']);
                   // $image['size'] = $fileHandler['size'];
                }
                return $this->_jsonEncoder->encode($images);
            }
        }
        return '[]';
    }
    /**
     * @return string
     */
    public function getImagesJsonProductTest()
    {
        $objectManager = ObjectManager::getInstance();
        //return $objectManager->create('Magenest\MysqlSearch\Model\Adapter')
       // $element = $objectManager->create('Magento\Catalog\Model\Product')->load(12);

        $element = $this->productFactory->create() ->load(12);

        //Aiden L

       // $images = $element->getImages();
        $value = $element->getData('media_gallery');
        if (is_array($value) &&
            array_key_exists('images', $value) &&
            is_array($value['images']) &&
            count($value['images'])
        ) {
            $directory = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA);
            $images = $this->sortImagesByPosition($value['images']);
            foreach ($images as &$image) {
                $image['url'] = $this->_mediaConfig->getMediaUrl($image['file']);
                $fileHandler = $directory->stat($this->_mediaConfig->getMediaPath($image['file']));
                $image['size'] = $fileHandler['size'];
            }
            return $this->_jsonEncoder->encode($images);
        }
        return '[]';
    }

    /**
     * Sort images array by position key
     *
     * @param array $images
     * @return array
     */
    private function sortImagesByPosition($images)
    {
        if (is_array($images)) {
            usort($images, function ($imageA, $imageB) {
                return ($imageA['position'] < $imageB['position']) ? -1 : 1;
            });
        }
        return $images;
    }

    /**
     * @return string
     */
    public function getImagesValuesJson()
    {
        $values = [];
        foreach ($this->getMediaAttributes() as $attribute) {
            /* @var $attribute \Magento\Eav\Model\Entity\Attribute */
            $values[$attribute->getAttributeCode()] = $this->getElement()->getDataObject()->getData(
                $attribute->getAttributeCode()
            );
        }
        return $this->_jsonEncoder->encode($values);
    }

    /**
     * Get image types data
     *
     * @return array
     */
    public function getImageTypes()
    {
        return [];
        $imageTypes = [];
        foreach ($this->getMediaAttributes() as $attribute) {
            /* @var $attribute \Magento\Eav\Model\Entity\Attribute */
            $imageTypes[$attribute->getAttributeCode()] = [
                'code' => $attribute->getAttributeCode(),
                'value' => $this->getElement()->getDataObject()->getData($attribute->getAttributeCode()),
                'label' => $attribute->getFrontend()->getLabel(),
                'scope' => __($this->getElement()->getScopeLabel($attribute)),
                'name' => $this->getElement()->getAttributeFieldName($attribute),
            ];
        }
        return $imageTypes;
    }

    /**
     * Retrieve default state allowance
     *
     * @return bool
     */
    public function hasUseDefault()
    {
        foreach ($this->getMediaAttributes() as $attribute) {
            if ($this->getElement()->canDisplayUseDefault($attribute)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieve media attributes
     *
     * @return array
     */
    public function getMediaAttributes()
    {
        $product = $this->productFactory ->create();
        $att = $product->getMediaAttributes();
        return $att;
        //return $this->getElement()->getDataObject()->getMediaAttributes();
    }

    /**
     * Retrieve JSON data
     *
     * @return string
     */
    public function getImageTypesJson()
    {
        return $this->_jsonEncoder->encode($this->getImageTypes());
    }
}
