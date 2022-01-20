<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Base
*/


declare(strict_types=1);

namespace Amasty\Base\Model\Response;

use Amasty\Base\Model\MagentoVersion;
use Magento\Framework\App;
use Magento\Framework\Filesystem\File\ReadInterface;
use Magento\Framework\Session\Config\ConfigInterface;
use Magento\Framework\Stdlib;

/**
 * Response class for downloading files on frontend
 * @since 1.10.6
 */
abstract class AbstractOctetResponse extends App\Response\Http implements OctetResponseInterface
{
    private $fileName = null;

    /**
     * @var DownloadOutput
     */
    protected $downloadHelper;

    /**
     * @var ReadInterface
     */
    protected $readResource;

    public function __construct(
        DownloadOutput $downloadHelper,
        MagentoVersion $magentoVersion,
        App\Request\Http $request,
        Stdlib\CookieManagerInterface $cookieManager,
        Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        App\Http\Context $context,
        Stdlib\DateTime $dateTime,
        ConfigInterface $sessionConfig = null
    ) {
        $this->downloadHelper = $downloadHelper;
        $this->initHeaders();
        $arguments = [$request, $cookieManager, $cookieMetadataFactory, $context, $dateTime];

        if (version_compare($magentoVersion->get(), '2.2.4', '>')) {
            $arguments[] = $sessionConfig;
        }

        parent::__construct(...$arguments);
    }

    /**
     * Can be used for override output handler.
     * Method getContentDisposition() implementation is required due the inheritance.
     * @see OctetResponseInterface
     */
    public function sendOctetResponse()
    {
        $this->downloadHelper->setResourceHandler($this->readResource);
        $this->downloadHelper->output();
    }

    public function sendResponse()
    {
        $this->setHeader(
            'Content-Disposition',
            $this->getContentDisposition() . '; filename=' . $this->getFileName(),
            true
        );

        if (!$this->getHeader('Content-Length')) {
            $resourceStats = $this->readResource->stat();

            if ($resourceSize = $resourceStats['size']) {
                $this->setHeader('Content-Length', $resourceSize);
            }
        }

        $this->clearBody();
        $this->sendHeaders();
        $this->sendOctetResponse();
    }

    public function setReadResource(ReadInterface $readResource): OctetResponseInterface
    {
        $this->readResource = $readResource;

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): OctetResponseInterface
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function setContentType(string $contentType): OctetResponseInterface
    {
        $this->setHeader('Content-type', $contentType, true);

        return $this;
    }

    public function setContentLength(int $length): OctetResponseInterface
    {
        $this->setHeader('Content-Length', $length, true);

        return $this;
    }

    public function getContentDisposition(): string
    {
        return (string)$this->downloadHelper->getContentDisposition() ?: 'attachment';
    }

    private function initHeaders(): OctetResponseInterface
    {
        $this->setHttpResponseCode(200);
        $this->setHeader('Pragma', 'public', true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Content-type', 'application/octet-stream', true)
            ->setHeader('Last-Modified', date('r'), true);

        return $this;
    }
}
