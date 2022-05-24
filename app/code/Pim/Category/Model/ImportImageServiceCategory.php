<?php

namespace Pim\Category\Model;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;

/**
 * Class ImportImageService
 * assign images to products by image URL
 */
class ImportImageServiceCategory
{
    /**
     * Directory List
     *
     * @var DirectoryList
     */
    protected $directoryList;
    /**
     * File interface
     *
     * @var File
     */
    protected $file;
    /**
     * ImportImageService constructor
     *
     * @param DirectoryList $directoryList
     * @param File $file
     */
    public function __construct(
        DirectoryList $directoryList,
        File $file
    ) {
        $this->directoryList = $directoryList;
        $this->file = $file;
    }
    /**
     * Main service executor
     *
     * @param Category $categoryobj
     * @param string $imageUrl
     * @param array $imageType
     * @param bool $visible
     *
     * @return bool
     */
    public function execute($categoryobj, $imageUrl, $visible = true, $imageType = [])
    {
        /** @var string $tmpDir */
        $tmpDir = $this->getMediaDirTmpDir();
        $thumbDir = $this->getMediaDirThumbnailsDir();

        $imageName = baseName($imageUrl);

        /** create folder if it is not exists */
        $this->file->checkAndCreateFolder($tmpDir);
        $this->file->checkAndCreateFolder($thumbDir);

        /** @var string $newFileName */
        $newFileName = $tmpDir . $imageName;
        $newFileNameThumb = $thumbDir . $imageName;

        /** read file from URL and copy it to the new destination */
        $result = $this->file->read($imageUrl, $newFileName);
        $resultThumb = $this->file->read($imageUrl, $newFileNameThumb);

        if ($result && $resultThumb) {
            /** add saved file to the $categoryobj gallery */
            //$imageType = array('image', 'small_image', 'thumbnail');
            $catImage = DirectoryList::MEDIA. DIRECTORY_SEPARATOR . 'catalog/category' . DIRECTORY_SEPARATOR.baseName($newFileName);
            $categoryobj->setImage($catImage, $imageType, $visible, false); // make sure image will be in pub/media/catalog/category/

            $pathThumb = DirectoryList::MEDIA. DIRECTORY_SEPARATOR . 'catalog/category/thumbnails' . DIRECTORY_SEPARATOR.baseName($newFileNameThumb);
            $categoryobj->setThumbnail($pathThumb, ['thumbnail'], $visible, false); // make sure image will be in pub/media/catalog/category/thumbnails

        }
        return $result;
    }
    /**
     * Media directory name for the temporary file storage
     * pub/media/tmp
     *
     * @return string
     */
    protected function getMediaDirTmpDir()
    {
        return $this->directoryList->getPath(DirectoryList::MEDIA) . DIRECTORY_SEPARATOR . 'catalog/category' . DIRECTORY_SEPARATOR;
    }

     protected function getMediaDirThumbnailsDir()
    {
        return $this->directoryList->getPath(DirectoryList::MEDIA) . DIRECTORY_SEPARATOR . 'catalog/category/thumbnails' . DIRECTORY_SEPARATOR;
    }
}
