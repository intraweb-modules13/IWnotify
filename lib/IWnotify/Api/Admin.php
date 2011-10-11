<?php

class IWnotify_Api_Admin extends Zikula_AbstractApi {

    public function getlinks($args) {
        $links = array();
        if (SecurityUtil::checkPermission('IWnotify::', "::", ACCESS_ADMIN)) {
            $links[] = array('url' => ModUtil::url('IWnotify', 'admin', 'config'), 'text' => $this->__('Configure'), 'class' => 'z-icon-es-conf');
            $links[] = array('url' => ModUtil::url('IWnotify', 'admin', 'viewLogs'), 'text' => $this->__('View logs'), 'class' => 'z-icon-es-view');
        }
        return $links;
    }

}