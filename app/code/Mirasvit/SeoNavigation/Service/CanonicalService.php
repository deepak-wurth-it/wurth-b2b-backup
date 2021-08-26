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
use Mirasvit\SeoNavigation\Model\CanonicalInterface;

class CanonicalService implements CanonicalServiceInterface
{
    use SorterTrait;

    const TYPE = 'canonical';

    /**
     * @var array
     */
    private $canonicalStrategies;
    /**
     * @var Config
     */
    private $pageConfig;

    public function __construct(
        Config $pageConfig,
        array $canonicalStrategies = []
    ) {
        uasort($canonicalStrategies, [$this, 'sortStrategies']);
        $this->canonicalStrategies = $canonicalStrategies;
        $this->pageConfig = $pageConfig;
    }

    /**
     * @inheritdoc
     */
    public function apply(RequestInterface $request)
    {
        $canonicalStrategy = $this->match($request);
        if ($canonicalStrategy && $this->isAllowed($canonicalStrategy, $request)) {
            $this->pageConfig->addRemotePageAsset(
                html_entity_decode($canonicalStrategy->getHref($request)),
                self::TYPE,
                ['attributes' => ['rel' => self::TYPE]]
            );
        }
    }

    /**
     * @return CanonicalInterface|false
     */
    private function match(RequestInterface $request)
    {
        $strategy = false;
        foreach ($this->canonicalStrategies as $canonicalStrategy) {
            /** @var ValidatorInterface $validator */
            /** @var CanonicalInterface $provider */
            $validator = $canonicalStrategy['validator'];
            $provider = $canonicalStrategy['provider'];
            if ($validator->isApplicable($request)) {
                $strategy = $provider;
                break;
            }
        }

        return $strategy;
    }

    /**
     * Is robots content can be applied.
     *
     * @param CanonicalInterface $canonicalStrategy
     * @param RequestInterface   $request
     *
     * @return bool
     */
    private function isAllowed(CanonicalInterface $canonicalStrategy, RequestInterface $request)
    {
        $groups = $this->pageConfig->getAssetCollection()->getGroupByContentType(self::TYPE);

        return !empty($canonicalStrategy->getHref($request)) && $groups === false;
    }
}
