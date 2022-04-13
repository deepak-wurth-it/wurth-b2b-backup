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



namespace Mirasvit\SearchSphinx\Model;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteFactory;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Search\Api\Data\IndexInterface;
use Mirasvit\Search\Repository\IndexRepository;
use Mirasvit\Search\Service\IndexService;
use Mirasvit\SearchMysql\SearchAdapter\Index\IndexNameResolver;
use Mirasvit\SearchSphinx\Helper\Data as Helper;
use Mirasvit\SearchSphinx\SphinxQL\Connection;
use Mirasvit\SearchSphinx\SphinxQL\SphinxQL;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Engine
{
    private $config;

    private $helper;

    private $indexRepository;

    private $indexService;

    private $storeManager;

    private $directory;

    private $basePath;

    private $configFilePath;

    private $absConfigFilePath;

    private $host;

    private $port;

    private $connection;

    private $lastStatusCheck     = 0;

    private $productAttributeCollectionFactory;

    private $availableAttributes = [];

    private $searchdCommand;

    private $indexNameResolver;

    public function __construct(
        Filesystem $fs,
        WriteFactory $writeFactory,
        Config $config,
        Helper $helper,
        IndexRepository $indexRepository,
        IndexService $indexService,
        IndexNameResolver $indexNameResolver,
        StoreManagerInterface $storeManager,
        AttributeCollectionFactory $productAttributeCollectionFactory
    ) {
        $this->config                            = $config;
        $this->helper                            = $helper;
        $this->indexRepository                   = $indexRepository;
        $this->indexService                      = $indexService;
        $this->indexNameResolver                 = $indexNameResolver;
        $this->storeManager                      = $storeManager;
        $this->productAttributeCollectionFactory = $productAttributeCollectionFactory;

        $this->directory = $fs->getDirectoryWrite(DirectoryList::VAR_DIR);

        if ($this->config->getCustomBasePath()) {
            $this->basePath  = rtrim($this->config->getCustomBasePath());
            $this->directory = $writeFactory->create('/');
        } else {
            $this->basePath = $fs->getDirectoryRead(DirectoryList::VAR_DIR)->getRelativePath('sphinx');
        }

        $this->configFilePath = $this->basePath . DIRECTORY_SEPARATOR . 'sphinx.conf';

        $this->absConfigFilePath = $this->directory->getAbsolutePath($this->configFilePath);

        $this->host = $this->config->getHost();
        $this->port = $this->config->getPort();

        // check all paths
        foreach ($this->config->getBinPath() as $binPath) {
            $this->searchdCommand = $binPath;
            try {
                if ($this->isAvailable()) {
                    break;
                }
            } catch (\LogicException $e) {
                $message = $e->getMessage();
            } catch (\Exception $e) {
                $message = $e->getMessage();
            }
        }

        $this->connection = new Connection();
        $this->connection->setParams([
            'host' => $this->host,
            'port' => $this->port,
        ]);

        if (file_exists($this->absConfigFilePath . '.attr')) {
            $this->availableAttributes = \Zend_Json::decode(file_get_contents($this->absConfigFilePath . '.attr'));
        }
    }

    public function isAvailable(string &$output = ''): bool
    {
        /** mp uncomment start 
        return true;
        mp uncomment end **/
        if ($this->config->isSameServer() == false) {
            return true;
        }

        $proceed = false;
        $path    = $this->directory->getAbsolutePath($this->configFilePath);
        if ($this->configFilePath && file_exists($path)) {
            $command = "$this->searchdCommand --status -c $path ";
            $exec    = $this->helper->exec($command);
            $proceed = strpos($exec['data'], 'uptime') !== false;
        }

        if (!$proceed) {
            $command = "$this->searchdCommand --config fake.conf 2>&1";
            $exec    = $this->helper->exec($command);
            $proceed = strpos($exec['data'], 'failed to parse config file') !== false;
        }

        if ($proceed) {
            $command     = "ps aux | grep searchd | awk '{print $13;}'";
            $exec        = $this->helper->exec($command);
            $configFiles = [];
            foreach (explode(PHP_EOL, $exec['data']) as $value) {
                if (strripos($value, 'sphinx.conf') !== false) {
                    $configFiles[] = $value;
                }
            }
            $configFiles = array_unique($configFiles);
            $ports       = [];
            foreach ($configFiles as $configFile) {
                if ($configFile == $this->directory->getAbsolutePath($this->configFilePath)) {
                    continue;
                }

                try {
                    $config = file_get_contents($configFile);
                    foreach (explode(PHP_EOL, $config) as $row) {
                        if (strripos($row, 'listen') !== false) {
                            foreach (explode(':', $row) as $data) {
                                if (is_numeric($data)) {
                                    $ports[] = (int)$data;
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {
                }
            }

            if (in_array($this->port, $ports)) {
                $output .= __('Searchd already using the following config file(s) : ' . PHP_EOL . implode('-' . PHP_EOL, $configFiles) . PHP_EOL);
                $output .= __('Please make sure you use different sphinx ports for all of these instances.');

                return false;
            }

            return true;
        } else {
            $output .= __('Searchd not found at %1', $this->searchdCommand);

            return false;
        }
    }

    public function saveDocuments(IndexInterface $index, string $indexName, array $documents): void
    {
        $instance = $this->indexRepository->getInstance($index);

        foreach ($documents as $id => $document) {
            if (empty($id)) {
                continue;
            }

            $query = $this->getQuery()->replace()
                ->into($indexName)
                ->value('id', $id);

            $doc = [];

            foreach ($document as $attr => $value) {
                if (isset($this->availableAttributes[$indexName])
                    && !in_array($attr, $this->availableAttributes[$indexName])
                ) {
                    $attr = 'options';
                }

                if (is_scalar($value)) {
                    if (isset($doc[$attr])) {
                        $doc[$attr] .= ' ' . $value;
                    } else {
                        $doc[$attr] = $value . '';
                    }
                }
            }

            if (isset($document['autocomplete'])) {
                $doc['autocomplete'] = \Zend_Json::encode($document['autocomplete']);
            }

            foreach ($doc as $attr => $value) {
                $query->value('`' . $attr . '`', $value);
            }

            try {
                $query->execute();
            } catch (\Exception $e) {
                if (strpos($e->getMessage(), 'no such index') !== false) {
                    if ($this->config->isSameServer()) {
                        throw new \LogicException((string)__('Please reset and restart your sphinx daemon to search by new index'));
                    } else {
                        throw new \LogicException((string)__('Please generate a new configuration file'
                            . ' and place it to your remote server to search by new index'));
                    }
                } elseif (strpos($e->getMessage(), 'unknown column') !== false) {
                    throw new \LogicException((string)__('Please reset and restart your sphinx daemon to apply changes to search index'));
                } else {
                    throw new \LogicException('Please reset and restart your sphinx daemon. Current error is: ' . $e->getMessage());
                }
            }
        }
    }

    public function getQuery(): SphinxQL
    {
        return new SphinxQL($this->getConnection());
    }

    public function status(string &$output = ''): bool
    {
        if ($this->config->isSameServer() == false) {
            return true;
        }

        if (!$this->isAvailable($output)) {
            return false;
        }

        $output = '';

        $command = "$this->searchdCommand --config $this->absConfigFilePath --status";
        try {
            $exec = $this->helper->exec($command);
        } catch (\LogicException $e) {
            return true;
        }

        $output .= $exec['data'] . PHP_EOL;

        $command = "ps aux | grep searchd | awk '{print $2,$9,$11,$12,$13;}'";
        $exec    = $this->helper->exec($command);

        $output .= $exec['data'] . PHP_EOL;

        if (strpos($output, 'failed to connect to') !== false) {
            return false;
        }

        if (strpos($output, 'searchd status') === false) {
            return false;
        }

        if (strpos($output, 'uptime:') === false) {
            return false;
        }

        return true;
    }

    public function start(string &$output = ''): bool
    {
        if ($this->config->isSameServer() == false) {
            return true;
        }

        if (!$this->isAvailable($output)) {
            return false;
        }

        $this->makeConfig();

        $command = "$this->searchdCommand --config $this->absConfigFilePath";
        $exec    = $this->helper->exec($command);

        $output .= $exec['data'];

        if ($exec['status'] === 0) {
            return true;
        } else {
            return false;
        }
    }

    public function makeConfig(): string
    {
        if (!$this->directory->isExist($this->basePath)) {
            $this->directory->create($this->basePath);
            $this->directory->changePermissions($this->basePath, 0777);
        }

        $jsonData = [];

        $sphinxData = [
            'time'          => date('d.m.Y H:i:s'),
            'host'          => $this->host,
            'port'          => $this->port,
            'fallback_port' => $this->port - 1,
            'logdir'        => $this->directory->getAbsolutePath($this->basePath),
            'sphinxdir'     => $this->directory->getAbsolutePath($this->basePath),
            'indexes'       => '',
            'localdir'      => dirname(dirname(__FILE__)),
            'custom'        => $this->config->getAdditionalSearchdConfig(),
        ];

        $sphinxTemplate = $this->config->getSphinxConfigurationTemplate();
        $indexTemplate  = $this->config->getSphinxIndexConfigurationTemplate();

        foreach ($this->indexRepository->getCollection() as $index) {
            $instance = $this->indexRepository->getInstance($index);

            foreach (array_keys($this->storeManager->getStores()) as $storeId) {
                $indexName    = $this->indexNameResolver->getIndexNameByStoreId($instance->getIdentifier(), $storeId);
                $charsetTable = $this->config->getCustomCharsetTable();
                if (!$charsetTable) {
                    $charsetTable = $this->config->getDefaultCharsetTable();
                }

                $data = [
                    'name'          => $indexName,
                    'min_word_len'  => 1,
                    'path'          => $this->directory->getAbsolutePath($this->basePath) . '/' . $indexName,
                    'custom'        => $this->config->getAdditionalIndexConfig(),
                    'charset_table' => $charsetTable,
                ];

                $jsonAttributes = [];
                $attributes     = [];
                foreach (array_keys($instance->getAttributes()) as $attribute) {
                    $attributes[]     = "    rt_field = $attribute";
                    $jsonAttributes[] = $attribute;

                    if (count($attributes) > 250) {
                        break;
                    }
                }

                foreach (['options', 'status', 'visibility'] as $extra) {
                    $attributes[]     = "    rt_field = $extra";
                    $jsonAttributes[] = $extra;
                }

                $data['attributes'] = implode(PHP_EOL, $attributes);

                $sphinxData['indexes'] .= $this->helper->filterTemplate($indexTemplate, $data);

                $jsonData[$indexName] = $jsonAttributes;
            }
        }

        $config = $this->helper->filterTemplate($sphinxTemplate, $sphinxData);

        if ($this->directory->isWritable($this->basePath)) {
            $this->directory->writeFile($this->configFilePath, $config);
            $this->directory->writeFile($this->configFilePath . '.attr', json_encode($jsonData));
        } else {
            if ($this->directory->isExist($this->configFilePath)) {
                throw new \LogicException((string)__('File %1 is not writable', $this->configFilePath));
            } else {
                throw new \LogicException((string)__('Directory %1 is not writable', $this->basePath));
            }
        }

        return $this->directory->getAbsolutePath($this->configFilePath);
    }

    public function deleteDocuments(IndexInterface $index, string $indexName, array $documents): void
    {
        if (!$this->status() && $this->config->isAutoRestartAllowed()) {
            $this->start();
        }

        foreach ($documents as $document) {
            $this->getQuery()
                ->delete()
                ->from($indexName)
                ->where('id', '=', $document)
                ->execute();
        }
    }

    public function cleanIndex(string $indexName): void
    {
        if (!$this->status() && $this->config->isAutoRestartAllowed()) {
            $this->start();
        }

        $this->getQuery()
            ->delete()
            ->from($indexName)
            ->where('id', '>', 0)
            ->execute();
    }

    public function restart(string &$output = ''): bool
    {
        if ($this->config->isSameServer() == false) {
            return true;
        }

        if (!$this->isAvailable($output)) {
            return false;
        }

        $this->stop($output);

        return $this->start($output);
    }

    public function stop(string &$output = ''): bool
    {
        if ($this->config->isSameServer() == false) {
            return true;
        }

        if (!$this->isAvailable($output)) {
            return false;
        }

        // first attempt (normal)
        $command = $this->searchdCommand . ' --config ' . $this->absConfigFilePath . ' --stopwait';
        $exec    = $this->helper->exec($command);
        $output  .= $exec['data'];

        // second attempt (forced)
        $find = "ps aux | grep searchd | grep $this->absConfigFilePath  | awk '{print $2}'";
        $pids = $this->helper->exec($find);
        foreach (explode(PHP_EOL, $pids['data']) as $id) {
            $command = "kill -9 $id";
            $this->helper->exec($command);
        }

        if ($exec['status'] === 0) {
            return true;
        } else {
            return false;
        }
    }

    public function reset(string &$output = ''): bool
    {
        if ($this->config->isSameServer() == false) {
            return true;
        }

        $this->stop($output);

        $path = $this->directory->getAbsolutePath($this->basePath);

        if (!preg_match('/\/var\/sphinx[\/]*$/', $path)) {
            $output = __('Please correct your Custom Base Path, it should end with "var/sphinx"');
            return false;
        }

        $command = "rm -rf {$path}/*";
        $exec    = $this->helper->exec($command);
        $output  .= $exec['data'];

        return true;
    }

    private function getConnection(): Connection
    {
        if (microtime(true) - $this->lastStatusCheck < 20) {
            return $this->connection;
        }
        $this->lastStatusCheck = microtime(true);
        if (!$this->status() && $this->config->isAutoRestartAllowed()) {
            $this->start();
        }

        try {
            $this->connection->getConnection();
            $this->connection->ping();
        } catch (\Exception $e) {
            try {
                $this->connection->close();
            } catch (\Exception $e) {
            }
            $attempts = 0;
            $success  = false;
            while ($attempts < 20 && $success == false) {
                try {
                    $this->connection->connect();
                    $this->connection->ping();
                    $success = true;
                } catch (\Exception $e) {
                    $attempts++;
                }
            }
        }

        $this->connection->ping();

        return $this->connection;
    }

    public function getAbsConfigFilePath() : string
    {
        return $this->absConfigFilePath;
    }
}
