<?php

/**
 * Cli presenter.
 */
class CliPresenter extends BasePresenter {
    
    public function actionDefault() {
        
	die('Cli is dead');
	
//        Components\Lister::$verbose = TRUE;
//        Components\Lister::$limit = 200;
//        
//        $this['lister']->run('sent_invitation', callback($this, 'testCallback'));
//        die();
      
        $this->presenter->umbroModel->generateSignCodes();
        die();
    }
    
    
    public function actionReset() {
        $this['lister']->reset();
    }
        
    
}
