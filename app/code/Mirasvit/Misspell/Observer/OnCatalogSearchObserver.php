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

namespace Mirasvit\Misspell\Observer;

use Magento\Framework\Event\ObserverInterface;
use Mirasvit\Misspell\Service\QueryService;
use Mirasvit\Misspell\Model\ConfigProvider;
use Magento\Framework\App\Response\Http as HttpResponse;
use Mirasvit\Misspell\Repository\SuggestRepository;
use Magento\Framework\Event\Observer as EventObserver;

class OnCatalogSearchObserver implements ObserverInterface
{
    protected $queryService;

    protected $configProvider;

    private $response;

    private $suggestRepository;

    public function __construct(
        QueryService $queryService,
        ConfigProvider $configProvider,
        HttpResponse $response,
        SuggestRepository $suggestRepository
    ) {
        $this->queryService         = $queryService;
        $this->configProvider       = $configProvider;
        $this->response             = $response;
        $this->suggestRepository    = $suggestRepository;
    }

    public function execute(EventObserver $observer): void
    {
        if (!empty($this->queryService->getQueryText()) && (bool) $this->queryService->getNumResults() == false) {
            if ($this->configProvider->isMisspellEnabled()) {
                $result = $this->doSpellCorrection();
            } else {
                $result = false;
            }

            if (!$result && $this->configProvider->isFallbackEnabled()) {
                $this->doFallbackCorrection();
            }
        }
    }

    public function doSpellCorrection(): bool
    {
        $query = $this->queryService->getQueryText();
        $suggest = $this->suggestRepository->suggest($query);

        if ($suggest && $suggest != $query && $suggest != $this->queryService->getMisspellText()) {
            $url = $this->queryService->getMisspellUrl($query, $suggest);
            $this->response->setRedirect($url);

            return true;
        }

        return false;
    }

    public function doFallbackCorrection(): bool
    {
        $query = $this->queryService->getQueryText();
        $fallback = $this->queryService->fallback($query);

        if ($fallback && $fallback != $query && $fallback != $this->queryService->getFallbackText()) {
            $url = $this->queryService->getFallbackUrl($query, $fallback);
            $this->response->setRedirect($url);

            return true;
        }

        return false;
    }
}
