<?php

class IWnotify_Controller_Admin extends Zikula_AbstractController {

    public function postInitialize() {
        $this->view->setCaching(false);
    }

    /**
     * Redirect to the init function for users
     * @author:     Albert Pérez Monfort (aperezm@xtec.cat)
     */
    public function main() {
        // Security check
        if (!SecurityUtil::checkPermission('IWnotify::', "::", ACCESS_READ)) {
            throw new Zikula_Exception_Forbidden();
        }

        // Redirect to the main site for the user
        return System::redirect(ModUtil::url('IWnotify', 'admin', 'config'));
    }

    public function config() {
        // Security check
        if (!SecurityUtil::checkPermission('IWnotify::', "::", ACCESS_READ)) {
            throw new Zikula_Exception_Forbidden();
        }
        return $this->view->fetch('IWnotify_admin_config.htm');
    }

    public function viewLogs() {
        // Security check
        if (!SecurityUtil::checkPermission('IWnotify::', "::", ACCESS_READ)) {
            throw new Zikula_Exception_Forbidden();
        }

        $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
        $logsTable = ModUtil::apiFunc('IWmain', 'user', 'getLogs', array('sv' => $sv));

        return $this->view->fetch('IWnotify_admin_viewLogs.htm');
    }

}