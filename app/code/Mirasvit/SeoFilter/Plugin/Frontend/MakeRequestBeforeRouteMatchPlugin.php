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
 * @package   mirasvit/module-seo-filter
 * @version   1.1.5
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\SeoFilter\Plugin\Frontend;

use Magento\Framework\App\RequestInterface;
use Mirasvit\SeoFilter\Model\ConfigProvider;
use Mirasvit\SeoFilter\Service\MatchService;

/**
 * @see \Magento\Framework\App\Router\Base::match()
 */
class MakeRequestBeforeRouteMatchPlugin
{
    private $params = null;

    private $configProvider;

    private $matchService;

    private $request;

    public function __construct(
        MatchService $matchService,
        ConfigProvider $configProvider,
        RequestInterface $request
    ) {
        $this->matchService   = $matchService;
        $this->configProvider = $configProvider;
        $this->request        = $request;
    }

    /**
     * Apply friendly filters
     *
     * @param object           $subject
     * @param RequestInterface $request
     *
     * @return void
     */
    public function beforeMatch($subject, RequestInterface $request): void
    {
        if (!$this->configProvider->isEnabled()) {
            return;
        }

        if ($request->getActionName()) {
            // request already processed (found rewrite)
            return;
        }

        $params = $this->matchService->getParams();

        if ($params && $params['match']) {
            /** @var \Magento\Framework\App\Request\Http $request */

            if ($params['is_brand_page'] || $params['is_all_pages']) {
                $request->setParams($params['params']);

                $this->params = $params['params'];
            }

            if ($params['category_id']) {
                $request->setRouteName('catalog')
                    ->setModuleName('catalog')
                    ->setControllerName('category')
                    ->setActionName('view')
                    ->setParam('id', $params['category_id'])
                    ->setParams($params['params']);

                $this->params = $params['params'];
            }
        }
    }

    /**
     * @param object $subject
     * @param object $result
     *
     * @return object
     */
    public function afterMatch($subject, $result)
    {
        //restore params (match can overwrite params with variables)
        if ($this->params) {
            $this->request->setParams($this->params);
        }

        return $result;
    }
}
