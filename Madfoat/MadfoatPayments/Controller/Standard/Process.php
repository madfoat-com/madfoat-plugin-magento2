<?php

namespace Madfoat\MadfoatPayments\Controller\Standard;

class Process extends \Madfoat\MadfoatPayments\Controller\MadfoatPayments {

    public function execute() {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
