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


declare(strict_types=1);

namespace Mirasvit\SearchElastic\SearchAdapter;

use Elasticsearch\ClientBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Manager
{
    private $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function status(string &$output)
    {
        $client = $this->getClient();

        if (!$client->ping()) {
            return false;
        }

        $indices = $client->cat()->indices();
        if (is_array($indices)) {
            usort($indices, function ($a, $b) {
                return strcmp($a['index'], $b['index']);
            });

            $output .= "docs count | index name" . PHP_EOL;
            $output .= "-----------|----------------------" . PHP_EOL;

            foreach ($indices as $info) {
                $count  = (string)$info['docs.count'];
                $output .= str_repeat(' ', 10 - strlen($count)) . $count . " | " . $info['index'] . PHP_EOL;
            }
        }
        $output .= PHP_EOL;

        $stats = $client->info();

        $output .= $this->prettyPrint($stats);

        $output .= $this->prettyPrint($client->indices()->stats());

        try {
            $mapping = $client->indices()->getMapping([
                'index' => '*',
            ]);
            $output  .= $this->prettyPrint($mapping);

            $settings = $client->indices()->getSettings([
                'index' => '*',
            ]);
            $output   .= $this->prettyPrint($settings);
        } catch (\Exception $e) {
            $output .= $e->getMessage();
        }

        return true;
    }

    public function getESConfig(): array
    {
        $options = [
            'hostname'   => $this->scopeConfig->getValue('catalog/search/elasticsearch7_server_hostname'),
            'port'       => $this->scopeConfig->getValue('catalog/search/elasticsearch7_server_port'),
            'index'      => $this->scopeConfig->getValue('catalog/search/elasticsearch7_index_prefix'),
            'enableAuth' => $this->scopeConfig->getValue('catalog/search/elasticsearch7_enable_auth'),
            'username'   => $this->scopeConfig->getValue('catalog/search/elasticsearch7_username'),
            'password'   => $this->scopeConfig->getValue('catalog/search/elasticsearch7_password'),
            'timeout'    => $this->scopeConfig->getValue('catalog/search/elasticsearch7_server_timeout'),
        ];

        $hostname = preg_replace('/http[s]?:\/\//i', '', $options['hostname']);
        // @codingStandardsIgnoreStart
        $protocol = parse_url($options['hostname'], PHP_URL_SCHEME);
        // @codingStandardsIgnoreEnd
        if (!$protocol) {
            $protocol = 'http';
        }

        $authString = '';
        if (!empty($options['enableAuth']) && (int)$options['enableAuth'] === 1) {
            $authString = "{$options['username']}:{$options['password']}@";
        }

        $portString = '';
        if (!empty($options['port'])) {
            $portString = ':' . $options['port'];
        }

        $host = $protocol . '://' . $authString . $hostname . $portString;

        $options['hosts'] = [$host];

        return $options;
    }

    public function reset(string &$output = ''): bool
    {
        $client = $this->getClient();

        if (!$client->ping()) {
            return false;
        }

        if ($client->cat()->indices()) {
            $indices = $client->cat()->indices();
            foreach ($indices as $index) {
                try {
                    $this->getClient()->indices()->close([
                        'index' => $index['index'],
                    ]);
                } catch (\Exception $e) {
                    $output .= $e->getMessage();
                }

                try {
                    $this->getClient()->indices()->delete([
                        'index' => $index['index'],
                    ]);
                } catch (\Exception $e) {
                    $output .= $e->getMessage();
                }
            }
        }

        $output .= $this->prettyPrint($client->indices()->delete([
            'index' => '*',
        ]));

        return true;
    }

    public function resetStore(string &$output = ''): bool
    {
        $indexPrefix = $this->getESConfig()['index'];
        $client      = $this->getClient();

        if (!$client->ping()) {
            return false;
        }

        if ($client->cat()->indices()) {
            $indices = $client->cat()->indices();
            foreach ($indices as $index) {
                if (!preg_match('/^' . $indexPrefix . '_[^_]{1}.+/', $index['index'])) {
                    continue;
                }

                try {
                    $this->getClient()->indices()->close([
                        'index' => $index['index'],
                    ]);
                } catch (\Exception $e) {
                    $output .= $e->getMessage();
                }

                try {
                    $this->getClient()->indices()->delete([
                        'index' => $index['index'],
                    ]);
                } catch (\Exception $e) {
                    $output .= $e->getMessage();
                }
            }
        }

        return true;
    }

    private function getClient(): \Elasticsearch\Client
    {
        $esConfig = $this->getESConfig();

        return ClientBuilder::fromConfig($esConfig, true);
    }

    private function prettyPrint(array $array, int $offset = 0): string
    {
        $str = "";
        if (is_array($array)) {
            foreach ($array as $key => $val) {
                if (is_array($val)) {
                    $str .= str_repeat(' ', $offset) . $key . ': ' . PHP_EOL . $this->prettyPrint($val, $offset + 5);
                } else {
                    $str .= str_repeat(' ', $offset) . $key . ': ' . $val . PHP_EOL;
                }
            }
        }
        $str .= '</ul>';

        return $str;
    }
}
