<?php

require_once _PS_MODULE_DIR_ . 'shareino/classes/dokmeAuth.php';

class ShareinoCategoryModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        $data = array('status' => true);

        $auth = new dokmeAuth();
        if ($auth->auth()) {
            $categories = CategoryCore::getNestedCategories(
                null,
                $this->context->language->id
            );
            $data['data'] = $this->treeCategories($categories);
            echo Tools::jsonEncode($data);
        }

        $this->setTemplate();
    }

    protected function treeCategories($categories, $data = array())
    {
        foreach ($categories as $category) {
            $data[] = array(
                'id' => $category['id_category'],
                'parent_id' => $category['id_parent'],
                'name' => $category['name']
            );

            if (!empty($category['children'])) {
                return $this->treeCategories($category['children'], $data);
            }
        }

        return $data;
    }
}