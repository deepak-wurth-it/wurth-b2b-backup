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



namespace Mirasvit\Search\Api\Data;

use Mirasvit\Search\Model\ScoreRule\Rule;

interface ScoreRuleInterface
{
    const TABLE_NAME       = 'mst_search_score_rule';
    const INDEX_TABLE_NAME = 'mst_search_score_rule_index';

    const ID = 'rule_id';

    const TITLE       = 'title';
    const IS_ACTIVE   = 'is_active';
    const STATUS      = 'status';
    const ACTIVE_FROM = 'active_from';
    const ACTIVE_TO   = 'active_to';
    const STORE_IDS   = 'store_ids';

    const SCORE_FACTOR = 'score_factor';

    const CONDITIONS_SERIALIZED      = 'conditions_serialized';
    const POST_CONDITIONS_SERIALIZED = 'post_conditions_serialized';

    /**
     * @return string
     */
    public function getId();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setTitle($value);

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @param bool $value
     *
     * @return $this
     */
    public function setIsActive($value);

    /**
     * @return bool
     */
    public function isActive();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setStatus($value);

    /**
     * @return string
     */
    public function getStatus();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setActiveFrom($value);

    /**
     * @return string
     */
    public function getActiveFrom();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setActiveTo($value);

    /**
     * @return string
     */
    public function getActiveTo();

    /**
     * @param array $value
     *
     * @return $this
     */
    public function setStoreIds($value);

    /**
     * @return array
     */
    public function getStoreIds();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setScoreFactor($value);

    /**
     * @return string
     */
    public function getScoreFactor();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setConditionsSerialized($value);

    /**
     * @return string
     */
    public function getConditionsSerialized();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setPostConditionsSerialized($value);

    /**
     * @return string
     */
    public function getPostConditionsSerialized();

    /**
     * @return Rule
     */
    public function getRule();
}
