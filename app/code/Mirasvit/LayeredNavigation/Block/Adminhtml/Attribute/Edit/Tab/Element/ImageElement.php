<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-navigation
 * @version   2.0.12
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\LayeredNavigation\Block\Adminhtml\Attribute\Edit\Tab\Element;

/**
 * Class BaseImage
 */
class ImageElement extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    /**
     * Element output template
     */
    const ELEMENT_OUTPUT_TEMPLATE = 'Mirasvit_LayeredNavigation::attribute/image_field.phtml';

    /**
     * Model Url instance
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $url;

    /**
     * @var \Magento\Framework\File\Size
     */
    protected $fileConfig;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $assetRepo;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;
    /**
     * @var int
     */
    private $maxFileSize;

    /**
     * @param \Magento\Framework\Data\Form\Element\Factory           $factoryElement
     * @param \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection
     * @param \Magento\Framework\Escaper                             $escaper
     * @param \Magento\Framework\View\Asset\Repository               $assetRepo
     * @param \Magento\Backend\Model\UrlFactory                      $backendUrlFactory
     * @param \Magento\Framework\File\Size                           $fileConfig
     * @param \Magento\Framework\View\LayoutInterface                $layout
     * @param array                                                  $data
     */
    public function __construct(
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Backend\Model\UrlFactory $backendUrlFactory,
        \Magento\Framework\File\Size $fileConfig,
        \Magento\Framework\View\LayoutInterface $layout,
        array $data = []
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);

        $this->assetRepo   = $assetRepo;
        $this->url         = $backendUrlFactory->create();
        $this->fileConfig  = $fileConfig;
        $this->maxFileSize = $this->getFileMaxSize();
        $this->layout      = $layout;
    }

    /**
     * Get label
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Image');
    }

    /**
     * Return element html code
     * @return string
     */
    public function getElementHtml()
    {
        $block = $this->createElementOutputBlock();
        $this->assignBlockVariables($block);

        return $block->toHtml();
    }

    /**
     * @param \Magento\Framework\View\Element\Template $block
     *
     * @return \Magento\Framework\View\Element\Template
     */
    public function assignBlockVariables(\Magento\Framework\View\Element\Template $block)
    {
        $block->assign([
            'htmlId'               => $this->_escaper->escapeHtml($this->getHtmlId()),
            'fileMaxSize'          => $this->maxFileSize,
            'uploadUrl'            => $this->_escaper->escapeHtml($this->_getUploadUrl()),
            'spacerImage'          => $this->assetRepo->getUrl('images/spacer.gif'),
            'imagePlaceholderText' => __('Click here or drag and drop to add image'),
            'deleteImageText'      => __('Delete image'),
            'hiddenText'           => __('Hidden'),
            'imageManagementText'  => __('Images'),
        ]);

        return $block;
    }


    /**
     * @return \Magento\Framework\View\Element\Template
     */
    public function createElementOutputBlock()
    {
        /** @var \Magento\Framework\View\Element\Template $block */
        $block = $this->layout->createBlock(
            'Magento\Framework\View\Element\Template',
            'm.navigation.product.attribute.form.base.image.element'
        );
        $block->setTemplate(self::ELEMENT_OUTPUT_TEMPLATE);

        return $block;
    }

    /**
     * Get url to upload files
     * @return string
     */
    protected function _getUploadUrl()
    {
        return $this->url->getUrl('layered_navigation/image/upload');
    }

    /**
     * Get maximum file size to upload in bytes
     * @return int
     */
    protected function getFileMaxSize()
    {
        return $this->fileConfig->getMaxFileSize();
    }
}
