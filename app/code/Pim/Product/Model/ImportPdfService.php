<?php
   
namespace Pim\Product\Model;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;
use \Magento\Framework\Filesystem;
/**
 * Class ImportImageService
 * assign images to products by image URL
 */
class ImportPdfService
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
        Filesystem $filesystem,
        File $file
    ) {
        $this->directoryList = $directoryList;
        $this->file = $file;
        $this->filesystem = $filesystem;
    }
    /**
     * Main service executor
     *
     * @param Product $product
     * @param string $imageUrl
     * @param array $imageType
     * @param bool $visible
     *
     * @return bool
     */
    public function execute($pdf_name,$pdfUrl)
    { 
        /** @var string $tmpDir */
        $pdfDir = $this->getMediaDirPdfDir();
        /** create folder if it is not exists */
        $this->file->checkAndCreateFolder($pdfDir);
        /** @var string $newFileName */
        $newFileName = $pdfDir . baseName($pdfUrl);
        /** read file from URL and copy it to the new destination */
        $result = $this->file->read($pdfUrl, $newFileName);
     
        if ($result) {
			    $pdf_file = baseName($newFileName);
                $mediapath = DirectoryList::MEDIA.DIRECTORY_SEPARATOR . 'product_pdfs'.DIRECTORY_SEPARATOR.$pdf_file;
				return  $mediapath;
        }
        return $result;
    }
    /**
     * Media directory name for the temporary file storage
     * pub/media/tmp
     *
     * @return string
     */
    protected function getMediaDirPdfDir()
    {
        return $this->directoryList->getPath(DirectoryList::MEDIA) . DIRECTORY_SEPARATOR . 'product_pdfs'.DIRECTORY_SEPARATOR;
    }
}

