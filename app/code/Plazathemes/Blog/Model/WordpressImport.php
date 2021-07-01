<?php
/**
 * Copyright © 2015 PlazaThemes.com. All rights reserved.

 * @author PlazaThemes Team <contact@plazathemes.com>
 */

namespace Plazathemes\Blog\Model;

/**
 * Wordpress import model
 */
class WordpressImport extends AbstractImport
{
    protected $_requiredFields = ['dbname', 'uname', 'pwd', 'dbhost', 'prefix', 'store_id'];

    public function execute()
    {
        $con = $this->_connect = mysqli_connect(
            $this->getData('dbhost'),
            $this->getData('uname'),
            $this->getData('pwd'),
            $this->getData('dbname')
        );

        if (mysqli_connect_errno()) {
            throw new \Exception("Failed connect to wordpress database", 1);
        }

        $_pref = mysqli_real_escape_string($con, $this->getData('prefix'));

        $categories = [];
        $oldCategories = [];

        /* Import categories */
        $sql = 'SELECT
                    t.term_id as old_id,
                    t.name as title,
                    t.slug as identifier,
                    tt.parent as parent_id
                FROM '.$_pref.'terms t
                LEFT JOIN '.$_pref.'term_taxonomy tt on t.term_id = tt.term_id
                WHERE tt.taxonomy = "category" AND t.slug <> "uncategorized"';

        $result = $this->_mysqliQuery($sql);
        while ($data = mysqli_fetch_assoc($result)) {
            /* Prepare category data */
            $data['store_id'] = $this->getStoreId();
            $data['is_active'] = 1;
            $data['position'] = 0;
            $data['path'] = 0;
            $data['identifier'] = trim(strtolower($data['identifier']));
            if (strlen($data['identifier']) == 1) {
                $data['identifier'] .= $data['identifier'];
            }

            $category = $this->_categoryFactory->create();
            try {
                /* Initial saving */
                $category->setData($data)->save();
                $this->_importedCategoriesCount++;
                $categories[$category->getId()] = $category;
                $oldCategories[$category->getOldId()] = $category;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                unset($category);
                $this->_skippedCategories[] = $data['title'];
            }
        }

        /* Reindexing parent categories */
        foreach ($categories as $ct) {
            if ($oldParentId = $ct->getData('parent_id')) {
                if (isset($oldCategories[$oldParentId])) {
                    $ct->setPath(
                        $parentId = $oldCategories[$oldParentId]->getId()
                    );

                    echo 'set path: '.$parentId.'<br/>';
                }
            }
        }

        for ($i = 0; $i < 4; $i++) {
            $changed = false;
            foreach ($categories as $ct) {
                if ($ct->getPath()) {
                    $parentId = explode('/', $ct->getPath())[0];
                    $pt = $categories[$parentId];
                    if ($pt->getPath()) {
                        $ct->setPath($pt->getPath() . '/'. $ct->getPath());
                        $changed = true;
                    }
                }
            }

            if (!$changed) {
                break;
            }
        }
        /* end*/

        foreach($categories as $ct) {
            /* Final saving */
            $ct->save();
        }

        /* Import posts */
        $sql = 'SELECT * FROM '.$_pref.'posts WHERE `post_type` = "post"';
        $result = $this->_mysqliQuery($sql);

        while ($data = mysqli_fetch_assoc($result)) {

            /* find post categories*/
            $postCategories = [];

            $sql = 'SELECT tt.term_id as term_id FROM '.$_pref.'term_relationships tr
                    LEFT JOIN '.$_pref.'term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                    WHERE tr.`object_id` = "'.$data['ID'].'"';

            $result2 = $this->_mysqliQuery($sql);
            while ($data2 = mysqli_fetch_assoc($result2)) {
                $oldTermId = $data2['term_id'];
                if (isset($oldCategories[$oldTermId])) {
                    $postCategories[] = $oldCategories[$oldTermId]->getId();
                }
            }

            /* Prepare post data */
            $creationTime = strtotime($data['post_date_gmt']);
            $data = [
                'store_id' => $this->getStoreId(),
                'title' => $data['post_title'],
                'meta_keywords' => '',
                'meta_description' => '',
                'identifier' => $data['post_name'],
                'content_heading' => '',
                'content' => str_replace('<!--more-->', '<!-- pagebreak -->', $data['post_content']),
                'creation_time' => $creationTime,
                'update_time' => strtotime($data['post_modified_gmt']),
                'publish_time' => $creationTime,
                'is_active' => (int)($data['post_status'] == 'publish'),
                'categories' => $postCategories,
            ];
            $data['identifier'] = trim(strtolower($data['identifier']));
            if (strlen($data['identifier']) == 1) {
                $data['identifier'] .= $data['identifier'];
            }

            $post = $this->_postFactory->create();
            try {
                /* Post saving */
                $post->setData($data)->save();
                $this->_importedPostsCount++;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->_skippedPosts[] = $data['title'];
            }

            unset($post);
        }
        /* end */

        mysqli_close($con);
    }

}

