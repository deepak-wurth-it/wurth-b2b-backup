<?php

namespace Amasty\BannersLite\Model;

class BannerImageUpload extends \Magento\Catalog\Model\ImageUploader
{
    /**
     * @inheritdoc
     */
    public function moveFileFromTmp($imageName, $returnRelativePath = false)
    {
        $baseTmpPath = $this->getBaseTmpPath();
        $basePath = $this->getBasePath();
        $validName = $this->getValidNewFileName($basePath, $imageName);

        $baseImagePath = $this->getFilePath($basePath, $validName);
        $baseTmpImagePath = $this->getFilePath($baseTmpPath, $imageName);

        try {
            $this->coreFileStorageDatabase->copyFile(
                $baseTmpImagePath,
                $baseImagePath
            );
            $this->mediaDirectory->renameFile(
                $baseTmpImagePath,
                $baseImagePath
            );
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Something went wrong while saving the file(s).')
            );
        }

        return $returnRelativePath ? $baseImagePath : $validName;
    }

    /**
     * @param string $fileName
     *
     * @return string
     */
    public function duplicateFile($fileName)
    {
        $basePath = $this->getBasePath();
        $validName = $this->getValidNewFileName($basePath, $fileName);

        $oldName = $this->getFilePath($basePath, $fileName);
        $newName = $this->getFilePath($basePath, $validName);

        $this->mediaDirectory->copyFile(
            $oldName,
            $newName
        );

        return $validName;
    }

    /**
     * @param string $basePath
     * @param string $imageName
     *
     * @return string
     */
    private function getValidNewFileName($basePath, $imageName)
    {
        $basePath = $this->mediaDirectory->getAbsolutePath($basePath) . DIRECTORY_SEPARATOR . $imageName;

        return \Magento\Framework\File\Uploader::getNewFileName($basePath);
    }
}
