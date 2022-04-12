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


namespace Mirasvit\Report\Api\Repository;


use Magento\Framework\Exception\NoSuchEntityException;
use Mirasvit\Report\Api\Data\EmailInterface;

interface EmailRepositoryInterface
{
    /**
     * @return \Mirasvit\Report\Model\ResourceModel\Email\Collection|EmailInterface[]
     */
    public function getCollection();

    /**
     * @param EmailInterface $email
     * @return EmailInterface
     */
    public function save(EmailInterface $email);

    /**
     * @param int $id
     * @return EmailInterface
     * @throws NoSuchEntityException
     */
    public function get($id);

    /**
     * @return EmailInterface
     */
    public function create();

    /**
     * @param EmailInterface $email
     * @return bool
     */
    public function delete(EmailInterface $email);

    /**
     * Get report blocks for emails.
     *
     * @return array
     */
    public function getReports();

//    /**
//     * @return array
//     */
//    public function getBlocks();
}