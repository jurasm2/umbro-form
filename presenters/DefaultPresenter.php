<?php

/**
 * Homepage presenter.
 */
class DefaultPresenter extends BasePresenter {

    
    private $event = NULL;
    
    public function createComponentRegisterForm($name) {
        return new Components\Forms\SpringTimeRegisterForm($this, $name);
        
    }
    
    /**
     * Sign off of a user
     * @param string $signoffHash
     */
    public function actionSignOff($signoffHash) {
        $user = $this->umbroModel->getUserBySignoffHash($signoffHash);

        if (!$user) {
            $this->flashMessage('Neplatný kód', 'error');
            $this->redirect(301, 'blank');
        }
        
        if (!$user['is_active']) {
            $this->flashMessage('Váš email byl již odstraněn z naší databáze', 'error');
            $this->redirect(301, 'blank');
        }
        
        $this->presenter->umbroModel->signOffUser($user['user_id']);
        $this->flashMessage('Váš email byl odstraněn z naší databáze');
        $this->redirect(301, 'blank');
        
    }
    
    /**
     * Quick sign in of a member
     * @param type $signinHash
     */
    public function actionSignIn($signinHash) {
        $user = $this->umbroModel->getUserBySigninHash($signinHash);

        if (!$user) {
            $this->flashMessage('Neplatný kód', 'error');
            $this->redirect(301, 'blank');
        }
        
        if ($user['is_active']) {
            $this->flashMessage('Váš email je již registrován');
            $this->redirect(301, 'blank');
        }
        
        $this->presenter->umbroModel->signInUser($user['user_id']);
        $this->flashMessage('Váš email byl úspěšně registrován');
        $this->sendConfirmMail($user['email']);
        $this->redirect(301, 'blank');
        
        
    }
    
    public function sendConfirmMail($email) {
         // send confirm mail
        $templateParams = array(
                            'basePath'  =>  $this->presenter->baseUri
        );
        
        $attachments = array(
                        WWW_DIR . '/attachments/vip-pozvanka-2013.pdf',
                        WWW_DIR . '/attachments/pruvodni-dopis.doc'
        );
        
        $this->sendUserMail($email, 'confirm.latte', 'Úspěšná registrace na V.I.P. UMBRO SPRINGTIME', $templateParams, $attachments);
    }
    
    
    
    public function renderSent($eventId) {
        
        $event = $this->presenter->umbroModel->getEventById($eventId);
        
        $this->template->event = $event;
        
    }
    
    public function renderDefault($hash) {

        if (!$hash) {
            throw new Nette\Application\BadRequestException('No hash provided');
        }
        
        $event = $this->presenter->umbroModel->findEventByHash($hash);
        
        if (!$event) {
            throw new Nette\Application\BadRequestException('Invalid link');
        }
        
        $this->event = $event;
        $this->template->event = $event;
        
	}

    public function getEvent() {
        return $this->event;
    }
        
    
}
