<?php

namespace Model;

use Components\Lister;

final class ListerModel extends BaseModel {
   
    public function createNewTask($taskName) {
        $data = array(
                    'task_name' =>  $taskName,
        );
        $this->connection->query('INSERT INTO %n %v', Lister::$tableName, $data);
    }
                
    public function getTask($taskName) {
        
        $where = array(
                    'task_name%s'   =>  $taskName
        );
        
        $task = $this->connection->fetch("SELECT * FROM %n WHERE %and LIMIT 1", Lister::$tableName, $where);
        
        if (!$task)
            $this->createNewTask($taskName);
        
        return $this->connection->fetch("SELECT * FROM %n WHERE %and LIMIT 1", Lister::$tableName, $where);
    }
    
    public function updateTask($taskName, $data) {
        return $this->connection->query('UPDATE %n SET %a WHERE [task_name] = %s', Lister::$tableName, $data, $taskName);
    }
    
    public function reset() {
        
        $data = array(
                    'mailing1_sent' => 0,
                    'mailing2_sent' => 0,
                    'mailing3_sent' => 0,
                    'invitation_sent' => 0
                    );
        
        $this->connection->query('UPDATE [reg_users] SET', $data);
        
        return $this->connection->query('TRUNCATE %n', Lister::$tableName);
    }
    
}

