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

        $notifies = array();
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
                                ->fetch('IWnotify_user_importInform.htm');
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
                // get notify inform
          
                
                
                
                
                
                
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
                $i = 0;
                $itemsArray = array();
                $columnsArray = array();
                foreach ($items as $item) {
                    $cells = $item->getElementsByTagName('Cell');
                    $j = 0;
                    foreach ($cells as $cell) {
                        $data = $cell->getElementsByTagName('Data')->item(0)->nodeValue;
                        $columnsArray[$i][$j] = $data;
                        $j++;
                    }
                    $i++;
                }

                if ($i == 0) {
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
                    // create data for each field
                    for ($i = 1; $i < count($columnsArray); $i++) {
                        foreach ($columnsArray[$i] as $content) {
                            $fieldContent = ModUtil::apiFunc('IWnotify', 'user', 'fillField', array('notifyId' => $notifyId,
                                        'notifyFieldId' => $fieldCreated,
                                        'notifyFieldContent' => $content,
                                    ));
                            if (!$fieldContent) {
                                LogUtil::registerError($this->__("Error filling field content: " . $content));
                                // Redirect to the main site for the user
                                return System::redirect(ModUtil::url('IWnotify', 'user', 'newNotify', array('notifyId' => $notifyId,
                                                    'step' => 2)));
                            }
                        }
                    }
                }

                print_r($columnsArray);
                die();
        }
    }

    public function prepareFieldname($args) {
        $fieldName = FormUtil::getPassedValue('fieldName', isset($args['fieldName']) ? $args['fieldName'] : null, 'POST');
        
        $cleanedString = preg_replace("/[^a-zA-Z0-9]/", "", $fieldName);

        return $cleanedString;
    }

}