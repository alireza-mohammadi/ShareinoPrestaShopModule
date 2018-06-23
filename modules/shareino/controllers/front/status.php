<?php

class ShareinoStatusModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        if (Module::isInstalled('shareino')) {
            echo Tools::jsonEncode(['status' => true], true);
        }

        $this->setTemplate();
    }
}
