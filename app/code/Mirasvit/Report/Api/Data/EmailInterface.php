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



namespace Mirasvit\Report\Api\Data;

interface EmailInterface
{
    const TABLE_NAME = 'mst_report_email';

    const ID = 'email_id';
    const TITLE = 'title';
    const IS_ACTIVE = 'is_active';
    const IS_ATTACH_ENABLED = 'is_attach_enabled';
    const SUBJECT = 'subject';
    const RECIPIENT = 'recipient';
    const SCHEDULE = 'schedule';
    const BLOCKS_SERIALIZED = 'blocks_serialized';
    const BLOCKS = 'blocks';
    const LAST_SENT_AT = 'last_sent_at';

    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @param string $input
     * @return $this
     */
    public function setTitle($input);

    /**
     * @return int
     */
    public function getIsActive();

    /**
     * @param int $input
     * @return $this
     */
    public function setIsActive($input);

     /**
      * @return int
      */
    public function getIsAttachEnabled();

    /**
     * @param int $input
     * @return $this
     */
    public function setIsAttachEnabled($input);

    /**
     * @return string
     */
    public function getSubject();

    /**
     * @param string $input
     * @return $this
     */
    public function setSubject($input);

    /**
     * @return string
     */
    public function getRecipient();

    /**
     * @param string $input
     * @return $this
     */
    public function setRecipient($input);

    /**
     * @return string
     */
    public function getSchedule();

    /**
     * @param string $input
     * @return $this
     */
    public function setSchedule($input);

    /**
     * @return string
     */
    public function getLastSentAt();

    /**
     * @param string $input
     * @return $this
     */
    public function setLastSentAt($input);

    /**
     * @return string
     */
    public function getBlocksSerialized();

    /**
     * @param string $input
     * @return $this
     */
    public function setBlocksSerialized($input);

    /**
     * @return array
     */
    public function getBlocks();


    /**
     * @param string $key
     * @return mixed|array
     */
    public function getData($key = null);

    /**
     * @param string|array $key
     * @param string|int|array $value
     * @return $this
     */
    public function setData($key, $value = null);

    /**
     * @param array $data
     * @return $this
     */
    public function addData(array $data);
}
