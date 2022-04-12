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

namespace Mirasvit\SearchAutocomplete\Controller\Ajax;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Mirasvit\SearchAutocomplete\Model\Result;

class Suggest extends Action
{
    private $result;

    public function __construct(
        Result $result,
        Context $context
    ) {
        $this->result = $result;
        parent::__construct($context);
    }

    public function execute()
    {
        if (empty($this->_request->getParam('q'))) {
            return $this->getResponse()->setRedirect('/');
        }

        $this->result->init();

        /** @var \Magento\Framework\App\Response\Http $response */
        $response = $this->getResponse();
        $response->setHeader('cache-control', 'max-age=86400, public, s-maxage=86400', true);
        $response->representJson(\Zend_Json::encode(
            $this->result->toArray()
        ));
    }
}
