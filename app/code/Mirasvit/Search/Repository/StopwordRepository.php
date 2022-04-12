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


namespace Mirasvit\Search\Repository;

use Magento\Framework\Exception\NoSuchEntityException;
use Mirasvit\Search\Api\Data\StopwordInterface;
use Mirasvit\Search\Api\Data\StopwordInterfaceFactory;
use Mirasvit\Search\Model\ResourceModel\Stopword\CollectionFactory as StopwordCollectionFactory;

class StopwordRepository
{
    /**
     * @var StopwordInterfaceFactory
     */
    private $stopwordFactory;

    /**
     * @var StopwordCollectionFactory
     */
    private $stopwordCollectionFactory;

    public function __construct(
        StopwordInterfaceFactory $stopwordFactory,
        StopwordCollectionFactory $stopwordCollectionFactory
    ) {
        $this->stopwordFactory = $stopwordFactory;
        $this->stopwordCollectionFactory = $stopwordCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getCollection()
    {
        return $this->stopwordCollectionFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        return $this->stopwordFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function get(int $id)
    {
        /** @var \Mirasvit\Search\Model\Stopword $stopword */
        $stopword = $this->create();
        $stopword->load($id);

        if (!$stopword->getId()) {
            throw NoSuchEntityException::singleField('stopword_id', $id);
        }

        return $stopword;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(StopwordInterface $stopword)
    {
        /** @var \Mirasvit\Search\Model\Stopword $stopword */
        $stopword->delete();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function save(StopwordInterface $stopword)
    {
        /** @var \Mirasvit\Search\Model\Stopword $stopword */

        $stopword->setTerm(trim(strtolower($stopword->getTerm())));

        $stopword->save();

        return $this;
    }
}
