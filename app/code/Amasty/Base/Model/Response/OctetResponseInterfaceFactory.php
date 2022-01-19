<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Base
*/


declare(strict_types=1);

namespace Amasty\Base\Model\Response;

use Magento\Framework\Filesystem\Io;

class OctetResponseInterfaceFactory
{
    /**
     * @var Io\File
     */
    private $ioFile;

    /**
     * @var array
     */
    private $responseFactoryAssociationMap;

    public function __construct(
        Io\File $ioFile,
        array $responseFactoryAssociationMap = []
    ) {
        $this->ioFile = $ioFile;
        $this->responseFactoryAssociationMap = $responseFactoryAssociationMap;
    }

    public function create(
        string $resourcePath,
        string $resourceType = OctetResponseInterface::FILE,
        string $fileName = null
    ): OctetResponseInterface {
        if (!isset($this->responseFactoryAssociationMap[$resourceType])) {
            throw new \InvalidArgumentException('There is no resource handler for type ' . $resourceType);
        }

        $concreteOctetResponse = $this->responseFactoryAssociationMap[$resourceType]->create();

        if (!$concreteOctetResponse instanceof OctetResponseInterface) {
            throw new \LogicException(
                sprintf(
                    'OctetResponse class %s must implement %s interface',
                    get_class($concreteOctetResponse),
                    OctetResponseInterface::class
                )
            );
        }

        $readResource = $concreteOctetResponse->getReadResourceByPath($resourcePath);
        $concreteOctetResponse->setReadResource($readResource);
        $fileName = $fileName ?? $this->getFileNameFromResourcePath($resourcePath);
        $concreteOctetResponse->setFileName($fileName);

        return $concreteOctetResponse;
    }

    private function getFileNameFromResourcePath(string $resourcePath): string
    {
        $resourcePathInfo = $this->ioFile->getPathInfo($resourcePath);

        return $resourcePathInfo['basename'] ?? 'file';
    }
}
