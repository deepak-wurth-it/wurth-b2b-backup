<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Base
*/

declare(strict_types=1);

namespace Amasty\Base\Model;

class LinkValidator
{
    const ALLOWED_DOMAINS = [
        'amasty.com',
        'marketplace.magento.com'
    ];

    /**
     * @param string $link
     *
     * @return bool
     */
    public function validate(string $link): bool
    {
        if (! (string) $link) { // fix for xml object
            return true;
        }

        foreach (static::ALLOWED_DOMAINS as $allowedDomain) {
            if (preg_match('/^http[s]?:\/\/' . $allowedDomain . '\/.*$/', $link) === 1) {
                return true;
            }
        }

        return false;
    }
}
