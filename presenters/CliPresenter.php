<?php

/**
 * Cli presenter.
 */
class CliPresenter extends BasePresenter {
    
    public function testCallback($limit) {
        
        $total = $this->umbroModel->getNumberOfAllUsers();
        $users = $this->umbroModel->getUninvitedUsers($limit);
        
        $numOfUsers = 0;
        
        if ($users) {
            $userIds = array_keys($users);
            $this->umbroModel->setInvitationMailingAsSent($userIds);
            
            foreach ($users as $user) {
                // invite user
                
                printf("Invite user %s %s \n", $user['name'], $user['surname']);
                
                $numOfUsers++;
            }
        }
        
        
        return array($numOfUsers, $total);
    }
    
    public function actionDefault() {
        
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
