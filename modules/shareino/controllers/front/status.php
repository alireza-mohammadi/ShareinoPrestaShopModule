<?php

class ShareinoStatusModuleFrontController extends ModuleFrontController
{

    public function initContent()
    {
        parent::initContent();

        if (Module::isInstalled('shareino')) {
            echo json_encode(['status' => true], true);
        }

        $this->setTemplate('status.tpl');
    }

}
