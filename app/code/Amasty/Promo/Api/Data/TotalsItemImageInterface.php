<?php

namespace Amasty\Promo\Api\Data;

interface TotalsItemImageInterface
{
    /**
     * @param string $src
     *
     * @return $this
     */
    public function setImageSrc($src);

    /**
     * @return string
     */
    public function getImageSrc();

    /**
     * @param string|null $alt
     *
     * @return $this
     */
    public function setImageAlt($alt);

    /**
     * @return string
     */
    public function getImageAlt();

    /**
     * @param string|null $width
     *
     * @return $this
     */
    public function setImageWidth($width);

    /**
     * @return string
     */
    public function getImageWidth();

    /**
     * @param string|null $height
     *
     * @return $this
     */
    public function setImageHeight($height);

    /**
     * @return string
     */
    public function getImageHeight();
}
