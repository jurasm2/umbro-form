<?php

namespace Model;

final class UmbroModel extends BaseModel {

   
    public function registerUser($data) {
    
//        dump($data);
//        die();
        
        
        return $this->connection->query('INSERT INTO [reg_users]', $data);
        
    }
    
    
    public function findEventByHash($hash) {
        return $this->connection->query('SELECT * FROM [reg_events] WHERE [hash] = %s LIMIT 1', $hash)->fetch();
    } 
    
    public function getEventMailingFlags() {

        return $this->connection->query('SELECT 
                                        [event_id], 
                                        [mailing1_sent], 
                                        [mailing2_sent] 
                                        FROM 
                                            [reg_users] 
                                        GROUP BY [event_id]
                                    ')->fetchAssoc('event_id');
        
    }
    
    
    private function _getUsersForMailing($offset = 0) {
        return $this->connection->query('SELECT 
                                            *
                                            FROM 
                                                [reg_users] [u] 
                                            JOIN 
                                                [reg_events] [e] 
                                            USING 
                                                ([event_id]) 
                                            WHERE 
                                                DATE_ADD([e].[start_date], INTERVAL %i DAY) = DATE(NOW())
                                                %if
                                                    AND
                                                [mailing1_sent] = 0
                                                %else
                                                    AND
                                                [mailing2_sent] = 0
                                                %end
                                            ', $offset, $offset == 0)->fetchAssoc('user_id');
    }
    
    
    public function getUsersForMailing1() {
        return $this->_getUsersForMailing();
    }

    public function getUsersForMailing2() {
        return $this->_getUsersForMailing(3);
    }
    
    private function _setMailingAsSent($userIds, $attrib) {        
        return $this->connection->query('UPDATE [reg_users] SET %n = 1 WHERE [user_id] IN %in', $attrib, $userIds);
    }

    public function setMailing1AsSent($userIds) {
        return $this->_setMailingAsSent((array) $userIds, 'mailing1_sent');
    }
    
    public function setMailing2AsSent($userIds) {
        return $this->_setMailingAsSent((array) $userIds, 'mailing2_sent');
    }
    
    public function getEventById($eventId) {
        return $this->connection->fetch('SELECT * FROM [reg_events] WHERE [event_id] = %i', $eventId);
    }
    
    
    public function getAllUsers($offset, $limit = 100) {
        
        return $this->connection->fetchAll('SELECT * FROM [reg_users] LIMIT %i,%i', $offset, $limit);
        
    }
    
    
    public function getAllRegistrations() {
        return $this->connection->query('SELECT 
                                            [u].[user_id],
                                            [u].[name],
                                            [u].[surname],
                                            [u].[email],
                                            [e].[start_date],
                                            [e].[end_date],
                                            [e].[event_id]
                                            FROM 
                                                [reg_events] [e]
                                            LEFT JOIN
                                                [reg_users] [u]
                                            USING
                                                ([event_id])
                                            ORDER BY
                                                [e].[start_date] ASC,
                                                [u].[surname] ASC
                                            ')->fetchAssoc('event_id,=,user_id');
    }
    
    public function getClosestRegistrations() {
        return $this->connection->query('SELECT 
                                            [u].[user_id],
                                            [u].[name],
                                            [u].[surname],
                                            [u].[email],
                                            [e].[start_date],
                                            [e].[end_date],
                                            [e].[event_id]
                                            FROM 
                                                [reg_events] [e]
                                            LEFT JOIN
                                                [reg_users] [u]
                                            USING
                                                ([event_id])
                                            WHERE
                                                [e].[start_date] > NOW()
                                            ORDER BY
                                                [e].[start_date] ASC,
                                                [u].[surname] ASC
                                            ')->fetchAssoc('event_id,=,user_id');
    }
    
    public function getActualEvent() {
        return $this->connection->fetch('SELECT * FROM [reg_events] WHERE [start_date] <= NOW() ORDER BY [start_date] DESC LIMIT 1');
    }
    
    public function getUsersByEventId($eventId, $offset = NULL) {
        return $this->connection->fetchAll('SELECT * FROM [reg_users] WHERE [event_id] = %i %if LIMIT %i,50',$eventId, $offset !== NULL, $offset);
    }
    
    
}