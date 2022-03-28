<?php
namespace Wcb\Store\Plugin\Block\User\Edit\Tab;

class Main
{
    /**
     * Get form HTML
     *
     * @return string
     */
    public function aroundGetFormHtml(
        \Magento\User\Block\User\Edit\Tab\Main $subject,
        \Closure $proceed
    )
    {
        $form = $subject->getForm();
        if (is_object($form)) {
            $fieldset = $form->addFieldset('admin_stores', ['legend' => __('Stores')]);
            $fieldset->addField(
                'stores',
                'select',
            [
                'name' => 'stores',
                'label' => __('Pickup Stores'),
                'title' => __('Pickup Stores'),
                //'values' => $this->deployedLocales->getOptionLocales(),
                'class' => 'select'
            ]
            );
            
         $subject->setForm($form);
        }

        return $proceed();
    }
}
