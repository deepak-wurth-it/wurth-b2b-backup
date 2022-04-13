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



namespace Mirasvit\Report\Ui\Email\Form;

use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Mirasvit\Report\Api\Data\EmailInterface;
use Mirasvit\Report\Api\Repository\EmailRepositoryInterface;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * DataProvider constructor.
     * @param EmailRepositoryInterface $emailRepository
     * @param ArrayManager $arrayManager
     * @param TimezoneInterface $timezone
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        EmailRepositoryInterface $emailRepository,
        ArrayManager $arrayManager,
        TimezoneInterface $timezone,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->collection   = $emailRepository->getCollection();
        $this->arrayManager = $arrayManager;
        $this->timezone     = $timezone;

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        $meta = parent::getMeta();
        $meta = $this->arrayManager->set(
            'general/children/subject/arguments/data/config/addafter',
            $meta,
            '[' . $this->timezone->date()->format("M d, Y H:i") . ']'
        );

        return $meta;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $result = [];

        foreach ($this->collection as $email) {
            $data = [
                EmailInterface::ID                  => $email->getId(),
                EmailInterface::TITLE               => $email->getTitle(),
                EmailInterface::IS_ACTIVE           => $email->getIsActive(),
                EmailInterface::IS_ATTACH_ENABLED   => $email->getIsAttachEnabled(),
                EmailInterface::SUBJECT             => $email->getSubject(),
                EmailInterface::RECIPIENT           => $email->getRecipient(),
                EmailInterface::SCHEDULE            => $email->getSchedule(),
                EmailInterface::BLOCKS              => $email->getBlocks(),
            ];

            $result[$email->getId()] = $data;
        }

        return $result;
    }
}
