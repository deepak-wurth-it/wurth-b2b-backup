<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Base
*/


namespace Amasty\Base\Debug\System;

class AmastyFormatter extends \Monolog\Formatter\LineFormatter
{
    /**
     * @param array $record
     *
     * @return string
     */
    public function format(array $record): string
    {
        $output = $this->format;
        $output = str_replace('%datetime%', date('H:i d/m/Y'), $output);
        $output = str_replace('%message%', $record['message'], $output);
        return $output;
    }
}
