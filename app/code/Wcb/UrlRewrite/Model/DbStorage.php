<?php
namespace Wcb\UrlRewrite\Model;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

class DbStorage extends \Magento\UrlRewrite\Model\Storage\DbStorage {

    protected function doReplace(array $urls) : array{
        $this->deleteOldUrls($urls);
        $data = [];
        $storeId_requestPaths = [];
        foreach ($urls as $url) {
            $storeId = $url->getStoreId();
            $requestPath = $url->getRequestPath();
            $sql = "SELECT * FROM url_rewrite where store_id = $storeId and request_path = '$requestPath'";
            $exists = $this->connection->fetchOne($sql);
            if ($exists) continue;
            $storeId_requestPaths[] = $storeId . '-' . $requestPath;
            $data[] = $url->toArray();
        }

        // Remove duplication data;
        $n = count($storeId_requestPaths);
        for ($i = 0; $i < $n - 1; $i++) {
            for ($j = $i + 1; $j < $n; $j++) {
                if ($storeId_requestPaths[$i] == $storeId_requestPaths[$j]) {
                    unset($data[$j]);
                }
            }
        }
        try {
            $this->insertMultiple($data);
        } catch (\Magento\Framework\Exception\AlreadyExistsException $e) {
            /** @var \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[] $urlConflicted */
            $urlConflicted = [];
            foreach ($urls as $url) {
                $urlFound = $this->doFindOneByData(
                    [
                        UrlRewriteData::REQUEST_PATH => $url->getRequestPath(),
                        UrlRewriteData::STORE_ID => $url->getStoreId(),
                    ]
                );
                if (isset($urlFound[UrlRewriteData::URL_REWRITE_ID])) {
                    $urlConflicted[$urlFound[UrlRewriteData::URL_REWRITE_ID]] = $url->toArray();
                }
            }
            if ($urlConflicted) {
                throw new \Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException(
                    __('URL key for specified store already exists.'),
                    $e,
                    $e->getCode(),
                    $urlConflicted
                );
            } else {
                throw $e->getPrevious() ?: $e;
            }
        }

        return $urls;
    }

}

