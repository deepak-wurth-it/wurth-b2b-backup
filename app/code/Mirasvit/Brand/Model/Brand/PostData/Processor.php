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

namespace Mirasvit\Brand\Model\Brand\PostData;

use Mirasvit\Brand\Api\Data\PostData\ProcessorInterface;

class Processor implements ProcessorInterface
{
    /** @var ProcessorInterface[] */
    private $processors;

    public function __construct(array $processors = [])
    {
        $this->processors = $processors;
    }

    public function preparePostData(array $data): array
    {
        foreach ($this->processors as $processor) {
            $data = $processor->preparePostData($data);
        }

        return $data;
    }
}
