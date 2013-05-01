<?php

/**
 * Homepage presenter.
 */
class CronPresenter extends BasePresenter {
    
    /**
     * Send email to user
     * baseUri and signoffCode are assigned to template
     * 
     * @param array $user
     * @param string $templateName (eg mailing1.latte)
     * @param string $subject
     */
    public function _sendUserMail($user, $templateName, $subject) {
        
        $templateParams = array(
                            'basePath'      =>  $this->baseUri,
                            'signoffCode'   =>  $user['signoff_hash']
        );  
        
        $this->sendUserMail($user['email'], $templateName, $subject, $templateParams);
    }
    

    /**
     * Fetches limited number of users
     * and send particular emails (by flag name)
     * 
     * @param string $flagName (mailing1|mailing2|mailing3)
     * @param string $templateName
     * @param int $limit
     * @return array
     */
    public function _sendGenericMailing($flagName, $templateName, $limit, $subject) {
        // get number of all ACTIVE members
        $total = $this->umbroModel->getNumberOfAllUsers(TRUE);
        $users = $this->presenter->umbroModel->getUsersForMailing($flagName, $limit);
        
        $numOfUsers = 0;
        if ($users) {
            $this->presenter->umbroModel->setMailingAsSent($flagName, array_keys($users));
           
            foreach ($users as $user) {  
                $this->_sendUserMail($user, $templateName, $subject);
                $numOfUsers++;
            } 
        }
        
        return array($numOfUsers, $total);
    }
    
    public function sendMailing1Callback($limit) {
        return $this->_sendGenericMailing('mailing1_sent', 'mailing1.latte', $limit, 'AKCE PRÁVĚ ZAČALA!');
    }
    
    public function sendMailing2Callback($limit) {
        return $this->_sendGenericMailing('mailing2_sent', 'mailing2.latte', $limit, 'AKCE PROBÍHÁ');
    }

    public function sendMailing3Callback($limit) {
        return $this->_sendGenericMailing('mailing3_sent', 'mailing3.latte', $limit, 'Pro velký úspěch...');
    }
        
    public function sendInvitationCallback($limit) {
        
        // get number of all members (ACTIVE AND NON ACTIVE)
        $total = $this->umbroModel->getNumberOfAllUsers();
        $usersForInvitation = $this->presenter->umbroModel->getUninvitedUsers($limit);
        
        $numOfUsers = 0;
        if ($usersForInvitation) {
            $this->presenter->umbroModel->setInvitationMailingAsSent(array_keys($usersForInvitation));
           
            foreach ($usersForInvitation as $user) { 
                $templateParams = array(
                            'basePath'      =>  $this->baseUri,
                            'signinCode'    =>  $user['signin_hash'],
                            'signoffCode'   =>  $user['signoff_hash']
                );  

                $this->sendUserMail($user['email'], 'invitation.latte', 'Pozvánka na V.I.P. UMBRO SPRINGTIME', $templateParams);
                $numOfUsers++;
            } 
        }
        
        return array($numOfUsers, $total);
    }
    
    public function actionSendMailing($mailingId) {
        
        // configure lister 
        Components\Lister::$verbose = TRUE;
        Components\Lister::$limit = 50;
        
        switch ($mailingId) {
            case 1: // mailing1
		die("Mailing 1 is dead");
                $this['lister']->run('mailing1', callback($this, 'sendMailing1Callback'));
                break;
            case 2: // mailing2
		die("Mailing 2 is dead");
                $this['lister']->run('mailing2', callback($this, 'sendMailing2Callback'));
                break;
            case 3: // mailing3 TODO
                echo "Mailing 3 is not ready yet";
                //$this['lister']->run('mailing3', callback($this, 'sendMailing3Callback'));
                break;
            default:
                echo "No such mailing"; 
        }
        
        die();
    }
    
    
    public function actionSendInvitation() {
        
	die('Invitation is dead');
	
        Components\Lister::$verbose = TRUE;
        Components\Lister::$limit = 50;
        
        $this['lister']->run('invitation', callback($this, 'sendInvitationCallback'));
        die();
        
    }
    
    
    public function actionDefault() {
        die('actionDefault is dead');
        
    }
    
    private function _isMonday() {
        return date('D') == 'Mon';
    }

    public function actionInfo() {
        
        //die('actionInfo is dead');
        
        //Nette\Diagnostics\Debugger::$maxDepth = 6;
        
        $allRegistrations = $this->presenter->umbroModel->getAllRegistrations();
//        dump($allRegistrations);
//        die();
        $template = new \Nette\Templating\FileTemplate(APP_DIR . '/templates/emailTemplates/summary.latte');
        $template->registerFilter(new Nette\Latte\Engine);
        $template->registerHelperLoader('Nette\Templating\Helpers::loader');
        
        $template->allRegistrations = $allRegistrations;
        
//        echo $template;
//        die();
        
        // jarolim@umbro.cz
        //die('Odkomentovat die()!!!!  actionInfo -> jarolim@umbro.cz');
        $r = array(
                'jan.schoepp@netstars.cz',
                'jurasm2@gmail.cz'
        );
        $this->presenter->mailerService->sendMail($r, $template, 'Průběžný seznam registrací');
        //$this->presenter->mailerService->sendMail('jarolim@umbro.cz', $template, 'Průběžný seznam registrací');
        die();
    }
    
        
    
}
