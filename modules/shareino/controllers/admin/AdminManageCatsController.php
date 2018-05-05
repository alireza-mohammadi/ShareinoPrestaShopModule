<?php

class AdminManageCatsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->module = 'shareino';
        $this->context = Context::getContext();
        $this->context->controller = $this;
        $this->bootstrap = true;
        $this->lang = false;

        parent::__construct();
    }

    public function renderForm()
    {
        parent::renderForm();
    }

    public function initContent()
    {
        parent::initContent();
        $this->processConfiguration();
        $this->context->smarty;
    }

    public function createTemplate($tpl_name)
    {
        if (file_exists($this->getTemplatePath() . $tpl_name) && $this->viewAccess()) {

            return $this->context->smarty->createTemplate($this->getTemplatePath() . 'category.tpl', $this->context->smarty);
        }
        return parent::createTemplate($tpl_name);
    }

    public function processConfiguration()
    {
        // Assign module dir
        $this->context->smarty->assign('module_dir', __PS_BASE_URI__ . 'modules/');

        // Assign Store's categories
        $tree = new HelperTreeCategoriesCore('associated-categories-tree', 'دسته بندی های فروشگاه');
        $tree->setUseCheckBox(true);
        $tree->setInputName('storeCategory');
        $this->context->smarty->assign('storeCategoryBox', $tree->render());

        // Assign controller's url
        $link = new LinkCore();
        $action = $link->getAdminLink('AdminManageCats');
        $this->context->smarty->assign('url', $action);

        $this->context->smarty->assign(array(
            'token' => Tools::getAdminTokenLite('AdminManageCats'),
            'category' => Configuration::get('SHAREINO_SELECT_CATEGORY')
        ));

        $this->context->smarty->assign('list', $this->renderList());
    }

    function ajaxProcessSelectedCategories()
    {
        $category = json_encode(Tools::getValue('categories'), true);
        $result = Configuration::updateValue('SHAREINO_SELECT_CATEGORY', $category);

        if ($result) {
            echo json_encode(array('status' => true, 'message' => 'ذخیره سازی با موفقیت انجام شد.'));
        } else {
            echo json_encode(array('status' => false, 'message' => 'خطایی در ذخیره‌سازی وجود دارد.'));
        }

    }
}
