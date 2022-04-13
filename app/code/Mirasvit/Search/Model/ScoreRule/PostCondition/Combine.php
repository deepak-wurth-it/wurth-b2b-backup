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



namespace Mirasvit\Search\Model\ScoreRule\PostCondition;

use Magento\Rule\Model\Condition\Context;

class Combine extends \Magento\Rule\Model\Condition\Combine
{
    /**
     * @var RequestCondition
     */
    private $requestCondition;

    /**
     * Combine constructor.
     * @param RequestCondition $requestCondition
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        RequestCondition $requestCondition,
        Context $context,
        array $data = []
    ) {
        $data['prefix'] = 'post_conditions';

        $this->requestCondition = $requestCondition;

        parent::__construct($context, $data);

        $this->setType('Mirasvit\Search\Model\ScoreRule\PostCondition\Combine');
    }

    /**
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $searchRequestAttributes = [];

        foreach ($this->requestCondition->getAttributeOption() as $code => $label) {
            $searchRequestAttributes[] = [
                'value' => RequestCondition::class . '|' . $code,
                'label' => $label,
            ];
        }


        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive(
            $conditions,
            [
                [
                    'value' => 'Mirasvit\Search\Model\ScoreRule\PostCondition\Combine',
                    'label' => __('Conditions Combination'),
                ],
                ['label' => __('Search Request'), 'value' => $searchRequestAttributes],
            ]
        );
        return $conditions;
    }
}
