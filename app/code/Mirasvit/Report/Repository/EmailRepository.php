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



namespace Mirasvit\Report\Repository;

use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\ObjectManagerInterface;
use Mirasvit\Report\Api\Data\EmailInterface;
use Mirasvit\Report\Api\Repository\Email\BlockRepositoryInterface;
use Mirasvit\Report\Api\Repository\EmailRepositoryInterface;
use Mirasvit\Report\Model\EmailFactory;
use Mirasvit\Report\Model\ResourceModel\Email\CollectionFactory as CollectionFactory;

class EmailRepository implements EmailRepositoryInterface
{
    /**
     * @var EmailFactory
     */
    private $factory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var array
     */
    private $repositoryPool = [];


    /**
     * EmailRepository constructor.
     * @param EmailFactory $emailFactory
     * @param CollectionFactory $collectionFactory
     * @param EntityManager $entityManager
     * @param ObjectManagerInterface $objectManager
     * @param array $repositoryPool
     */
    public function __construct(
        EmailFactory $emailFactory,
        CollectionFactory $collectionFactory,
        EntityManager $entityManager,
        ObjectManagerInterface $objectManager,
        array $repositoryPool = []
    ) {
        $this->factory           = $emailFactory;
        $this->collectionFactory = $collectionFactory;
        $this->entityManager     = $entityManager;
        $this->objectManager     = $objectManager;
        $this->repositoryPool    = $repositoryPool;
    }

    /**
     * {@inheritdoc}
     */
    public function getCollection()
    {
        return $this->collectionFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        return $this->factory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        $model = $this->create();

        $this->entityManager->load($model, $id);

        return $model->getId() ? $model : false;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(EmailInterface $email)
    {
        $this->entityManager->delete($email);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function save(EmailInterface $email)
    {
        /** @var \Mirasvit\Report\Model\Email $email */

        $email->setData(EmailInterface::BLOCKS_SERIALIZED, \Zend_Json::encode($email->getData(EmailInterface::BLOCKS)));

        return $this->entityManager->save($email);
    }

    /**
     * {@inheritdoc}
     */
    public function getReports()
    {
        $reports = [];

        foreach ($this->repositoryPool as $repositoryClass) {
            /** @var BlockRepositoryInterface $repository */
            $repository = $this->objectManager->get($repositoryClass);

            foreach ($repository->getBlocks() as $identifier => $block) {
                $reports[] = [
                    'value'      => $identifier,
                    'label'      => $block,
                    'repository' => $repository,
                ];
            }
        }

        return $reports;
    }
}
