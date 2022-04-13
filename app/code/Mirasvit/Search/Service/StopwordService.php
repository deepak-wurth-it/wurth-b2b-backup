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



namespace Mirasvit\Search\Service;


use Mirasvit\Search\Repository\StopwordRepository;

class StopwordService
{
    private $stopwordRepository;

    private $cloudService;

    public function __construct(
        StopwordRepository $stopwordRepository,
        CloudService $cloudService
    ) {
        $this->stopwordRepository = $stopwordRepository;
        $this->cloudService       = $cloudService;
    }

    public function isStopword(string $term, int $storeId): bool
    {
        $collection = $this->stopwordRepository->getCollection()
            ->addFieldToFilter('term', $term)
            ->addFieldToFilter('store_id', [0, $storeId]);

        return $collection->getSize() ? true : false;
    }

    /**
     * @param array|int $storeIds
     */
    public function import(string $file, $storeIds): \Generator
    {
        $result = [
            'stopwords'     => 0,
            'errors'        => 0,
            'error_message' => '',
        ];

        if (file_exists($file)) {
            $content = file_get_contents($file);
        } else {
            $content = $this->cloudService->get('search', 'stopword', $file);
        }
        if (!$content) {
            yield $result;
        } else {
            $stopwords = \Zend_Config_Yaml::decode($content);

            if (!is_array($storeIds)) {
                $storeIds = [$storeIds];
            }
            foreach ($storeIds as $storeId) {
                foreach ($stopwords as $stopword) {
                    try {
                        $stopword = $this->stopwordRepository->create()
                            ->setTerm((string)$stopword)
                            ->setStoreId($storeId);

                        $this->stopwordRepository->save($stopword);

                        $result['stopwords']++;
                    } catch (\Exception $e) {
                        $result['errors']++;

                        if (strripos($e->getMessage(), '(') === false) {
                            $result['error_message'] = $e->getMessage();
                        } else {
                            $result['error_message'] = substr($e->getMessage(), 0, strripos($e->getMessage(), '('));
                        }
                    }

                    yield $result;
                }
            }

            yield $result;
        }
    }
}
