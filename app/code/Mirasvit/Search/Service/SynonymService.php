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

use Magento\Search\Model\SynonymAnalyzer;
use Magento\Search\Model\SynonymGroupFactory;
use Magento\Search\Model\SynonymGroupRepository;

class SynonymService
{
    private $synonymRepository;

    private $synonymFactory;

    private $synonymAnalyzer;

    private $cloudService;

    public function __construct(
        SynonymGroupRepository $synonymRepository,
        SynonymGroupFactory $synonymFactory,
        SynonymAnalyzer $synonymAnalyzer,
        CloudService $cloudService
    ) {
        $this->synonymRepository = $synonymRepository;
        $this->synonymFactory    = $synonymFactory;
        $this->synonymAnalyzer   = $synonymAnalyzer;
        $this->cloudService      = $cloudService;
    }

    /**
     * {@inheritdoc}
     */
    public function getSynonyms(array $terms, int $storeId): array
    {
        $result = [];

        foreach ($terms as $term) {
            $synonyms      = $this->synonymAnalyzer->getSynonymsForPhrase($term);
            if (empty($synonyms)){
                continue;
            }

            if ($term != implode ('',$synonyms[0])) {
                $result[$term] = $synonyms[0];
            }
        }

        return $result;
    }

    /**
     * @param array|int $storeIds
     */
    public function import(string $file, $storeIds): \Generator
    {
        $result = [
            'synonyms' => 0,
            'total'    => 0,
            'errors'   => 0,
            'message'  => '',
        ];

        if (file_exists($file)) {
            $content = file_get_contents($file);
        } else {
            $content = $this->cloudService->get('search', 'synonym', $file);
        }

        if (!$content) {
            yield false;
        } else {
            if (strlen($content) > 10000 && php_sapi_name() != "cli") {
                $result['errors']++;
                $result['message'] = __('File is too large. Please use CLI interface (bin/magento mirasvit:search:synonym --file EN.yaml --store 1)');
                yield $result;
            } else {
                $synonyms = \Zend_Config_Yaml::decode($content);

                if (!is_array($storeIds)) {
                    $storeIds = [$storeIds];
                }

                foreach ($storeIds as $storeId) {
                    $result['total'] = count($synonyms);
                    foreach ($synonyms as $synonym) {
                        try {
                            $group = implode(',', [$synonym['term'], $synonym['synonyms']]);
                            $model = $this->synonymFactory->create()
                                ->setSynonymGroup($group)
                                ->setStoreId(1)
                                ->setWebsiteId($storeId);

                            $this->synonymRepository->save($model);

                            $result['synonyms']++;
                        } catch (\Exception $e) {
                            $result['errors']++;

                            $result['message'] = $e->getMessage();
                        }
                        yield $result;
                    }
                }
            }
        }
    }
}
