<?php

namespace Wcb\MirasavitSearchAutocomplate\Plugin\Block;

use Mirasvit\SearchAutocomplete\Block\Injection as mirasvitInjection;

class Injection
{
    public function afterGetJsConfig(mirasvitInjection $subject, $result)
    {
        $result['popularTitle'] = __("Last search");
        return $result;
    }
}
