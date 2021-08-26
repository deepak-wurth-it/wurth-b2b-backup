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

namespace Mirasvit\Brand\Model;

use Magento\Framework\DataObject;
use Magento\Framework\Filter\FilterManager;
use Mirasvit\Brand\Api\Data\BrandInterface;
use Mirasvit\Brand\Api\Data\BrandPageInterface;
use Mirasvit\Brand\Repository\BrandPageRepository;
use Mirasvit\Brand\Service\BrandUrlService;
use Mirasvit\Brand\Service\ImageUrlService;

class Brand extends DataObject implements BrandInterface
{
    private $brandUrlService;

    private $imageUrlService;

    private $brandPageRepository;

    private $filterManager;

    public function __construct(
        BrandPageRepository $brandPageRepository,
        ImageUrlService $imageUrlService,
        BrandUrlService $brandUrlService,
        FilterManager $filterManager,
        array $data = []
    ) {
        $this->brandPageRepository = $brandPageRepository;
        $this->imageUrlService     = $imageUrlService;
        $this->brandUrlService     = $brandUrlService;
        $this->filterManager       = $filterManager;

        parent::__construct($data);
    }

    public function getId(): int
    {
        return (int)$this->getData(self::ID);
    }

    public function getAttributeId(): int
    {
        return (int)$this->getData(self::ATTRIBUTE_ID);
    }

    public function getAttributeCode(): string
    {
        return (string)$this->getData(self::ATTRIBUTE_CODE);
    }

    public function getLabel(): string
    {
        return (string)$this->getData(self::LABEL);
    }

    public function getUrl(): string
    {
        return $this->brandUrlService->getBrandUrl($this);
    }

    public function getUrlKey(): string
    {
        $urlKey = '';

        if ($this->getPage()) {
            $urlKey = $this->getPage()->getUrlKey();
        }

        return $urlKey ? $urlKey : $this->filterManager->translitUrl($this->getLabel());
    }

    public function getImage(): string
    {
        if (!$this->getPage()) {
            return '';
        }

        return $this->getPage()->getId() && $this->getPage()->getLogo()
            ? $this->imageUrlService->getImageUrl($this->getPage()->getLogo())
            : '';
    }

    public function getPage(): ?BrandPageInterface
    {
        return $this->getData(self::PAGE);
    }
}
