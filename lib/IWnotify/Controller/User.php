<?php

class IWnotify_Controller_User extends Zikula_AbstractController {

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
        return System::redirect(ModUtil::url('IWnotify', 'user', 'viewNotifies'));
    }

    /**
     * show the available notify informs
     * @author:     Albert Pérez Monfort (aperezm@xtec.cat)
     * @return:	The list of available notify informs
     */
    public function viewNotifies() {
        // Security check
        if (!SecurityUtil::checkPermission('IWnotify::', "::", ACCESS_READ)) {
            throw new Zikula_Exception_Forbidden();
        }

        $notifies = ModUtil::apiFunc('IWnotify', 'user', 'getAllUserNotifies');

        foreach ($notifies as $notify) {
            $notifies[$notify['notifyId']]['notifyOpenDate'] = ($notify['notifyOpenDate'] != '0000-00-00 00:00:00') ? DateUtil::formatDatetime($notify['notifyOpenDate'], '%d/%m/%Y') : '';
            $notifies[$notify['notifyId']]['notifyCloseDate'] = ($notify['notifyCloseDate'] != '0000-00-00 00:00:00') ? DateUtil::formatDatetime($notify['notifyCloseDate'], '%d/%m/%Y') : '';
        }

        return $this->view->assign('notifies', $notifies)
                        ->fetch('IWnotify_user_viewNotifies.htm');
    }

    /**
     * starts the process to create a new notify inform. The notify creation have 3 steps:
     * 1. Define relevant information about the notify
     * 2. Import data from xml file
     * 3. Define validation field
     * 4. Define notify structure and validation
     * 5. Define notify validators
     * @author:     Albert Pérez Monfort (aperezm@xtec.cat)
     * @return:	The notify creation form
     */
    public function newNotify($args) {
        $notifyId = FormUtil::getPassedValue('notifyId', isset($args['notifyId']) ? $args['notifyId'] : 0, 'REQUEST');
        $step = FormUtil::getPassedValue('step', isset($args['step']) ? $args['step'] : 0, 'REQUEST');
        // Security check
        if (!SecurityUtil::checkPermission('IWnotify::', "::", ACCESS_ADD)) {
            throw new Zikula_Exception_Forbidden();
        }
        if ($notifyId > 0) {
            $notify = ModUtil::apiFunc('IWnotify', 'user', 'getNotify', array('notifyId' => $notifyId));
            if (!$notify) {
                LogUtil::registerError($this->__('Error getting notify inform.'));
                // Redirect to the main site for the user
                return System::redirect(ModUtil::url('IWnotify', 'user', 'viewNotifies'));
            }
        } else {
            $notify = array('notifyId' => 0,
                'notifyTitle' => '',
                'notifyDescription' => '',
                'notifyType' => 1,
                'notifyOpenDate' => '',
                'notifyCloseDate' => '',
                'notifyReturnUrl' => '',
                'notifyCloseMsg' => '',
                'notifyFailsMsg' => '',
                'notifyFormText' => '',
            );
        }
        if ($step > 1) {
            if ($notifyId == 0) {
                LogUtil::registerError($this->__('No notify id received.'));
                // Redirect to the main site for the user
                return System::redirect(ModUtil::url('IWnotify', 'user', 'viewNotifies'));
            }
            // get fields in case them exist
            $fields = ModUtil::apiFunc('IWnotify', 'user', 'getAllNotifyFields', array('notifyId' => $notifyId));
        }
        switch ($step) {
            case 0:
            case 1:
                return $this->view->assign('step', 1)
                                ->assign('notify', $notify)
                                ->assign('func', 'createNotify')
                                ->fetch('IWnotify_user_addEditNotify.htm');

                break;
            case 2:
                return $this->view->assign('step', 2)
                                ->assign('notify', $notify)
                                ->assign('fields', $fields)
                                ->fetch('IWnotify_user_importInform.htm');
                break;
            case 3:
                return $this->view->assign('step', 3)
                                ->assign('notify', $notify)
                                ->assign('fields', $fields)
                                ->fetch('IWnotify_user_validationField.htm');
                break;
            case 4:
                return $this->view->assign('step', 4)
                                ->assign('notify', $notify)
                                ->assign('fields', $fields)
                                ->fetch('IWnotify_user_formatInform.htm');
                break;
        }
    }

    /**
     * Create a new notify inform
     * @author:     Albert Pérez Monfort (aperezm@xtec.cat)
     * @return:	The notify creation result. Depending on the step redirect to the needed creation step
     */
    public function createNotify($args) {
        $notifyId = FormUtil::getPassedValue('notifyId', isset($args['notifyId']) ? $args['notifyId'] : 0, 'GETPOST');
        $step = FormUtil::getPassedValue('step', isset($args['step']) ? $args['step'] : 0, 'GETPOST');

        $errorMsg = '';
        $error = false;

        // Security check
        if (!SecurityUtil::checkPermission('IWnotify::', "::", ACCESS_ADD)) {
            throw new Zikula_Exception_Forbidden();
        }

        // Confirm authorisation code
        $this->checkCsrfToken();

        switch ($step) {
            case 1:
                $notifyTitle = FormUtil::getPassedValue('notifyTitle', isset($args['notifyTitle']) ? $args['notifyTitle'] : null, 'POST');
                $notifyDescription = FormUtil::getPassedValue('notifyDescription', isset($args['notifyDescription']) ? $args['notifyDescription'] : null, 'POST');
                $notifyOpenDate = FormUtil::getPassedValue('notifyOpenDate', isset($args['notifyOpenDate']) ? $args['notifyOpenDate'] : null, 'POST');
                $notifyCloseDate = FormUtil::getPassedValue('notifyCloseDate', isset($args['notifyCloseDate']) ? $args['notifyCloseDate'] : null, 'POST');
                $notifyType = FormUtil::getPassedValue('notifyType', isset($args['notifyType']) ? $args['notifyType'] : 0, 'POST');
                $notifyCloseMsg = FormUtil::getPassedValue('notifyCloseMsg', isset($args['notifyCloseMsg']) ? $args['notifyCloseMsg'] : null, 'POST');
                $notifyReturnUrl = FormUtil::getPassedValue('notifyReturnUrl', isset($args['notifyReturnUrl']) ? $args['notifyReturnUrl'] : null, 'POST');
                $notifyFailsMsg = FormUtil::getPassedValue('notifyFailsMsg', isset($args['notifyFailsMsg']) ? $args['notifyFailsMsg'] : null, 'POST');
                $notifyFormText = FormUtil::getPassedValue('notifyFormText', isset($args['notifyFormText']) ? $args['notifyFormText'] : null, 'POST');

                $notifyOpenDate = '20' . substr($notifyOpenDate, 6, 2) . '-' . substr($notifyOpenDate, 3, 2) . '-' . substr($notifyOpenDate, 0, 2) . ' 00:00:00';
                $notifyCloseDate = '20' . substr($notifyCloseDate, 6, 2) . '-' . substr($notifyCloseDate, 3, 2) . '-' . substr($notifyCloseDate, 0, 2) . ' 00:00:00';

                $created = ModUtil::apiFunc('IWnotify', 'user', 'createNotify', array('notifyTitle' => $notifyTitle,
                            'notifyDescription' => $notifyDescription,
                            'notifyOpenDate' => $notifyOpenDate,
                            'notifyCloseDate' => $notifyCloseDate,
                            'notifyType' => $notifyType,
                            'notifyCloseMsg' => $notifyCloseMsg,
                            'notifyReturnUrl' => $notifyReturnUrl,
                            'notifyFailsMsg' => $notifyFailsMsg,
                            'notifyFormText' => $notifyFormText,
                        ));
                if (!$created) {
                    LogUtil::registerError($this->__('Error creating notify inform.'));
                    // Redirect to the main site for the user
                    return System::redirect(ModUtil::url('IWnotify', 'user', 'viewNotifies'));
                }

                LogUtil::registerStatus($this->__('The notify inform has been created successfully.'));

                // Redirect to the main site for the user
                return System::redirect(ModUtil::url('IWnotify', 'user', 'newNotify', array('notifyId' => $created,
                                    'step' => 2)));
                break;
            case 2:
                // get notify inform for segurity proposals
                // checks that the correct file has been received
                $importFile = FormUtil::getPassedValue('importFile', isset($args['importFile']) ? $args['importFile'] : null, 'FILES');
                if ($importFile['name'] == '') {
                    $errorMsg = $this->__('No file selected.');
                    $error = true;
                }
                $fileName = $importFile['name'];
                $fileExtension = FileUtil::getExtension($fileName);
                if (strtolower($fileExtension) != 'xml') {
                    $errorMsg = $this->__('The file extension is not correct. Only the extension XML is allowed.');
                    $error = true;
                }
                // checks the correct format of the xml file
                $source = $importFile['tmp_name'];
                $doc = new DOMDocument();
                if (!$doc->load($source)) {
                    $errorMsg = $this->__('Error opening the xml file.');
                    $error = true;
                }
                $items = $doc->getElementsByTagName("Row");
                $nitems = 0;
                $itemsArray = array();
                $columnsArray = array();
                foreach ($items as $item) {
                    $cells = $item->getElementsByTagName('Cell');
                    $j = 0;
                    foreach ($cells as $cell) {
                        $data = $cell->getElementsByTagName('Data')->item(0)->nodeValue;
                        $columnsArray[$nitems][$j] = $data;
                        $j++;
                    }
                    $nitems++;
                }

                if ($nitems == 0) {
                    $errorMsg = $this->__('No items found in xml file.');
                    $error = true;
                }

                /*
                  // verify that the xml structure is correct. At least need the same number of elements for each file
                  $nElements = count($columnsArray[0]);
                  foreach ($columnsArray as $column) {
                  if (count($column) != $nElements) {
                  $errorMsg = $this->__('Bad formated xml file.');
                  $error = true;
                  }
                  }
                 * 
                 */

                if ($error) {
                    LogUtil::registerError($errorMsg);
                    // Redirect to the main site for the user
                    return System::redirect(ModUtil::url('IWnotify', 'user', 'newNotify', array('notifyId' => $created,
                                        'step' => 2)));
                }

                // delete previous fields if exists
                if (!ModUtil::apifunc('IWnotify', 'user', 'deleteFields', array('notifyId' => $notifyId))) {
                    LogUtil::registerError($this->__("Error deleting previous fields."));
                    // Redirect to the main site for the user
                    return System::redirect(ModUtil::url('IWnotify', 'user', 'newNotify', array('notifyId' => $notifyId,
                                        'step' => 2)));
                }

                // delete previous fields contents if exists
                if (!ModUtil::apifunc('IWnotify', 'user', 'deleteFieldsContent', array('notifyId' => $notifyId))) {
                    LogUtil::registerError($this->__("Error deleting previous fields content."));
                    // Redirect to the main site for the user
                    return System::redirect(ModUtil::url('IWnotify', 'user', 'newNotify', array('notifyId' => $notifyId,
                                        'step' => 2)));
                }

                $fieldsCreatedArray = array();

                // the file is formated correctly. Create fields in database
                foreach ($columnsArray[0] as $field) {
                    $fieldName = ModUtil::func('IWnotify', 'user', 'prepareFieldname', array('fieldName' => $field));
                    $fieldCreated = ModUtil::apiFunc('IWnotify', 'user', 'createField', array('notifyId' => $notifyId,
                                'notifyFieldName' => $fieldName));
                    if (!$fieldCreated) {
                        LogUtil::registerError($this->__("Error creating notify field: " . $fieldName));
                        // Redirect to the main site for the user
                        return System::redirect(ModUtil::url('IWnotify', 'user', 'newNotify', array('notifyId' => $notifyId,
                                            'step' => 2)));
                    }
                    $fieldsCreatedArray[] = $fieldCreated;
                }

                // create data for each field
                for ($i = 1; $i < $nitems; $i++) {
                    $j = 0;
                    if (count($columnsArray[$i]) > 1) {
                        foreach ($columnsArray[$i] as $content) {
                            $fieldContent = ModUtil::apiFunc('IWnotify', 'user', 'fillField', array('notifyId' => $notifyId,
                                        'notifyFieldId' => $fieldsCreatedArray[$j],
                                        'notifyFieldContent' => $content,
                                        'notifyRecordId' => $i,
                                    ));
                            if (!$fieldContent) {
                                LogUtil::registerError($this->__("Error filling field content: " . $content));
                                // Redirect to the main site for the user
                                return System::redirect(ModUtil::url('IWnotify', 'user', 'newNotify', array('notifyId' => $notifyId,
                                                    'step' => 2)));
                            }
                            $j++;
                        }
                    }
                }

                LogUtil::registerStatus($this->__('The data has been saved successfully in database.'));

                // Redirect to the main site for the user
                return System::redirect(ModUtil::url('IWnotify', 'user', 'newNotify', array('notifyId' => $notifyId,
                                    'step' => 3)));
                break;
            case 3:
                $notifyAuthField = FormUtil::getPassedValue('notifyAuthField', isset($args['notifyAuthField']) ? $args['notifyAuthField'] : 0, 'POST');

                if ($notifyAuthField == 0) {
                    LogUtil::registerError($this->__("Please select a validation field."));
                    // Redirect to the main site for the user
                    return System::redirect(ModUtil::url('IWnotify', 'user', 'newNotify', array('notifyId' => $notifyId,
                                        'step' => 3)));
                }

                if (!ModUtil::apiFunc('IWnotify', 'user', 'selectValidationField', array('notifyId' => $notifyId,
                            'notifyAuthField' => $notifyAuthField))) {
                    LogUtil::registerError($this->__('Error selecting the validation field.'));
                    return System::redirect(ModUtil::url('IWnotify', 'user', 'newNotify', array('notifyId' => $notifyId,
                                        'step' => 3)));
                }

                LogUtil::registerStatus($this->__('The validation field has been successfully selected.'));


                // Redirect to the main site for the user
                return System::redirect(ModUtil::url('IWnotify', 'user', 'newNotify', array('notifyId' => $notifyId,
                                    'step' => 4)));

                break;
            case 4:
                $notifyFormat = FormUtil::getPassedValue('notifyFormat', isset($args['notifyFormat']) ? $args['notifyFormat'] : null, 'POST');

                $edited = ModUtil::apiFunc('IWnotify', 'user', 'editNotify', array('notifyId' => $notifyId,
                            'items' => array('notifyFormat' => $notifyFormat),
                        ));
                if (!$edited) {
                    LogUtil::registerError($this->__('Error editing notify inform format.'));
                    // Redirect to the main site for the user
                    return System::redirect(ModUtil::url('IWnotify', 'user', 'viewNotifies'));
                }

                LogUtil::registerStatus($this->__('The notify has been created or modified successfully.'));

                return System::redirect(ModUtil::url('IWnotify', 'user', 'viewNotifies'));
                break;
        }
    }

    public function editNotify($args) {
        $notifyId = FormUtil::getPassedValue('notifyId', isset($args['notifyId']) ? $args['notifyId'] : 0, 'GETPOST');

        // Security check
        if (!SecurityUtil::checkPermission('IWnotify::', "::", ACCESS_ADD)) {
            throw new Zikula_Exception_Forbidden();
        }

        // get inform
        $notify = ModUtil::apiFunc('IWnotify', 'user', 'getNotify', array('notifyId' => $notifyId));

        if (!$notify) {
            LogUtil::registerError($this->__('Error getting notify inform.'));
            // Redirect to the main site for the user
            return System::redirect(ModUtil::url('IWnotify', 'user', 'viewNotifies'));
        }

        // check if notify author is the current user
        if ($notify['notifyCreator'] != UserUtil::getVar('uid') && !SecurityUtil::checkPermission('IWnotify::', "::", ACCESS_ADMIN)) {
            throw new Zikula_Exception_Forbidden();
        }

        $notify['notifyOpenDate'] = DateUtil::formatDatetime($notify['notifyOpenDate'], '%d/%m/%y');
        $notify['notifyCloseDate'] = DateUtil::formatDatetime($notify['notifyCloseDate'], '%d/%m/%y');

        return $this->view->assign('step', 1)
                        ->assign('notify', $notify)
                        ->assign('func', 'updateNotify')
                        ->fetch('IWnotify_user_addEditNotify.htm');
    }

    public function updateNotify($args) {
        $notifyId = FormUtil::getPassedValue('notifyId', isset($args['notifyId']) ? $args['notifyId'] : 0, 'GETPOST');
        $notifyTitle = FormUtil::getPassedValue('notifyTitle', isset($args['notifyTitle']) ? $args['notifyTitle'] : null, 'POST');
        $notifyDescription = FormUtil::getPassedValue('notifyDescription', isset($args['notifyDescription']) ? $args['notifyDescription'] : null, 'POST');
        $notifyOpenDate = FormUtil::getPassedValue('notifyOpenDate', isset($args['notifyOpenDate']) ? $args['notifyOpenDate'] : null, 'POST');
        $notifyCloseDate = FormUtil::getPassedValue('notifyCloseDate', isset($args['notifyCloseDate']) ? $args['notifyCloseDate'] : null, 'POST');
        $notifyType = FormUtil::getPassedValue('notifyType', isset($args['notifyType']) ? $args['notifyType'] : 0, 'POST');
        $notifyCloseMsg = FormUtil::getPassedValue('notifyCloseMsg', isset($args['notifyCloseMsg']) ? $args['notifyCloseMsg'] : null, 'POST');
        $notifyReturnUrl = FormUtil::getPassedValue('notifyReturnUrl', isset($args['notifyReturnUrl']) ? $args['notifyReturnUrl'] : null, 'POST');
        $notifyFailsMsg = FormUtil::getPassedValue('notifyFailsMsg', isset($args['notifyFailsMsg']) ? $args['notifyFailsMsg'] : null, 'POST');
        $notifyFormText = FormUtil::getPassedValue('notifyFormText', isset($args['notifyFormText']) ? $args['notifyFormText'] : null, 'POST');

        // Security check
        if (!SecurityUtil::checkPermission('IWnotify::', "::", ACCESS_ADD)) {
            throw new Zikula_Exception_Forbidden();
        }

        // get inform
        $notify = ModUtil::apiFunc('IWnotify', 'user', 'getNotify', array('notifyId' => $notifyId));

        if (!$notify) {
            LogUtil::registerError($this->__('Error getting notify inform.'));
            // Redirect to the main site for the user
            return System::redirect(ModUtil::url('IWnotify', 'user', 'viewNotifies'));
        }

        $notifyOpenDate = '20' . substr($notifyOpenDate, 6, 2) . '-' . substr($notifyOpenDate, 3, 2) . '-' . substr($notifyOpenDate, 0, 2) . ' 00:00:00';
        $notifyCloseDate = '20' . substr($notifyCloseDate, 6, 2) . '-' . substr($notifyCloseDate, 3, 2) . '-' . substr($notifyCloseDate, 0, 2) . ' 00:00:00';

        if (!ModUtil::apiFunc('IWnotify', 'user', 'editNotify', array(
                    'notifyId' => $notifyId,
                    'items' => array(
                        'notifyTitle' => $notifyTitle,
                        'notifyDescription' => $notifyDescription,
                        'notifyOpenDate' => $notifyOpenDate,
                        'notifyCloseDate' => $notifyCloseDate,
                        'notifyType' => $notifyType,
                        'notifyCloseMsg' => $notifyCloseMsg,
                        'notifyReturnUrl' => $notifyReturnUrl,
                        'notifyFailsMsg' => $notifyFailsMsg,
                        'notifyFormText' => $notifyFormText,
                    ),
                ))) {
            LogUtil::registerError($this->__('Error editing notify inform.'));
            // Redirect to the main site for the user
            return System::redirect(ModUtil::url('IWnotify', 'user', 'viewNotifies'));
        }
        LogUtil::registerStatus($this->__('The notify inform has been edited.'));
        // Redirect to the main site for the user
        return System::redirect(ModUtil::url('IWnotify', 'user', 'viewNotifies'));
    }

    public function prepareFieldname($args) {
        $fieldName = FormUtil::getPassedValue('fieldName', isset($args['fieldName']) ? $args['fieldName'] : null, 'POST');

        $cleanedString = preg_replace("/[^a-zA-Z0-9]/", "", $fieldName);

        return $cleanedString;
    }

    public function loadNotify($args) {
        $notifyId = FormUtil::getPassedValue('notifyId', isset($args['notifyId']) ? $args['notifyId'] : 0, 'GETPOST');
        $errorMsgCode = FormUtil::getPassedValue('errorMsgCode', isset($args['errorMsgCode']) ? $args['errorMsgCode'] : 0, 'GETPOST');

        // Security check
        if (!SecurityUtil::checkPermission('IWnotify::', "::", ACCESS_READ)) {
            throw new Zikula_Exception_Forbidden();
        }

        $outOfDate = false;

        $notify = ModUtil::apiFunc('IWnotify', 'user', 'getNotify', array('notifyId' => $notifyId));
        if (!$notify) {
            LogUtil::registerError($this->__('Error getting notify inform.'));
            // Redirect to the main site for the user
            return System::redirect(ModUtil::url('IWnotify', 'user', 'viewNotifies'));
        }

        // check if it is a valid date
        if (($notify['notifyOpenDate'] != '' && time() < DateUtil::makeTimestamp($notify['notifyOpenDate'])) || ($notify['notifyCloseDate'] != '' && time() > DateUtil::makeTimestamp($notify['notifyCloseDate']))) {
            $notifyLogIp = ModUtil::func('IWnotify', 'user', 'getIp');
            $userId = (UserUtil::isLoggedIn()) ? UserUtil::getVar('uid') : '-1';
            ModUtil::apiFunc('IWnotify', 'user', 'saveLog', array('notifyId' => $notifyId,
                'logType' => -1,
                'notifyLogIp' => $notifyLogIp,
                'userId' => $userId,
                'validateData' => '',
            ));
            $outOfDate = true;
        }

        $operators = array('+', '-', '*');
        $secVal1 = rand(1, 9);
        $secOperator = $operators[rand(0, 2)];
        $secVal2 = rand(1, 9);

        switch ($secOperator) {
            case '+':
                $result = $secVal1 + $secVal2;
                break;
            case '-':
                $result = $secVal1 - $secVal2;
                break;
            case '*':
                $result = $secVal1 * $secVal2;
                break;
        }

        SessionUtil::setVar('secResult', $result);

        return $this->view->assign('notify', $notify)
                        ->assign('errorMsgCode', $errorMsgCode)
                        ->assign('outOfDate', $outOfDate)
                        ->assign('secVal1', $secVal1)
                        ->assign('secVal2', $secVal2)
                        ->assign('secOperator', $secOperator)
                        ->fetch('IWnotify_user_openNotify.htm');
    }

    public function deleteNotify($args) {
        $notifyId = FormUtil::getPassedValue('notifyId', isset($args['notifyId']) ? $args['notifyId'] : 0, 'GETPOST');
        $confirm = FormUtil::getPassedValue('confirm', isset($args['confirm']) ? $args['confirm'] : 0, 'POST');

        // Security check
        if (!SecurityUtil::checkPermission('IWnotify::', "::", ACCESS_ADD)) {
            throw new Zikula_Exception_Forbidden();
        }

        // get inform
        $notify = ModUtil::apiFunc('IWnotify', 'user', 'getNotify', array('notifyId' => $notifyId));

        if (!$notify) {
            LogUtil::registerError($this->__('Error getting notify inform.'));
            // Redirect to the main site for the user
            return System::redirect(ModUtil::url('IWnotify', 'user', 'viewNotifies'));
        }

        // check if notify author is the current user
        if ($notify['notifyCreator'] != UserUtil::getVar('uid') && !SecurityUtil::checkPermission('IWnotify::', "::", ACCESS_ADMIN)) {
            throw new Zikula_Exception_Forbidden();
        }

        if ($confirm == 0) {
            return $this->view->assign('notify', $notify)
                            ->fetch('IWnotify_user_deleteNotify.htm');
        }

        // deletion confirmed. Proceed with it
        if (!ModUtil::apiFunc('IWnotify', 'user', 'deleteNotify', array('notifyId' => $notifyId))) {
            LogUtil::registerError($this->__('Error deleting notify.'));
            // Redirect to the main site for the user
            return System::redirect(ModUtil::url('IWnotify', 'user', 'viewNotifies'));
        }

        LogUtil::registerStatus($this->__('Notify deleted.'));
        // Redirect to the main site for the user
        return System::redirect(ModUtil::url('IWnotify', 'user', 'viewNotifies'));
    }

    public function getInform($args) {
        $notifyId = FormUtil::getPassedValue('notifyId', isset($args['notifyId']) ? $args['notifyId'] : 0, 'POST');
        $validateData = FormUtil::getPassedValue('validateData', isset($args['validateData']) ? $args['validateData'] : 0, 'POST');
        $validateSecAns = FormUtil::getPassedValue('validateSecAns', isset($args['validateSecAns']) ? $args['validateSecAns'] : null, 'POST');

        // Security check
        if (!SecurityUtil::checkPermission('IWnotify::', "::", ACCESS_READ)) {
            throw new Zikula_Exception_Forbidden();
        }

        // Confirm authorisation code
        $this->checkCsrfToken();

        $errorMsgCode = 0;
        $logType = 0;

        // get user IP
        $notifyLogIp = ModUtil::func('IWnotify', 'user', 'getIp');

        $userId = (UserUtil::isLoggedIn()) ? UserUtil::getVar('uid') : '-1';

        if ($validateData == '') {
            $errorMsgCode = 1;
            $logType = -4;
        }

        if ($validateSecAns != SessionUtil::getVar('secResult')) {
            $errorMsgCode = 2;
            $logType = -2;
        }

        if ($errorMsgCode != 0) {
            ModUtil::apiFunc('IWnotify', 'user', 'saveLog', array('notifyId' => $notifyId,
                'logType' => $logType,
                'notifyLogIp' => $notifyLogIp,
                'userId' => $userId,
                'validateData' => $validateData,
            ));
            // Redirect to the main site for the user
            return System::redirect(ModUtil::url('IWnotify', 'user', 'loadNotify', array('notifyId' => $notifyId,
                                'errorMsgCode' => $errorMsgCode)));
        }

        // get notify inform validation field
        $validationField = ModUtil::apiFunc('IWnotify', 'user', 'getNotifyValidationField', array('notifyId' => $notifyId));

        if (!$validationField) {
            LogUtil::registerError($this->__('Error! Not possible to get validation field.'));
            // Redirect to the main site for the user
            return System::redirect(ModUtil::url('IWnotify', 'user', 'viewNotifies'));
        }

        // get data acording with the validation field information
        $validateValue = ModUtil::apiFunc('IWnotify', 'user', 'getNotifyValidateValue', array('notifyFieldId' => $validationField['notifyFieldId'],
                    'notifyId' => $notifyId,
                    'notifyFieldContent' => $validateData));

        if (!$validateValue || strtolower($validateData) != strtolower($validateValue[$notifyId]['notifyFieldContent'])) {
            ModUtil::apiFunc('IWnotify', 'user', 'saveLog', array('notifyId' => $notifyId,
                'logType' => -3,
                'notifyLogIp' => $notifyLogIp,
                'userId' => $userId,
                'validateData' => $validateData,
            ));
            // Redirect to the main site for the user
            return System::redirect(ModUtil::url('IWnotify', 'user', 'loadNotify', array('notifyId' => $notifyId,
                                'errorMsgCode' => 3)));
        }

        SessionUtil::delVar('secResult');

        // prepare inform form to user view
        // get inform
        $notify = ModUtil::apiFunc('IWnotify', 'user', 'getNotify', array('notifyId' => $notifyId));

        if (!$notify) {
            LogUtil::registerError($this->__('Error getting notify inform.'));
            // Redirect to the main site for the user
            return System::redirect(ModUtil::url('IWnotify', 'user', 'viewNotifies'));
        }

        // get fields in case them exist
        $fields = ModUtil::apiFunc('IWnotify', 'user', 'getAllNotifyFields', array('notifyId' => $notifyId));

        // get inform content
        $fieldsContent = ModUtil::apiFunc('IWnotify', 'user', 'getNotifyValues', array('notifyId' => $notifyId,
                    'notifyRecordId' => $validateValue[$notifyId]['notifyRecordId']));

        $output = $notify['notifyFormat'];

        foreach ($fields as $field) {
            $output = str_replace('$$' . $field['notifyFieldName'] . '$$', trim($fieldsContent[$field['notifyFieldId']]['notifyFieldContent']), $output);
        }

        ModUtil::apiFunc('IWnotify', 'user', 'saveLog', array('notifyId' => $notifyId,
            'logType' => 1,
            'notifyLogIp' => $notifyLogIp,
            'userId' => $userId,
            'validateData' => $validateData,
        ));

        return $this->view->assign('notify', $notify)
                        ->assign('output', $output)
                        ->fetch('IWnotify_user_getInform.htm');
    }

    public function getIp() {

        // Security check
        if (!SecurityUtil::checkPermission('IWnotify::', "::", ACCESS_READ)) {
            throw new Zikula_Exception_Forbidden();
        }
        $ip = '';
        if (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = ModUtil::func('IWnotify', 'user', 'cleanremoteaddr', array('originaladdr' => $_SERVER['REMOTE_ADDR']));
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = ModUtil::func('IWnotify', 'user', 'cleanremoteaddr', array('originaladdr' => $_SERVER['HTTP_X_FORWARDED_FOR']));
        }
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = ModUtil::func('IWnotify', 'user', 'cleanremoteaddr', array('originaladdr' => $_SERVER['HTTP_CLIENT_IP']));
        }

        return $ip;
    }

    public function cleanremoteaddr($args) {
        $originaladdr = $args['originaladdr'];
        $matches = array();
        // first get all things that look like IP addresses.
        if (!preg_match_all('/(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/', $args['originaladdr'], $matches, PREG_SET_ORDER)) {
            return '';
        }
        $goodmatches = array();
        $lanmatches = array();
        foreach ($matches as $match) {
            // check to make sure it's not an internal address.
            // the following are reserved for private lans...
            // 10.0.0.0 - 10.255.255.255
            // 172.16.0.0 - 172.31.255.255
            // 192.168.0.0 - 192.168.255.255
            // 169.254.0.0 -169.254.255.255
            $bits = explode('.', $match[0]);
            if (count($bits) != 4) {
                // weird, preg match shouldn't give us it.
                continue;
            }
            if (($bits[0] == 10)
                    || ($bits[0] == 172 && $bits[1] >= 16 && $bits[1] <= 31)
                    || ($bits[0] == 192 && $bits[1] == 168)
                    || ($bits[0] == 169 && $bits[1] == 254)) {
                $lanmatches[] = $match[0];
                continue;
            }
            // finally, it's ok
            $goodmatches[] = $match[0];
        }
        if (!count($goodmatches)) {
            // perhaps we have a lan match, it's probably better to return that.
            if (!count($lanmatches)) {
                return '';
            } else {
                return array_pop($lanmatches);
            }
        }
        if (count($goodmatches) == 1) {
            return $goodmatches[0];
        }

        // We need to return something, so return the first
        return array_pop($goodmatches);
    }

}