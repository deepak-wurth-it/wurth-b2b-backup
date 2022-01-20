<?php

namespace Amasty\Promo\Model\Quote\Totals\Item;

use Amasty\Promo\Api\Data\TotalsItemImageInterface;
use Magento\Framework\DataObject;

class ImageData extends DataObject implements TotalsItemImageInterface
{
    /**
     * {@inheritdoc}
     */
    public function setImageSrc($src)
    {
        $this->setData('image_src', $src);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getImageSrc()
    {
        return $this->_getData('image_src');
    }

    /**
     * {@inheritdoc}
     */
    public function setImageAlt($alt)
    {
        $this->setData('image_alt', $alt);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getImageAlt()
    {
        return $this->_getData('image_alt');
    }

    /**
     * {@inheritdoc}
     */
    public function setImageWidth($width)
    {
        $this->setData('image_width', $width);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getImageWidth()
    {
        return $this->_getData('image_width');
    }

    /**
     * {@inheritdoc}
     */
    public function setImageHeight($height)
    {
        $this->setData('image_height', $height);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getImageHeight()
    {
        return $this->_getData('image_height');
    }
}
