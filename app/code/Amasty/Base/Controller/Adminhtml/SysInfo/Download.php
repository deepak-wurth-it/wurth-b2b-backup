<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Base
*/


declare(strict_types=1);

namespace Amasty\Base\Controller\Adminhtml\SysInfo;

use Amasty\Base\Model\SysInfo\FormatterInterface;
use Amasty\Base\Model\SysInfo\InfoProviderInterface;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteFactory;

class Download extends Action
{
    const FILE_NAME = 'system_information';
    const CONTENT_TYPE = 'application/octet-stream';

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var WriteFactory
     */
    private $writeFactory;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var InfoProviderInterface
     */
    private $infoProvider;

    /**
     * @var FormatterInterface
     */
    private $formatter;

    public function __construct(
        Action\Context $context,
        Filesystem $filesystem,
        WriteFactory $writeFactory,
        FileFactory $fileFactory,
        InfoProviderInterface $infoProvider,
        FormatterInterface $formatter
    ) {
        parent::__construct($context);
        $this->filesystem = $filesystem;
        $this->writeFactory = $writeFactory;
        $this->fileFactory = $fileFactory;
        $this->infoProvider = $infoProvider;
        $this->formatter = $formatter;
    }

    public function execute()
    {
        try {
            $info = $this->infoProvider->generate();
            list($content, $extension) = $this->formatter->format($info);

            $tmpDir = $this->filesystem->getDirectoryWrite(DirectoryList::TMP);
            $filePath = self::FILE_NAME . uniqid() . '.' . $extension;
            $tmpDir->writeFile($filePath, $content);

            return $this->fileFactory->create(
                sprintf('%s.%s', self::FILE_NAME, $extension),
                [
                    'type' => 'filename',
                    'value' => $filePath,
                    'rm' => true
                ],
                DirectoryList::TMP,
                self::CONTENT_TYPE
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath($this->_redirect->getRefererUrl());

        return $resultRedirect;
    }
}
