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


namespace Mirasvit\SeoNavigation\Service;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Page\Config;
use Mirasvit\SeoNavigation\Model\MetaInterface;

class MetaService implements MetaServiceInterface
{
    use SorterTrait;

    /**
     * @var array
     */
    private $metaStrategies;

    private $pageConfig;

    public function __construct(
        Config $pageConfig,
        array $metaStrategies = []
    ) {
        uasort($metaStrategies, [$this, 'sortStrategies']);
        $this->metaStrategies = $metaStrategies;
        $this->pageConfig     = $pageConfig;
    }

    /**
     * @inheritdoc
     */
    public function apply(RequestInterface $request)
    {
        $metaStrategy = $this->match($request);
        if ($metaStrategy && $this->isAllowed($metaStrategy)) {
            $this->pageConfig->setRobots($metaStrategy->getContent());
        }
    }

    /**
     * @return MetaInterface|false
     */
    private function match(RequestInterface $request)
    {
        $strategy = false;
        foreach ($this->metaStrategies as $metaStrategy) {
            /** @var ValidatorInterface $validator */
            /** @var MetaInterface $provider */
            $validator = $metaStrategy['validator'];
            $provider  = $metaStrategy['provider'];
            if ($validator->isApplicable($request)) {
                $strategy = $provider;
                break;
            }
        }

        return $strategy;
    }

    /**
     * Is robots content can be applied.
     * @return bool
     */
    private function isAllowed(MetaInterface $metaStrategy)
    {
        return !empty($metaStrategy->getContent());
    }
}
