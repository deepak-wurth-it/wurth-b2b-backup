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
 * @package   mirasvit/module-search-ultimate
 * @version   2.0.56
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */



declare(strict_types=1);

namespace Mirasvit\Misspell\Block;

use Magento\Framework\View\Element\Template;
use Mirasvit\Misspell\Service\QueryService;
use Mirasvit\Misspell\Service\TextService;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Element\Template\Context;

class Misspell extends Template
{
    protected $queryService;

    protected $textService;

    protected $urlFactory;

    protected $context;

    public function __construct(
        QueryService $queryService,
        TextService $textService,
        UrlFactory $urlFactory,
        Context $context
    ) {
        $this->queryService = $queryService;
        $this->textService = $textService;
        $this->urlFactory = $urlFactory;

        parent::__construct($context);
    }

    public function getQueryUrl(string $query): string
    {
        return $this->urlFactory->create()
            ->addQueryParams(['q' => $query])
            ->getUrl('catalogsearch/result');
    }

    public function highlight(string $new, string $old, string $tag = 'em'): string
    {
        $new = strtolower($new);
        $old = strtolower($old);

        return $this->textService->htmlDiff($new, $old, $tag);
    }

    public function getQueryText(): string
    {
        return $this->queryService->getQueryText();
    }

    public function getMisspellText(): string
    {
        return $this->queryService->getMisspellText();
    }

    public function getFallbackText(): string
    {
        return $this->queryService->getFallbackText();
    }

    public function getOriginalQuery(): string
    {
        return $this->queryService->getOriginalQuery();
    }
}
