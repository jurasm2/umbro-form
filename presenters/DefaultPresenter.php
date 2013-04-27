<?php

/**
 * Homepage presenter.
 */
class DefaultPresenter extends BasePresenter {

    
    private $event = NULL;
    
    public function createComponentRegisterForm($name) {
        return new Components\Forms\RegisterForm($this, $name);
        
    }
    
    public function renderSent($eventId) {
        
        $event = $this->presenter->umbroModel->getEventById($eventId);
        
        $this->template->event = $event;
        
    }
    
	public function renderDefault($hash) {
        
//        dump($hash);
//        die();
        
        $array = array();
        
        
//        for ($i = 1; $i <= 5; $i++) {
//            $array['obdobi'.$i] = sha1('obdobi'.$i);
//        }
//        
//        dump($array);
//        die();
        
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
