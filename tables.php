<?php

/**
 * Define module tables
 * @author Albert Pérez Monfort (aperezm@xtec.cat)
 * @return module tables information
 */
function IWnotify_tables() {
    // Initialise table array
    $tables = array();

    // IWforms_def table definition
    $tables['IWnotify_definition'] = DBUtil::getLimitedTablename('IWnotify_definition');
    $tables['IWnotify_definition_column'] = array('notifyId' => 'iw_notifyId',
        'notifyTitle' => 'iw_notifyTitle',
        'notifyDescription' => 'iw_notifyDescription',
        'notifyActive' => 'iw_notifyActive',
        'notifyOpenDate' => 'iw_notifyOpenDate',
        'notifyCloseDate' => 'iw_notifyCloseDate',
        'notifyType' => 'iw_notifyType', // 0 - registrated users, 1 - unregistrated users, 2 - registrated and unregistrated users
        'notifyCreator' => 'iw_notifyCreator',
        'notifyFormat' => 'iw_notifyFormat',
        'notifyCloseMsg' => 'iw_notifyCloseMsg',
        'notifyReturnUrl' => 'iw_notifyReturnUrl',
        'notifyFailsMsg' => 'iw_notifyFailsMsg',
        'notifyFormText' => 'notifyFormText',
    );

    $tables['IWnotify_definition_column_def'] = array('notifyId' => "I NOTNULL AUTO PRIMARY",
        'notifyTitle' => "C(255) NOTNULL DEFAULT ''",
        'notifyDescription' => "X NOTNULL",
        'notifyActive' => "I(1) NOTNULL DEFAULT '0'",
        'notifyOpenDate' => "T DEFDATETIME NOTNULL DEFAULT '1970-01-01 00:00:00'",
        'notifyCloseDate' => "T DEFDATETIME NOTNULL DEFAULT '1970-01-01 00:00:00'",
        'notifyType' => "I(1) NOTNULL DEFAULT '0'",
        'notifyCreator' => "I NOTNULL DEFAULT '0'",
        'notifyFormat' => "X NOTNULL",
        'notifyCloseMsg' => "X NOTNULL",
        'notifyReturnUrl' => "C(180) NOTNULL DEFAULT ''",
        'notifyFailsMsg' => "X NOTNULL",
        'notifyFormText' => "X NOTNULL",
    );

    ObjectUtil::addStandardFieldsToTableDefinition($tables['IWnotify_definition_column'], 'pn_');
    ObjectUtil::addStandardFieldsToTableDataDefinition($tables['IWnotify_definition_column_def'], 'iw_');

    // IWforms_resp table definition
    $tables['IWnotify_validator'] = DBUtil::getLimitedTablename('IWnotify_validator');
    $tables['IWnotify_validator_column'] = array('notifyValidatorId' => 'iw_notifyValidatorId',
        'notifyId' => 'iw_notifyId',
        'notifyValidator' => 'iw_notifyValidator',
    );

    $tables['IWnotify_validator_column_def'] = array('notifyValidatorId' => "I NOTNULL AUTO PRIMARY",
        'notifyId' => "I NOTNULL DEFAULT '0'",
        'notifyValidator' => "I NOTNULL DEFAULT '0'",
    );

    ObjectUtil::addStandardFieldsToTableDefinition($tables['IWnotify_validator_column'], 'pn_');
    ObjectUtil::addStandardFieldsToTableDataDefinition($tables['IWnotify_validator_column_def'], 'iw_');


    $tables['IWnotify_fields'] = DBUtil::getLimitedTablename('IWnotify_fields');
    $tables['IWnotify_fields_column'] = array('notifyFieldId' => 'iw_notifyFieldId',
        'notifyId' => 'iw_notifyId',
        'notifyFieldName' => 'iw_notifyFieldName',
        'notifyAuthField' => 'iw_notifyAuthField',
    );

    $tables['IWnotify_fields_column_def'] = array('notifyFieldId' => "I NOTNULL AUTO PRIMARY",
        'notifyId' => "I NOTNULL DEFAULT '0'",
        'notifyFieldName' => "C(50) NOTNULL DEFAULT ''",
        'notifyAuthField' => "I(1) NOTNULL DEFAULT '0'",
    );

    ObjectUtil::addStandardFieldsToTableDefinition($tables['IWnotify_fields_column'], 'pn_');
    ObjectUtil::addStandardFieldsToTableDataDefinition($tables['IWnotify_fields_column_def'], 'iw_');


    $tables['IWnotify_fields_content'] = DBUtil::getLimitedTablename('IWnotify_fields_content');
    $tables['IWnotify_fields_content_column'] = array('notifyFieldContentId' => 'iw_notifyFieldContentId',
        'notifyFieldId' => 'iw_notifyFieldId',
        'notifyId' => 'iw_notifyId',
        'notifyFieldContent' => 'iw_notifyFieldContent',
        'notifyRecordId' => 'iw_notifyRecordId',
    );

    $tables['IWnotify_fields_content_column_def'] = array('notifyFieldContentId' => "I NOTNULL AUTO PRIMARY",
        'notifyFieldId' => "I NOTNULL DEFAULT '0'",
        'notifyId' => "I NOTNULL DEFAULT '0'",
        'notifyFieldContent' => "X NOTNULL",
        'notifyRecordId' => "I NOTNULL DEFAULT '0'",
    );

    ObjectUtil::addStandardFieldsToTableDefinition($tables['IWnotify_fields_content_column'], 'pn_');
    ObjectUtil::addStandardFieldsToTableDataDefinition($tables['IWnotify_fields_content_column_def'], 'iw_');

    $tables['IWnotify_logs'] = DBUtil::getLimitedTablename('IWnotify_logs');
    $tables['IWnotify_logs_column'] = array('notifyLogId' => 'iw_notifyLogId',
        'notifyId' => 'iw_notifyId',
        'notifyLogDate' => 'iw_notifyLodDate',
        'notifyLogIp' => 'iw_notifyLogIp',
    );

    $tables['IWnotify_logs_column_def'] = array('notifyLogId' => "I NOTNULL AUTO PRIMARY",
        'notifyId' => "I NOTNULL DEFAULT '0'",
        'notifyLogDate' => "T DEFDATETIME NOTNULL DEFAULT '1970-01-01 00:00:00'",
        'notifyLogIp' => "C(15) NOTNULL DEFAULT ''",
    );

    ObjectUtil::addStandardFieldsToTableDefinition($tables['IWnotify_logs_column'], 'pn_');
    ObjectUtil::addStandardFieldsToTableDataDefinition($tables['IWnotify_logs_column_def'], 'iw_');

    //Returns tables information
    return $tables;
}