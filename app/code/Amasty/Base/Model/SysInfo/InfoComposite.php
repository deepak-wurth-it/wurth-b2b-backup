<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Base
*/


declare(strict_types=1);

namespace Amasty\Base\Model\SysInfo;

class InfoComposite implements InfoProviderInterface
{
    /**
     * @var InfoProviderInterface[]
     */
    private $providers;

    public function __construct(
        array $providers = []
    ) {
        $this->providers = $providers;
    }

    public function generate(): array
    {
        $info = [];

        foreach ($this->providers as $providerName => $provider) {
            if ($provider instanceof InfoProviderInterface) {
                $info[$providerName] = $provider->generate();
            } else {
                throw new \InvalidArgumentException(
                    __('Object must be an instance of %1', InfoProviderInterface::class)->render()
                );
            }
        }

        return $info;
    }
}
