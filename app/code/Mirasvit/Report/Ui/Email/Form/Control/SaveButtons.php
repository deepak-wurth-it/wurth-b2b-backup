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



namespace Mirasvit\Report\Ui\Email\Form\Control;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class SaveButtons extends GenericButton implements ButtonProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        return [
            'label'      => __('Save'),
            'class'      => 'primary',
            'class_name' => 'Magento\Backend\Block\Widget\Button\SplitButton',
            'sort_order' => 10,
            'options'    => [
                [
                    'id'             => 'save_continue',
                    'label'          => __('Save & Continue'),
                    'default'        => true,
                    'data_attribute' => [
                        'mage-init' => [
                            'buttonAdapter' => [
                                'actions' => [
                                    [
                                        'targetName' => 'report_email_form.report_email_form',
                                        'actionName' => 'save',
                                        'params'     => [true, [
                                            'back' => 'continue',
                                        ]],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'id'             => 'save_send',
                    'label'          => __('Save & Send'),
                    'data_attribute' => [
                        'mage-init' => [
                            'buttonAdapter' => [
                                'actions' => [
                                    [
                                        'targetName' => 'report_email_form.report_email_form',
                                        'actionName' => 'save',
                                        'params'     => [true, [
                                            'back' => 'send',
                                        ]],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    //    /**
    //     * {@inheritdoc}
    //     */
    //    public function getButtonData()
    //    {
    //        return [
    //            'label'          => __('Save'),
    //            'class'          => 'save primary',
    //            'data_attribute' => [
    //                'mage-init' => ['button' => ['event' => 'save']],
    //                'form-role' => 'save',
    //            ],
    //            'sort_order'     => 90,
    //        ];
    //    }
}
