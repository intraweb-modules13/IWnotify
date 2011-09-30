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
                'notifyOnlyOnceVisit' => 0,
                'notifyCloseMsg' => '',
                'notifyFailsMsg' => '',
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
                $notifyOnlyOnceVisit = FormUtil::getPassedValue('notifyOnlyOnceVisit', isset($args['notifyOnlyOnceVisit']) ? $args['notifyOnlyOnceVisit'] : 0, 'POST');

                $created = ModUtil::apiFunc('IWnotify', 'user', 'createNotify', array('notifyTitle' => $notifyTitle,
                            'notifyDescription' => $notifyDescription,
                            'notifyOpenDate' => $notifyOpenDate,
                            'notifyCloseDate' => $notifyCloseDate,
                            'notifyType' => $notifyType,
                            'notifyCloseMsg' => $notifyCloseMsg,
                            'notifyReturnUrl' => $notifyReturnUrl,
                            'notifyOnlyOnceVisit' => $notifyOnlyOnceVisit,
                            'notifyFailsMsg' => $notifyFailsMsg,
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

    public function prepareFieldname($args) {
        $fieldName = FormUtil::getPassedValue('fieldName', isset($args['fieldName']) ? $args['fieldName'] : null, 'POST');

        $cleanedString = preg_replace("/[^a-zA-Z0-9]/", "", $fieldName);

        return $cleanedString;
    }

    public function loadNotify($args) {
        $notifyId = FormUtil::getPassedValue('notifyId', isset($args['notifyId']) ? $args['notifyId'] : 0, 'GET');

        // Security check
        if (!SecurityUtil::checkPermission('IWnotify::', "::", ACCESS_READ)) {
            throw new Zikula_Exception_Forbidden();
        }

        $notify = ModUtil::apiFunc('IWnotify', 'user', 'getNotify', array('notifyId' => $notifyId));
        if (!$notify) {
            LogUtil::registerError($this->__('Error getting notify inform.'));
            // Redirect to the main site for the user
            return System::redirect(ModUtil::url('IWnotify', 'user', 'viewNotifies'));
        }

        return $this->view->assign('notify', $notify)
                        ->fetch('IWnotify_user_openNotify.htm');
    }

    public function getInform($args) {
        $notifyId = FormUtil::getPassedValue('notifyId', isset($args['notifyId']) ? $args['notifyId'] : 0, 'POST');
        $validateData = FormUtil::getPassedValue('validateData', isset($args['validateData']) ? $args['validateData'] : 0, 'POST');

        // Security check
        if (!SecurityUtil::checkPermission('IWnotify::', "::", ACCESS_READ)) {
            throw new Zikula_Exception_Forbidden();
        }

        // Confirm authorisation code
        $this->checkCsrfToken();

        print 'CONTINUAR AQUÍ';
        die();
    }

}