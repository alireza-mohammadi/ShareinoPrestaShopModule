<?php

require_once _PS_MODULE_DIR_ . 'shareino/classes/dokmeAuth.php';

class ShareinoCategoryModuleFrontController extends ModuleFrontController
{
    public function __construct($response = array())
    {
        parent::__construct($response);
        $this->display_header = false;
        $this->display_header_javascript = false;
        $this->display_footer = false;
    }

    public function initContent()
    {
        parent::initContent();

        $data = array('status' => true);

        $auth = new dokmeAuth();
        if ($auth->auth()) {

            $nestedCategories = CategoryCore::getNestedCategories(
                null,
                $this->context->language->id
            );
            $categories = array();
            $this->treeCategories($nestedCategories, $categories);
            $data['data'] = $categories;
            echo Tools::jsonEncode($data);

        }
    }

    protected function treeCategories($nestedCategories, &$categories)
    {
        foreach ($nestedCategories as $category) {
            $categories[] = array(
                'id' => $category['id_category'],
                'parent_id' => $category['id_parent'],
                'name' => $category['name']
            );
            if (!empty($category['children'])) {
                $this->treeCategories($category['children'], $categories);
            }
        }
    }
}