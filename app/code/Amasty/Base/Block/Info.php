<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Base
*/


namespace Amasty\Base\Block;

use Amasty\Base\Model\ModuleInfoProvider;
use Amasty\Base\Block\Adminhtml\System\Config\SysInfo\DownloadButton;
use Magento\Backend\Block\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Config\Block\System\Config\Form\Fieldset;
use Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory;
use Magento\Framework\App\DeploymentConfig\Reader;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\State;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\Helper\Js;

class Info extends Fieldset
{
    /**
     * @var CollectionFactory
     */
    private $cronFactory;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var Field|null
     */
    protected $fieldRenderer;

    /**
     * @var ModuleInfoProvider
     */
    private $moduleInfoProvider;

    public function __construct(
        Context $context,
        Session $authSession,
        Js $jsHelper,
        CollectionFactory $cronFactory,
        DirectoryList $directoryList,
        Reader $reader,
        ResourceConnection $resourceConnection,
        ProductMetadataInterface $productMetadata,
        ModuleInfoProvider $moduleInfoProvider,
        array $data = []
    ) {
        parent::__construct($context, $authSession, $jsHelper, $data);
        $this->cronFactory = $cronFactory;
        $this->directoryList = $directoryList;
        $this->resourceConnection = $resourceConnection;
        $this->productMetadata = $productMetadata;
        $this->reader = $reader;
        $this->moduleInfoProvider = $moduleInfoProvider;
    }

    /**
     * Render fieldset html
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $html = $this->_getHeaderHtml($element);

        $html .= $this->getMagentoMode($element);
        $html .= $this->getMagentoPathInfo($element);
        $html .= $this->getOwnerInfo($element);
        $html .= $this->getSystemTime($element);
        $html .= $this->getCronInfo($element);
        $html .= $this->getDownloadButtonHtml($element);

        $html .= $this->_getFooterHtml($element);

        return $html;
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface
     */
    private function getFieldRenderer()
    {
        if (empty($this->fieldRenderer)) {
            $this->fieldRenderer = $this->_layout->createBlock(
                Field::class
            );
        }

        return $this->fieldRenderer;
    }

    /**
     * @param AbstractElement $fieldset
     *
     * @return string
     */
    private function getMagentoMode($fieldset)
    {
        $label = __('Magento Mode');

        $env = $this->reader->load();
        $mode = isset($env[State::PARAM_MODE]) ? $env[State::PARAM_MODE] : '';

        return $this->getFieldHtml($fieldset, 'magento_mode', $label, ucfirst($mode));
    }

    /**
     * @param AbstractElement $fieldset
     *
     * @return string
     */
    private function getMagentoPathInfo($fieldset)
    {
        $label = __('Magento Path');
        $path = $this->directoryList->getRoot();

        return $this->getFieldHtml($fieldset, 'magento_path', $label, $path);
    }

    /**
     * @param AbstractElement $fieldset
     *
     * @return string
     */
    private function getOwnerInfo($fieldset)
    {
        $serverUser = __('Unknown');
        if (function_exists('get_current_user')) {
            $serverUser = get_current_user();
        }

        return $this->getFieldHtml(
            $fieldset,
            'magento_user',
            __('Server User'),
            $serverUser
        );
    }

    /**
     * @param AbstractElement $fieldset
     *
     * @return string
     */
    private function getSystemTime($fieldset)
    {
        if (version_compare($this->productMetadata->getVersion(), '2.2', '>=')) {
            $time = $this->resourceConnection->getConnection()->fetchOne('select now()');
        } else {
            $time = $this->_localeDate->date()->format('H:i:s');
        }
        return $this->getFieldHtml($fieldset, 'mysql_current_date_time', __('Current Time'), $time);
    }

    /**
     * @param AbstractElement $fieldset
     *
     * @return string
     */
    private function getCronInfo($fieldset)
    {
        $crontabCollection = $this->cronFactory->create();
        $crontabCollection->setOrder('schedule_id')->setPageSize(5);

        if ($crontabCollection->count() === 0) {
            $value = '<div class="red">';
            $value .= __('No cron jobs found') . '</div>';
            if (!$this->moduleInfoProvider->isOriginMarketplace()) {
                $value .=
                    '<a target="_blank"
                  href="https://support.amasty.com/index.php?/Knowledgebase/Article/View/72/24/magento-cron">' .
                    __('Learn more') .
                    '</a>';
            }
        } else {
            $value = '<table>';
            foreach ($crontabCollection as $crontabRow) {
                $value .=
                    '<tr>' .
                    '<td>' . $crontabRow['job_code'] . '</td>' .
                    '<td>' . $crontabRow['status'] . '</td>' .
                    '<td>' . $crontabRow['created_at'] . '</td>' .
                    '</tr>';
            }
            $value .= '</table>';
        }

        $label = __('Cron (Last 5)');

        return $this->getFieldHtml($fieldset, 'cron_configuration', $label, $value);
    }

    private function getDownloadButtonHtml(AbstractElement $fieldset): string
    {
        $button = $this->getLayout()->createBlock(DownloadButton::class);

        return $this->getFieldHtml(
            $fieldset,
            DownloadButton::ELEMENT_ID,
            __('Extensions data'),
            $button->toHtml()
        );
    }

    /**
     * @param AbstractElement $fieldset
     * @param string $fieldName
     * @param string $label
     * @param string $value
     *
     * @return string
     */
    protected function getFieldHtml($fieldset, $fieldName, $label = '', $value = '')
    {
        $field = $fieldset->addField($fieldName, 'label', [
            'name'  => 'dummy',
            'label' => $label,
            'after_element_html' => $value,
        ])->setRenderer($this->getFieldRenderer());

        return $field->toHtml();
    }
}
