<?php

class IWnotify_Installer extends Zikula_AbstractInstaller {

    /**
     * Initialise the IWforms module creating module tables and module vars
     * @author Albert Pérez Monfort (aperezm@xtec.cat)
     * @return bool true if successful, false otherwise
     */
    public function Install() {
        // Checks if module IWmain is installed. If not returns error
        $modid = ModUtil::getIdFromName('IWmain');
        $modinfo = ModUtil::getInfo($modid);

        if ($modinfo['state'] != 3) {
            return LogUtil::registerError($this->__('Module IWmain is needed. You have to install the IWmain module before installing it.'));
        }

        // Check if the version needed is correct
        $versionNeeded = '3.0.0';
        if (!ModUtil::func('IWmain', 'admin', 'checkVersion', array('version' => $versionNeeded))) {
            return false;
        }

        // Create module tables
        if (!DBUtil::createTable('IWnotify_definition'))
            return false;
        if (!DBUtil::createTable('IWnotify_validator'))
            return false;
        if (!DBUtil::createTable('IWnotify_fields'))
            return false;
        if (!DBUtil::createTable('IWnotify_fields_content'))
            return false;
        if (!DBUtil::createTable('IWnotify_logs'))
            return false;
        
        //Create indexes
        $pntable = DBUtil::getTables();
        $c = $pntable['IWnotify_definition_column'];
        if (!DBUtil::createIndex($c['notifyActive'], 'IWnotify_definition', 'notifyActive'))
            return false;
        if (!DBUtil::createIndex($c['notifyCreator'], 'IWnotify_definition', 'notifyCreator'))
            return false;
        
        $c = $pntable['IWnotify_validator_column'];
        if (!DBUtil::createIndex($c['notifyId'], 'IWnotify_validator', 'notifyId'))
            return false;

        $c = $pntable['IWnotify_fields_column'];
        if (!DBUtil::createIndex($c['notifyId'], 'IWnotify_fields', 'notifyId'))
            return false;

        $c = $pntable['IWnotify_fields_content_column'];
        if (!DBUtil::createIndex($c['notifyFieldId'], 'IWnotify_fields_content', 'notifyFieldId'))
            return false;

        $c = $pntable['IWnotify_logs_column'];
        if (!DBUtil::createIndex($c['notifyId'], 'IWnotify_logs', 'notifyId'))
            return false;

        /*
        //Set module vars
        $this->setVar('characters', '15')
                ->setVar('resumeview', '0')
                ->setVar('newsColor', '#90EE90')
                ->setVar('viewedColor', '#FFFFFF')
                ->setVar('completedColor', '#D3D3D3')
                ->setVar('validatedColor', '#CC9999')
                ->setVar('fieldsColor', '#ADD8E6')
                ->setVar('contentColor', '#FFFFE0')
                ->setVar('attached', 'forms')
                ->setVar('publicFolder', 'forms/public');
         * 
         */
        
        //Successfull
        return true;
    }

    /**
     * Delete the IWforms module
     * @author Albert Pérez Monfort (aperezm@xtec.cat)
     * @return bool true if successful, false otherwise
     */
    public function Uninstall() {
        // Delete module table
        DBUtil::dropTable('IWnotify_definition');
        DBUtil::dropTable('IWnotify_validator');
        DBUtil::dropTable('IWnotify_fields');
        DBUtil::dropTable('IWnotify_fields_content');
        DBUtil::dropTable('IWnotify_logs');

        /*
        //Delete module vars
        $this->delVar('characters')
                ->delVar('resumeview')
                ->delVar('newsColor')
                ->delVar('viewedColor')
                ->delVar('completedColor')
                ->delVar('validatedColor')
                ->delVar('fieldsColor')
                ->delVar('contentColor')
                ->delVar('attached')
                ->delVar('publicFolder');
         * 
         */

        //Deletion successfull
        return true;
    }

    /**
     * Update the IWforms module
     * @author Albert Pérez Monfort (aperezm@xtec.cat)
     * @author Jaume Fernàndez Valiente (jfern343@xtec.cat)
     * @return bool true if successful, false otherwise
     */
    public function upgrade($oldversion) {

        switch ($oldversion) {
            case '0.0.1':
                
        }

        return true;
    }

}