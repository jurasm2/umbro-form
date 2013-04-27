<?php

namespace ServiceFactories;

use Nette\DI;

class DibiFactory extends \Nette\Object {

    public static function createConnection(DI\Container $container) {
        
        $dibiConnection = new \DibiConnection($container->params['database']);

        $dibiConnection->query('SET NAMES UTF8');
        
        $substitutions = array(
                            'core'  =>  'cms_',
                            'vd'    =>  'cms_vd_'
        );
               
        foreach($substitutions as $sub => $prefix) {
            $dibiConnection->getSubstitutes()->$sub = $prefix;
        }
        
//        $profiler = new \DibiProfiler();
//        $dibiConnection->setProfiler($profiler);
//        $dibiConnection->setFile(APP_DIR.'/../log/dibi.log');
        
        return $dibiConnection;
        
    }
}
