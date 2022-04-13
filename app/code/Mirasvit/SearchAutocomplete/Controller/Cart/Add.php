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

namespace Mirasvit\SearchAutocomplete\Controller\Cart;

use Magento\Catalog\Controller\Product\View\ViewInterface;
use Magento\Checkout\Model\Cart as CheckoutCart;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class Add extends Action implements ViewInterface
{
    private $cart;

    public function __construct(
        CheckoutCart $cart,
        Context      $context
    ) {
        parent::__construct($context);
        $this->cart = $cart;
    }

    public function execute()
    {
        $productID = (int)$this->getRequest()->getParam('id');

        try {
            $this->cart->addProduct($productID, [
                'qty' => 1,
            ]);

            $this->cart->save();
            $this->messageManager->addSuccessMessage((string) __('Product was successfully added to the cart'));
            $this->_redirect($this->getCartUrl());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage((string) $e->getMessage());
            $this->_redirect($this->_redirect->getRefererUrl());
        }
    }

    private function getCartUrl(): string
    {
        return (string)$this->_url->getUrl('checkout/cart', ['_secure' => true]);
    }
}
