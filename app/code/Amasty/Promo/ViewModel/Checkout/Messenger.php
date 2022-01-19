<?php
declare(strict_types=1);

namespace Amasty\Promo\ViewModel\Checkout;

use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\Serialize\Serializer\Json;

class Messenger implements ArgumentInterface
{
    const MESSAGE_GROUP = 'ammessenger';

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var Json
     */
    private $jsonSerializer;

    public function __construct(
        ManagerInterface $messageManager,
        Json $jsonSerializer
    ) {
        $this->messageManager = $messageManager;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * @return bool
     */
    public function isShowErrorMessage(): bool
    {
        if ($this->messageManager->getMessages(false, self::MESSAGE_GROUP)->getLastAddedMessage()) {
            return true;
        }

        return false;
    }

    /**
     * @return false|string
     */
    public function getErrorMessages()
    {
        $messagesStack = [];
        /** @var \Magento\Framework\Message\Collection $messagesCollection */
        $messagesCollection = $this->messageManager->getMessages(false, self::MESSAGE_GROUP);
        foreach ($messagesCollection->getItems() as $message) {
            $messagesStack[] = $message->getText();
        }
        $messagesCollection->clear();

        return $this->jsonSerializer->serialize($messagesStack);
    }
}
