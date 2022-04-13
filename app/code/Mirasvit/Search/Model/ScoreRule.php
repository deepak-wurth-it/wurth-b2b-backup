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



namespace Mirasvit\Search\Model;

use Mirasvit\Search\Api\Data\ScoreRuleInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Mirasvit\Search\Model\ScoreRule\Rule;
use Mirasvit\Search\Model\ScoreRule\RuleFactory;

class ScoreRule extends AbstractModel implements ScoreRuleInterface
{
    /**
     * @var Rule
     */
    private $rule;

    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    /**
     * ScoreRule constructor.
     * @param RuleFactory $ruleFactory
     * @param Context $context
     * @param Registry $registry
     */
    public function __construct(
        RuleFactory $ruleFactory,
        Context $context,
        Registry $registry
    ) {
        $this->ruleFactory = $ruleFactory;

        parent::__construct($context, $registry);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(\Mirasvit\Search\Model\ResourceModel\ScoreRule::class);
    }

    /**
     * @return Rule
     */
    public function getRule()
    {
        if (!$this->rule) {
            $this->rule = $this->ruleFactory->create()
                ->setData(self::CONDITIONS_SERIALIZED, $this->getConditionsSerialized())
                ->setData(self::POST_CONDITIONS_SERIALIZED, $this->getPostConditionsSerialized());
        }

        return $this->rule;
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
    public function setTitle($value)
    {
        return $this->setData(self::TITLE, $value);
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
    public function setIsActive($value)
    {
        return $this->setData(self::IS_ACTIVE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function isActive()
    {
        return $this->getData(self::IS_ACTIVE);
    }

    /**
     * {@inheritdoc}
     */
    public function setStatus($value)
    {
        return $this->setData(self::STATUS, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * {@inheritdoc}
     */
    public function setActiveFrom($value)
    {
        return $this->setData(self::ACTIVE_FROM, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveFrom()
    {
        return $this->getData(self::ACTIVE_FROM);
    }

    /**
     * {@inheritdoc}
     */
    public function setActiveTo($value)
    {
        return $this->setData(self::ACTIVE_TO, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveTo()
    {
        return $this->getData(self::ACTIVE_TO);
    }

    /**
     * {@inheritdoc}
     */
    public function setStoreIds($value)
    {
        if (is_array($value)) {
            $value = implode(',', array_filter($value));
        }

        return $this->setData(self::STORE_IDS, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreIds()
    {
        return explode(',', $this->getData(self::STORE_IDS));
    }

    /**
     * {@inheritdoc}
     */
    public function setScoreFactor($value)
    {
        return $this->setData(self::SCORE_FACTOR, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getScoreFactor()
    {
        return $this->getData(self::SCORE_FACTOR);
    }

    /**
     * {@inheritdoc}
     */
    public function setConditionsSerialized($value)
    {
        return $this->setData(self::CONDITIONS_SERIALIZED, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getConditionsSerialized()
    {
        return $this->getData(self::CONDITIONS_SERIALIZED);
    }

    /**
     * {@inheritdoc}
     */
    public function setPostConditionsSerialized($value)
    {
        return $this->setData(self::POST_CONDITIONS_SERIALIZED, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getPostConditionsSerialized()
    {
        return $this->getData(self::POST_CONDITIONS_SERIALIZED);
    }
}
