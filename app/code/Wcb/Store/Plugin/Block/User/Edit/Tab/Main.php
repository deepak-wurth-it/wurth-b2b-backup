<?php
namespace Wcb\Store\Plugin\Block\User\Edit\Tab;

class Main
{
   
   
   public function __construct(
        \Wcb\Store\Block\Store $store,
        \Wcb\Store\Model\StoreOption $storeOption
    ) {
        $this->store = $store;
        $this->storeOption = $storeOption;
    } 
   
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
        $data =  $this->storeOption->getAllOptions();

        
        if (is_object($form) && $data) {
            $fieldset = $form->addFieldset('admin_user_store', ['legend' => __('Stores')]);
            $fieldset->addField(
                'pickup_store_id',
                'select',
            [
                'name' => 'pickup_store_id',
                'label' => __('Pickup Stores'),
                'title' => __('Pickup Stores'),
                'values' => $this->storeOption->getAllOptions(),
                'class' => 'select'
            ]
            );
            
         $subject->setForm($form);
        }

        return $proceed();
    }
}

