<?php

/**
 * Homepage presenter.
 */
class CronPresenter extends BasePresenter {

   
    
    public function actionDefault() {
        
        // mailing 1
        $usersForMailing1 = $this->presenter->umbroModel->getUsersForMailing1();
        
        if ($usersForMailing1) {
            $this->presenter->umbroModel->setMailing1AsSent(array_keys($usersForMailing1));
           
            foreach ($usersForMailing1 as $user) {                
                $template = new \Nette\Templating\FileTemplate(APP_DIR . '/templates/emailTemplates/mailing1.latte');
                $template->registerFilter(new Nette\Latte\Engine);
                $template->basePath = $this->baseUri;
                $this->presenter->mailerService->sendMail($user['email'], $template, 'AKCE PRÁVĚ ZAČALA!');
            } 
        }

        
        
        // mailing 2
        $usersForMailing2 = $this->presenter->umbroModel->getUsersForMailing2();
        
        if ($usersForMailing2) {
            $this->presenter->umbroModel->setMailing2AsSent(array_keys($usersForMailing2));
           
            foreach ($usersForMailing2 as $user) {                
                $template = new \Nette\Templating\FileTemplate(APP_DIR . '/templates/emailTemplates/mailing2.latte');
                $template->registerFilter(new Nette\Latte\Engine);
                $template->basePath = $this->baseUri;
                $this->presenter->mailerService->sendMail($user['email'], $template, 'UŽ JEN 4 DNY!');
            } 
        }
        
        
        
        
        
        
        
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
        $this->presenter->mailerService->sendMail('jarolim@umbro.cz', $template, 'Průběžný seznam registrací');
        
        
        
        
        $this->sendThanks();
        
        die();
        
    }
    
    
    public function actionTest() {
        $offset = $this->getParam('offset') ?: 0;
        
//        dump($offset);
//        die();
        
        $members = $this->presenter->umbroModel->getAllUsers($offset, 100);
        
        $template = new \Nette\Templating\FileTemplate(APP_DIR . '/templates/emailTemplates/xmas.latte');
        $template->registerFilter(new Nette\Latte\Engine);
        $template->basePath = $this->baseUri;
        
        if (!empty($members)) {
            
            foreach ($members as $member) {
                $email = $member['email'];
                dump('sending to '.$email);
                $this->presenter->mailerService->sendMail($email, $template, 'Dárek od V.I.P. UMBRO HAPPY DAYS');
            }
            
        }
        
        //dump($members);
                
        die();
    }
    
    public function actionSundayInfo() {
        
        //Nette\Diagnostics\Debugger::$maxDepth = 6;
        
        $closestRegistrations = $this->presenter->umbroModel->getClosestRegistrations();
        
        $data = NULL;
        if (!empty($closestRegistrations)) {
            $data[] = reset($closestRegistrations);
        }
        
//        dump($data);
//        die();
        $template = new \Nette\Templating\FileTemplate(APP_DIR . '/templates/emailTemplates/sundaySummary.latte');
        $template->registerFilter(new Nette\Latte\Engine);
        $template->registerHelperLoader('Nette\Templating\Helpers::loader');
        
        $template->allRegistrations = $data;
        
//        echo $template;
//        die();
//        
//        factorystore@umbro.cz
        //die('Odkomentovat die()!!!!   actionSundayInfo -> factorystore@umbro.cz');
        $this->presenter->mailerService->sendMail('factorystore@umbro.cz', $template, 'Seznam registrací na nadcházející termín');
        die();
        
    }
    
    
    private function _isMonday() {
        return date('D') == 'Mon';
    }
    
    
    public function sendThanks($eventId = NULL, $offset = NULL) {
        //        dump($eventId);
//        die();
        
        
//        dump($eventId, $offset);
//        die();
        
        $actualEvent = $this->presenter->umbroModel->getActualEvent();
        // pokud je pondeli a poslední událost neskočila před více než 5 dny....
        if ($this->_isMonday() && strtotime($actualEvent['end_date']) - strtotime("+5 day") > 0) {
            // vezmi ID predchozi udalosti
            // a vrat vsechny usery
        
            
            $users = $this->umbroModel->getUsersByEventId($eventId ?: $actualEvent['event_id'] - 1, $offset);
//            dump($users);
//            die();
            
            foreach ($users as $user) {
                $template = new \Nette\Templating\FileTemplate(APP_DIR . '/templates/emailTemplates/thanks.latte');
                $template->registerFilter(new Nette\Latte\Engine);
                $template->basePath = $this->baseUri;
                $this->presenter->mailerService->sendMail($user['email'], $template, 'V.I.P. UMBRO HAPPY DAYS');
                //$this->presenter->mailerService->sendMail('jan.schoepp@netstars.cz', $template, 'V.I.P. UMBRO HAPPY DAYS');
                //$this->presenter->mailerService->sendMail('jurasm2@gmail.com', $template, 'V.I.P. UMBRO HAPPY DAYS');
                echo "sent to ".$user['email']."<br />";
                //die();
            }
        }
    }
    
    
//    public function actionTest($eventId = NULL, $offset = NULL) {
//        
//            
//        $this->sendThanks($eventId, $offset);
//        die();
//        
//    }
    
    public function actionInfo() {
        
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
        $this->presenter->mailerService->sendMail('jarolim@umbro.cz', $template, 'Průběžný seznam registrací');
        die();
    }
    
        
    
}
