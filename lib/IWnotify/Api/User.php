<?php

class IWnotify_Api_User extends Zikula_AbstractApi {

    public function getlinks($args) {
        $links = array();
        if (SecurityUtil::checkPermission('IWnotify::', "::", ACCESS_READ)) {
            $links[] = array('url' => ModUtil::url('IWnotify', 'user', 'viewNotifies'), 'text' => $this->__('Show the notify informs'), 'class' => 'z-icon-es-view');
        }
        if (SecurityUtil::checkPermission('IWnotify::', "::", ACCESS_ADD)) {
            $links[] = array('url' => ModUtil::url('IWnotify', 'user', 'newNotify'), 'text' => $this->__('Create notify inform'), 'class' => 'z-icon-es-new');
            $links[] = array('url' => ModUtil::url('IWnotify', 'user', 'myNotifies'), 'text' => $this->__('My notify informs'), 'class' => 'z-icon-es-view');
        }
        return $links;
    }

    public function getNotify($args) {
        // security check
        if (!SecurityUtil::checkPermission('IWnotify::', "::", ACCESS_READ)) {
            throw new Zikula_Exception_Forbidden();
        }
        $item = array();
        if (!isset($args['notifyId'])) {
            return $item;
        }
        $item = DBUtil::selectObjectByID('IWnotify_definition', $args['notifyId'], 'notifyId');
        // Check for an error with the database code, and if so set an appropriate
        // error message and return
        if ($item === false) {
            return LogUtil::registerError($this->__("No s'han pogut carregar els registres."));
        }

        // Return the items
        return $item;
    }

    public function createNotify($args) {
        // security check
        if (!SecurityUtil::checkPermission('IWnotify::', "::", ACCESS_ADD)) {
            throw new Zikula_Exception_Forbidden();
        }

        $item = array('notifyTitle' => $args['notifyTitle'],
            'notifyDescription' => $args['notifyDescription'],
            'notifyOpenDate' => $args['notifyOpenDate'],
            'notifyCloseDate' => $args['notifyCloseDate'],
            'notifyType' => $args['notifyType'],
            'notifyCloseMsg' => $args['notifyCloseMsg'],
            'notifyReturnUrl' => $args['notifyReturnUrl'],
            'notifyOnlyOnceVisit' => $args['notifyOnlyOnceVisit'],
            'notifyFailsMsg' => $args['notifyFailsMsg'],
        );

        if (!DBUtil::insertObject($item, 'IWnotify_definition', 'notifyId')) {
            return LogUtil::registerError($this->__("L'intent de creació ha fallat."));
        }
        // Return the id of the newly created item to the calling process
        return $item['notifyId'];
    }

    public function createField($args) {
        // security check
        if (!SecurityUtil::checkPermission('IWnotify::', "::", ACCESS_ADD)) {
            throw new Zikula_Exception_Forbidden();
        }

        if (!$args['notifyId'] > 0) {
            return false;
        }

        $item = array('notifyId' => $args['notifyId'],
            'notifyFieldName' => $args['notifyFieldName'],
        );

        if (!DBUtil::insertObject($item, 'IWnotify_fields', 'notifyFieldId')) {
            return LogUtil::registerError($this->__("L'intent de creació ha fallat."));
        }
        // Return the id of the newly created item to the calling process
        return $item['notifyFieldId'];
    }

    public function fillField($args) {
        return true;
    }

    public function getAllUserNotifies($args) {

        $pntable = DBUtil::getTables();
        $where = "";
        $c = $pntable['IWnotify_definition_column'];

        $where = "$c[notifyCreator] = " . UserUtil::getVar('uid');
        $orderby = "$c[notifyTitle]";
        
        // get the objects from the db
        $items = DBUtil::selectObjectArray('IWnotify_definition', $where, $orderby, '-1', '-1', 'notifyId');
        
        // Check for an error with the database code, and if so set an appropriate
        if ($items === false)
            return LogUtil::registerError($this->__('Error! Could not load items.'));
        // Return the items


        return $items;
    }

}