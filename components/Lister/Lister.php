<?php

namespace Components;

use Nette,
    Nette\Diagnostics\Debugger;

class Lister extends \Nette\Application\UI\Control {
    
    const READY = 'ready',
          BUSY  = 'busy',
          ERROR = 'error';
    
    /**
     * Listing limit
     * 
     * @var int
     */
    public static $limit = 100;
    
    /**
     * Table name for lister
     * 
     * @var string
     */
    public static $tableName = 'reg_tasklist';
    
    public static $verbose = FALSE;
    
    public function run($taskName, $callback) {
        
        // validate callback
        if (!($callback instanceof Nette\Callback)) {
            throw new Nette\InvalidStateException(sprintf("Task '%s' must be an instance of Nette\\Callback", $taskName));
        }
        
        $currentTask = $this->presenter->listerModel->getTask($taskName);
        
        // validate current task
        if ($currentTask['status'] == self::BUSY) {
            // previous task did not finish
            // limit is too high or something worse happened -> set task as error
            // and do not do anything with this task            
            
            $this->presenter->listerModel->updateTask($taskName, array('status' => Lister::ERROR));
            
            $message = sprintf("Task '%s' could not be scheduled. Iteration did not finished yet.", $taskName);
            $this->logInfoMessage($message);
            throw new Lister\PreviousOperationDidNotFinishException($message);
        } else if ($currentTask['status'] == self::ERROR) {
            // nothing can be done....
            die();
        }
        
        if ($currentTask['completed'] == 1) {
            printf("Task '%s' is already completed", $taskName);
            die();
        }
        
        
        // set status of current task as 'BUSY'... 
        $this->presenter->listerModel->updateTask($taskName, array('status' => Lister::BUSY));

        // run the callback...
        list($currentItems, $allTotalItems) = $callback->invoke(self::$limit);

        $data = array();
        if ($currentItems == 0) {
            // current task has been FULLY completed
            $data = array(
                        'status%s'              =>  Lister::READY,
                        'completed%i'           =>  1,
                        'progress%s'            =>  '100%'
            );
            // log it
            $this->logInfoMessage(sprintf("Task '%s' fully completed", $taskName));
        } else {
            // current task has been completed only PARTIALLY
            $newIteration = $currentTask['iteration_number']+1;
            $newTotalItems = $currentTask['total_items']+$currentItems;
            $newProgress = sprintf('%.1f%s', (($newTotalItems / ($allTotalItems + 0.00001)) * 100), '%');
            $data = array(
                        'status%s'              =>  Lister::READY,
                        'iteration_number%i'    =>  $newIteration,
                        'total_items%i'         =>  $newTotalItems,
                        'progress%s'            =>  $newProgress
            );
            // log it
            $this->logInfoMessage(sprintf("Task '%s' partially completed (%s), run no. %d, items [%d/%d], total [%d/%d] ", 
                                                $taskName, 
                                                $newProgress,
                                                $newIteration, 
                                                $currentItems,
                                                self::$limit,
                                                $newTotalItems,
                                                $allTotalItems
                                        )
                                );
        }

        $data['last_run%t'] = new \DateTime;
//        dump($data);
//        die();
        
        $this->presenter->listerModel->updateTask($taskName, $data);

        // and im ready for the next iteration...

                
    }
    
    public function reset() {
        $this->presenter->listerModel->reset();
        printf('Lister is empty');
        die();
    }
    
    private function logInfoMessage($infoMessage) {
        //$infoMessage .= sprintf(", limit = %d", self::$limit);
        
        if (self::$verbose)
            echo $infoMessage;
        
        Debugger::log($infoMessage, Debugger::INFO);
    }

    
    
}

