<?php

class ShareinoStatusModuleFrontController extends ModuleFrontController
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

        if (Module::isInstalled('shareino')) {
            echo Tools::jsonEncode(['status' => true], true);
        }
    }
}
