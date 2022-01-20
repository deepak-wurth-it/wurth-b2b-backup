<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Base
*/


namespace Amasty\Base\Test\Unit\Controller\Adminhtml\Notification;

use Amasty\Base\Controller\Adminhtml\Notification\Frequency;
use Amasty\Base\Test\Unit\Traits;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class FrequencyTest
 *
 * @see Frequency
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class FrequencyTest extends TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @covers Frequency::execute
     * @dataProvider executeDataProvider
     */
    public function testExecute($action, $callError, $callIncrease, $callDecrease)
    {
        $messageManager = $this->createMock(ManagerInterface::class);
        $redirect = $this->createMock(RedirectInterface::class);
        $resultRedirect = $this->createPartialMock(
            Redirect::class,
            []
        );
        $resultRedirectFactory = $this->createPartialMock(
            RedirectFactory::class,
            ['create']
        );
        $request = $this->createPartialMock(
            Http::class,
            []
        );
        $request->setParam('action', $action);
        $controller = $this->createPartialMock(
            Frequency::class,
            ['increaseFrequency', 'decreaseFrequency', 'getRequest']
        );

        $messageManager->expects($callError)->method('addErrorMessage');
        $controller->expects($callDecrease)->method('decreaseFrequency');
        $controller->expects($callIncrease)->method('increaseFrequency');
        $controller->expects($this->any())->method('getRequest')->willReturn($request);
        $resultRedirectFactory->expects($this->any())->method('create')->willReturn($resultRedirect);

        $this->setProperty($controller, 'messageManager', $messageManager);
        $this->setProperty($controller, 'resultRedirectFactory', $resultRedirectFactory);
        $this->setProperty($controller, '_redirect', $redirect);
        $controller->execute();
    }

    /**
     * Data provider for execute test
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            ['less', $this->never(), $this->once(), $this->never()],
            ['more', $this->never(), $this->never(), $this->once()],
            ['test', $this->once(), $this->never(), $this->never()],
        ];
    }
}
