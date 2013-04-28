<?php

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter {

    public $baseUri;
    
    public function createComponentLister($name) {
        return new Components\Lister($this, $name);
    }
    
    public function startup() {
        parent::startup();
        
        $baseUrl = rtrim($this->presenter->getHttpRequest()->getUrl()->getBaseUrl(), '/');
        $this->baseUri = preg_replace('#https?://[^/]+#A', '', $baseUrl);

    }
    
    public function __call($methodName, $args) {
        if (preg_match('|.*getModel([a-zA-Z0-9]+).*|', $methodName, $mtch)) {
            if (class_exists('Models\\' . $mtch[1] . 'Model')) {
                return $this->context->modelLoader->loadModel($mtch[1] . 'Model');
            }
        } else {
            return parent::__call($methodName, $args);
        }
    }
    
    public function &__get($name) {
        if (preg_match('#([[:alnum:]]+Model)#', $name, $matches)) {
            $model = $this->context->modelLoader->loadModel(lcfirst($matches[1]));
            return $model;
        } else if (preg_match('#([[:alnum:]]+)Service#', $name, $matches)) {
            
           
            if ($this->context->hasService($matches[1])) {
                $service = $this->context->$matches[1];
                return $service;
            } else {
                throw new Nette\MemberAccessException("Service with name '$matches[1]' does not exist");
            }
        }
        return parent::__get($name);
    }

    
    /**
     * Send email to user
     * - adds templates params
     * - adds attachemnts
     * 
     * @param string|array $email
     * @param string $templateName
     * @param string $subject
     * @param array $templateParams
     * @param array $attachments
     */
    public function sendUserMail($email, $templateName, $subject, $templateParams = array(), $attachments = array()) {
        $template = new \Nette\Templating\FileTemplate(APP_DIR . sprintf('/templates/emailTemplates/%s', $templateName));
        $template->registerFilter(new Nette\Latte\Engine);
        
        foreach ($templateParams as $k => $v) {
            $template->$k = $v;
        }
        
        $this->presenter->mailerService->sendMail($email, $template, $subject, NULL, $attachments);
    }
    
}
