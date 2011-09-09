<?php
class IWnotify_Version extends Zikula_AbstractVersion
{
    public function getMetaData() {
        $meta = array();
        $meta['displayname'] = $this->__("IWnotify");
        $meta['description'] = $this->__("Creation, managment and use of notify forms.");
        $meta['url'] = $this->__("IWnotify");
        $meta['version'] = '0.0.1';
        $meta['securityschema'] = array('IWnotify::' => '::');
        /*
        $meta['dependencies'] = array(array('modname' => 'IWmain',
                                            'minversion' => '3.0.0',
                                            'maxversion' => '',
                                            'status' => ModUtil::DEPENDENCY_REQUIRED));
         *
         */
        return $meta;
    }
}