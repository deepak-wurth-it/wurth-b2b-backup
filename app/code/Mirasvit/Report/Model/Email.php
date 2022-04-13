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
 * @package   mirasvit/module-report
 * @version   1.3.112
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Report\Model;

use Magento\Framework\Model\AbstractModel;
use Mirasvit\Report\Api\Data\EmailInterface;

class Email extends AbstractModel implements EmailInterface
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Email::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * {@inheritdoc}
     */
    public function setTitle($input)
    {
        return $this->setData(self::TITLE, $input);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsActive()
    {
        return $this->getData(self::IS_ACTIVE);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsActive($input)
    {
        return $this->setData(self::IS_ACTIVE, $input);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsAttachEnabled()
    {
        return $this->getData(self::IS_ATTACH_ENABLED);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsAttachEnabled($input)
    {
        return $this->setData(self::IS_ATTACH_ENABLED, $input);
    }

    /**
     * {@inheritdoc}
     */
    public function getSubject()
    {
        return $this->getData(self::SUBJECT);
    }

    /**
     * {@inheritdoc}
     */
    public function setSubject($input)
    {
        return $this->setData(self::SUBJECT, $input);
    }

    /**
     * {@inheritdoc}
     */
    public function getRecipient()
    {
        return $this->getData(self::RECIPIENT);
    }

    /**
     * {@inheritdoc}
     */
    public function setRecipient($input)
    {
        return $this->setData(self::RECIPIENT, $input);
    }

    /**
     * {@inheritdoc}
     */
    public function getSchedule()
    {
        return $this->getData(self::SCHEDULE);
    }

    /**
     * {@inheritdoc}
     */
    public function setSchedule($input)
    {
        return $this->setData(self::SCHEDULE, $input);
    }

    /**
     * {@inheritdoc}
     */
    public function getLastSentAt()
    {
        return $this->getData(self::LAST_SENT_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setLastSentAt($input)
    {
        return $this->setData(self::LAST_SENT_AT, $input);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlocksSerialized()
    {
        return $this->getData(self::BLOCKS_SERIALIZED);
    }

    /**
     * {@inheritdoc}
     */
    public function setBlocksSerialized($input)
    {
        return $this->setData(self::BLOCKS_SERIALIZED, $input);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlocks()
    {
        try {
            return \Zend_Json::decode($this->getBlocksSerialized());
        } catch (\Exception $e) {
            return [];
        }
    }
}
