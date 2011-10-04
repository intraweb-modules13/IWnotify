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

    public function getAllNotifyFields($args) {
        // security check
        if (!SecurityUtil::checkPermission('IWnotify::', "::", ACCESS_READ)) {
            throw new Zikula_Exception_Forbidden();
        }
        $item = array();
        if (!isset($args['notifyId'])) {
            return $item;
        }
        $pntable = DBUtil::getTables();
        $where = "";
        $c = $pntable['IWnotify_fields_column'];

        $where = "$c[notifyId] = $args[notifyId]";
        $orderby = "$c[notifyFieldId]";

        // get the objects from the db
        $items = DBUtil::selectObjectArray('IWnotify_fields', $where, $orderby, '-1', '-1', 'notifyFieldId');

        // Check for an error with the database code, and if so set an appropriate
        if ($items === false)
            return LogUtil::registerError($this->__('Error! Could not load items.'));
        // Return the items


        return $items;
    }

    public function getNotifyValidationField($args) {
        // security check
        if (!SecurityUtil::checkPermission('IWnotify::', "::", ACCESS_READ)) {
            throw new Zikula_Exception_Forbidden();
        }
        $item = array();
        if (!isset($args['notifyId'])) {
            return $item;
        }
        $pntable = DBUtil::getTables();
        $where = "";
        $c = $pntable['IWnotify_fields_column'];

        $where = "$c[notifyId] = $args[notifyId] AND $c[notifyAuthField] = 1";

        // get the objects from the db
        $item = DBUtil::selectObjectArray('IWnotify_fields', $where, '', '-1', '-1', 'notifyId');

        // Check for an error with the database code, and if so set an appropriate
        if ($item === false)
            return LogUtil::registerError($this->__('Error! Could not load item.'));

        // Return the item
        return $item[$args['notifyId']];
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
            'notifyFailsMsg' => $args['notifyFailsMsg'],
            'notifyCreator' => UserUtil::getVar('uid'),
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

        // get inform
        $notify = ModUtil::apiFunc('IWnotify', 'user', 'getNotify', array('notifyId' => $args['notifyId']));

        if (!$notify) {
            return false;
        }

        // check if notify author is the current user
        if ($notify['notifyCreator'] != UserUtil::getVar('uid') && !SecurityUtil::checkPermission('IWnotify::', "::", ACCESS_ADMIN)) {
            throw new Zikula_Exception_Forbidden();
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
        // security check
        if (!SecurityUtil::checkPermission('IWnotify::', "::", ACCESS_ADD)) {
            throw new Zikula_Exception_Forbidden();
        }

        if (!$args['notifyId'] > 0 || !$args['notifyFieldId'] > 0) {
            return false;
        }

        // get inform
        $notify = ModUtil::apiFunc('IWnotify', 'user', 'getNotify', array('notifyId' => $args['notifyId']));

        if (!$notify) {
            return false;
        }

        // check if notify author is the current user
        if ($notify['notifyCreator'] != UserUtil::getVar('uid') && !SecurityUtil::checkPermission('IWnotify::', "::", ACCESS_ADMIN)) {
            throw new Zikula_Exception_Forbidden();
        }

        $item = array('notifyId' => $args['notifyId'],
            'notifyFieldId' => $args['notifyFieldId'],
            'notifyFieldContent' => $args['notifyFieldContent'],
            'notifyRecordId' => $args['notifyRecordId'],
        );

        if (!DBUtil::insertObject($item, 'IWnotify_fields_content', 'notifyFieldContentId')) {
            return LogUtil::registerError($this->__("L'intent de creació ha fallat."));
        }
        // Return the id of the newly created item to the calling process
        return $item['notifyFieldContentId'];
    }

    public function getAllUserNotifies($args) {
        // security check
        if (!SecurityUtil::checkPermission('IWnotify::', "::", ACCESS_ADD)) {
            throw new Zikula_Exception_Forbidden();
        }

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

    public function deleteFields($args) {
        // security check
        if (!SecurityUtil::checkPermission('IWnotify::', "::", ACCESS_ADD)) {
            throw new Zikula_Exception_Forbidden();
        }

        if (!$args['notifyId'] > 0) {
            return false;
        }

        // get inform
        $notify = ModUtil::apiFunc('IWnotify', 'user', 'getNotify', array('notifyId' => $args['notifyId']));

        if (!$notify) {
            return false;
        }

        // check if notify author is the current user
        if ($notify['notifyCreator'] != UserUtil::getVar('uid') && !SecurityUtil::checkPermission('IWnotify::', "::", ACCESS_ADMIN)) {
            throw new Zikula_Exception_Forbidden();
        }

        if (!DBUtil::deleteObjectByID('IWnotify_fields', $args['notifyId'], 'notifyId')) {
            return LogUtil::registerError($this->__('Error! Could not delete items.'));
        }
        return true;
    }

    public function deleteFieldsContent($args) {
        // security check
        if (!SecurityUtil::checkPermission('IWnotify::', "::", ACCESS_ADD)) {
            throw new Zikula_Exception_Forbidden();
        }

        if (!$args['notifyId'] > 0) {
            return false;
        }

        // get inform
        $notify = ModUtil::apiFunc('IWnotify', 'user', 'getNotify', array('notifyId' => $args['notifyId']));

        if (!$notify) {
            return false;
        }

        // check if notify author is the current user
        if ($notify['notifyCreator'] != UserUtil::getVar('uid') && !SecurityUtil::checkPermission('IWnotify::', "::", ACCESS_ADMIN)) {
            throw new Zikula_Exception_Forbidden();
        }

        if (!DBUtil::deleteObjectByID('IWnotify_fields_content', $args['notifyId'], 'notifyId')) {
            return LogUtil::registerError($this->__('Error! Could not delete items.'));
        }
        return true;
    }

    public function selectValidationField($args) {
        // security check
        if (!SecurityUtil::checkPermission('IWnotify::', "::", ACCESS_ADD)) {
            throw new Zikula_Exception_Forbidden();
        }

        if (!$args['notifyId'] > 0) {
            return false;
        }

        // get inform
        $notify = ModUtil::apiFunc('IWnotify', 'user', 'getNotify', array('notifyId' => $args['notifyId']));

        if (!$notify) {
            return false;
        }

        // check if notify author is the current user
        if ($notify['notifyCreator'] != UserUtil::getVar('uid') && !SecurityUtil::checkPermission('IWnotify::', "::", ACCESS_ADMIN)) {
            throw new Zikula_Exception_Forbidden();
        }

        $pntable = DBUtil::getTables();
        $c = $pntable['IWnotify_fields_column'];
        $where = "$c[notifyId] = $args[notifyId]";
        $item = array('notifyAuthField' => 0);
        if (!DBUTil::updateObject($item, 'IWnotify_fields', $where)) {
            return LogUtil::registerError($this->__('Error! Update attempt failed.'));
        }

        $where = "$c[notifyId] = $args[notifyId] AND $c[notifyFieldId] = $args[notifyAuthField]";
        $item = array('notifyAuthField' => 1);
        if (!DBUTil::updateObject($item, 'IWnotify_fields', $where)) {
            return LogUtil::registerError($this->__('Error! Update attempt failed.'));
        }

        return true;
    }

    public function editNotify($args) {
        // security check
        if (!SecurityUtil::checkPermission('IWnotify::', "::", ACCESS_ADD)) {
            throw new Zikula_Exception_Forbidden();
        }

        if (!$args['notifyId'] > 0) {
            return false;
        }

        // get inform
        $notify = ModUtil::apiFunc('IWnotify', 'user', 'getNotify', array('notifyId' => $args['notifyId']));

        if (!$notify) {
            return false;
        }

        // check if notify author is the current user
        if ($notify['notifyCreator'] != UserUtil::getVar('uid') && !SecurityUtil::checkPermission('IWnotify::', "::", ACCESS_ADMIN)) {
            throw new Zikula_Exception_Forbidden();
        }

        $pntable = DBUtil::getTables();
        $c = $pntable['IWnotify_definition_column'];
        $where = "$c[notifyId] = $args[notifyId]";

        if (!DBUTil::updateObject($args['items'], 'IWnotify_definition', $where)) {
            return LogUtil::registerError($this->__('Error! Update attempt failed.'));
        }

        return true;
    }

    public function deleteNotify($args) {
        // security check
        if (!SecurityUtil::checkPermission('IWnotify::', "::", ACCESS_ADD)) {
            throw new Zikula_Exception_Forbidden();
        }

        if (!$args['notifyId'] > 0) {
            return false;
        }

        // get inform
        $notify = ModUtil::apiFunc('IWnotify', 'user', 'getNotify', array('notifyId' => $args['notifyId']));

        if (!$notify) {
            return false;
        }

        // check if notify author is the current user
        if ($notify['notifyCreator'] != UserUtil::getVar('uid') && !SecurityUtil::checkPermission('IWnotify::', "::", ACCESS_ADMIN)) {
            throw new Zikula_Exception_Forbidden();
        }

        if (!ModUtil::apiFunc('IWnotify', 'user', 'deleteFields', array('notifyId' => $args['notifyId']))) {
            return LogUtil::registerError($this->__('Error! Could not delete items.'));
        }

        if (!ModUtil::apiFunc('IWnotify', 'user', 'deleteFieldsContent', array('notifyId' => $args['notifyId']))) {
            return LogUtil::registerError($this->__('Error! Could not delete items.'));
        }

        if (!DBUtil::deleteObjectByID('IWnotify_definition', $args['notifyId'], 'notifyId')) {
            return LogUtil::registerError($this->__('Error! Could not delete items.'));
        }

        return true;
    }

    public function getNotifyValidateValue($args) {
        // security check
        if (!SecurityUtil::checkPermission('IWnotify::', "::", ACCESS_READ)) {
            throw new Zikula_Exception_Forbidden();
        }
        $item = array();
        if (!isset($args['notifyId']) || !$args['notifyId'] > 0 || !isset($args['notifyFieldContent']) || $args['notifyFieldContent'] == '' || !isset($args['notifyFieldId']) || !$args['notifyFieldId'] > 0) {
            return $item;
        }
        $pntable = DBUtil::getTables();
        $where = "";
        $c = $pntable['IWnotify_fields_content_column'];

        $where = "$c[notifyId] = $args[notifyId] AND $c[notifyFieldContent] = '$args[notifyFieldContent]' AND $c[notifyFieldId] = $args[notifyFieldId]";

        // get the objects from the db
        $items = DBUtil::selectObjectArray('IWnotify_fields_content', $where, '', '-1', '-1', 'notifyId');

        // Check for an error with the database code, and if so set an appropriate
        if ($items === false)
            return LogUtil::registerError($this->__('Error! Could not load items.'));
        // Return the items


        return $items;
    }

    public function getNotifyValues($args) {
        // security check
        if (!SecurityUtil::checkPermission('IWnotify::', "::", ACCESS_READ)) {
            throw new Zikula_Exception_Forbidden();
        }
        $item = array();
        if (!isset($args['notifyId']) || !$args['notifyId'] > 0 || !isset($args['notifyRecordId']) || !$args['notifyRecordId'] > 0) {
            return $item;
        }

        $pntable = DBUtil::getTables();
        $c = $pntable['IWnotify_fields_content_column'];

        $where = "$c[notifyId] = $args[notifyId] AND $c[notifyRecordId] = $args[notifyRecordId]";

        // get the objects from the db
        $items = DBUtil::selectObjectArray('IWnotify_fields_content', $where, '', '-1', '-1', 'notifyFieldId');

        // Check for an error with the database code, and if so set an appropriate
        if ($items === false)
            return LogUtil::registerError($this->__('Error! Could not load items.'));
        // Return the items

        return $items;
    }

}