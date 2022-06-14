<?php
declare(strict_types=1);

namespace Wcb\MirasvitSearch\Model;

use Exception;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\UrlInterface;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BannerUploader
 */
class ImageUploader
{
    /**
     * @var string
     */
    const IMAGE_TMP_PATH = 'cmsimage';
    /**
     * @var string
     */
    const IMAGE_PATH = 'cmsimage';
    /**
     * Core file storage database
     *
     * @var Database
     */
    protected $coreFileStorageDatabase;
    /**
     * Media directory object (writable).
     *
     * @var WriteInterface
     */
    protected $mediaDirectory;
    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * Base tmp path
     *
     * @var string
     */
    protected $baseTmpPath;
    /**
     * Base path
     *
     * @var string
     */
    protected $basePath;
    /**
     * Allowed extensions
     *
     * @var string
     */
    protected $allowedExtensions;
    /**
     * Uploader factory
     *
     * @var UploaderFactory
     */
    private $uploaderFactory;

    /**
     * @param Database $coreFileStorageDatabase
     * @param Filesystem $filesystem
     * @param UploaderFactory $uploaderFactory
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     * @param $baseTmpPath
     * @param $basePath
     * @param array $allowedExtensions
     * @throws FileSystemException
     */
    public function __construct(
        Database $coreFileStorageDatabase,
        Filesystem $filesystem,
        UploaderFactory $uploaderFactory,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        $baseTmpPath = self::IMAGE_TMP_PATH,
        $basePath = self::IMAGE_PATH,
        $allowedExtensions = []
    ) {
        $this->coreFileStorageDatabase = $coreFileStorageDatabase;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->uploaderFactory = $uploaderFactory;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->baseTmpPath = $baseTmpPath;
        $this->basePath = $basePath;
        $this->allowedExtensions = $allowedExtensions;
    }

    /**
     * Checking file for save and save it to tmp dir
     *
     * @param string $fileId
     * @return string[]
     * @throws LocalizedException
     */
    public function saveFileToTmpDir($fileId)
    {
        $baseTmpPath = $this->getBaseTmpPath();
        $uploader = $this->uploaderFactory->create(['fileId' => $fileId]);
        $uploader->setAllowedExtensions($this->getAllowedExtensions());
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(false);
        $result = $uploader->save($this->mediaDirectory->getAbsolutePath($baseTmpPath));
        if (!$result) {
            throw new LocalizedException(
                __('File can not be saved to the destination folder.')
            );
        }
        /**
         * Workaround for prototype 1.7 methods "isJSON", "evalJSON" on Windows OS
         */
        $result['tmp_name'] = str_replace('\\', '/', $result['tmp_name']);
        $result['path'] = str_replace('\\', '/', $result['path']);
        $result['url'] = $this->getBaseUrl() . $this->getFilePath($baseTmpPath, $result['file']);
        if (isset($result['file'])) {
            try {
                $relativePath = rtrim($baseTmpPath, '/') . '/' . ltrim($result['file'], '/');
                $this->coreFileStorageDatabase->saveFile($relativePath);
            } catch (Exception $e) {
                $this->logger->critical($e);
                throw new LocalizedException(
                    __('Something went wrong while saving the file(s).')
                );
            }
        }
        return $result;
    }

    /**
     * Retrieve base tmp path
     *
     * @return string
     */
    public function getBaseTmpPath()
    {
        return $this->baseTmpPath;
    }

    /**
     * Set base tmp path
     *
     * @param string $baseTmpPath
     *
     * @return void
     */
    public function setBaseTmpPath($baseTmpPath)
    {
        $this->baseTmpPath = $baseTmpPath;
    }

    /**
     * Retrieve base path
     *
     * @return string[]
     */
    public function getAllowedExtensions()
    {
        return $this->allowedExtensions;
    }

    /**
     * Set allowed extensions
     *
     * @param string[] $allowedExtensions
     *
     * @return void
     */
    public function setAllowedExtensions($allowedExtensions)
    {
        $this->allowedExtensions = $allowedExtensions;
    }

    public function getBaseUrl()
    {
        return $this->storeManager
            ->getStore()
            ->getBaseUrl(
                UrlInterface::URL_TYPE_MEDIA
            );
    }

    /**
     * Retrieve path
     *
     * @param string $path
     * @param string $name
     *
     * @return string
     */
    public function getFilePath($path, $name)
    {
        return rtrim($path, '/') . '/' . ltrim($name, '/');
    }

    /**
     * @param $input
     * @param $data
     * @return string
     */
    public function uploadFileAndGetName($input, $data)
    {
        if (!isset($data[$input])) {
            return '';
        }
        if (is_array($data[$input]) && !empty($data[$input]['delete'])) {
            return '';
        }
        if (isset($data[$input][0]['name']) && isset($data[$input][0]['tmp_name'])) {
            try {
                $result = $this->moveFileFromTmp($data[$input][0]['file']);
                return $result;
            } catch (Exception $e) {
                return '';
            }
        } elseif (isset($data[$input][0]['name'])) {
            return $data[$input][0]['name'];
        }
        return '';
    }

    /**
     * Checking file for moving and move it
     *
     * @param string $name
     * @return string
     * @throws LocalizedException
     */
    public function moveFileFromTmp($name)
    {
        $baseTmpPath = $this->getBaseTmpPath();
        $basePath = $this->getBasePath();
        $baseFilePath = $this->getFilePath($basePath, $name);
        $baseTmpFilePath = $this->getFilePath($baseTmpPath, $name);
        try {
            $this->coreFileStorageDatabase->copyFile(
                $baseTmpFilePath,
                $baseFilePath
            );
            $this->mediaDirectory->renameFile(
                $baseTmpFilePath,
                $baseFilePath
            );
        } catch (Exception $e) {
            throw new LocalizedException(
                __('Something went wrong while saving the file(s).')
            );
        }
        return $name;
    }

    /**
     * Retrieve base path
     *
     * @return string
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * Set base path
     *
     * @param string $basePath
     *
     * @return void
     */
    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
    }
}
