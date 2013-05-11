<?php

namespace Model;

final class UmbroModel extends BaseModel {

   
    public function registerUser($data) {
        
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
    
    
//    private function _getUsersForMailing($offset = 0) {
//        return $this->connection->query('SELECT 
//                                            *
//                                            FROM 
//                                                [reg_users] [u] 
//                                            JOIN 
//                                                [reg_events] [e] 
//                                            USING 
//                                                ([event_id]) 
//                                            WHERE 
//                                                DATE_ADD([e].[start_date], INTERVAL %i DAY) = DATE(NOW())
//                                                %if
//                                                    AND
//                                                [mailing1_sent] = 0
//                                                %else
//                                                    AND
//                                                [mailing2_sent] = 0
//                                                %end
//                                            ', $offset, $offset == 0)->fetchAssoc('user_id');
//    }
//    
//    
//    public function getUsersForMailing1() {
//        return $this->_getUsersForMailing();
//    }
//
//    public function getUsersForMailing2() {
//        return $this->_getUsersForMailing(3);
//    }
    
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
    
    
//    public function getAllRegistrations() {
//        return $this->connection->query('SELECT 
//                                            [u].[user_id],
//                                            [u].[name],
//                                            [u].[surname],
//                                            [u].[email],
//                                            [e].[start_date],
//                                            [e].[end_date],
//                                            [e].[event_id]
//                                            FROM 
//                                                [reg_events] [e]
//                                            LEFT JOIN
//                                                [reg_users] [u]
//                                            USING
//                                                ([event_id])
//                                            ORDER BY
//                                                [e].[start_date] ASC,
//                                                [u].[surname] ASC
//                                            ')->fetchAssoc('event_id,=,user_id');
//    }

    public function getAllRegistrations() {
        return $this->connection->query('SELECT 
                                            *
                                            FROM 
                                                [reg_users]
                                            WHERE
                                                [is_active] = 1
                                            ORDER BY
                                                [surname] ASC
                                            ')->fetchAll();
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
    
    /**
     * Cli section
     */
    
    public function generateUserHash($user, $code) {
        return sha1(sprintf('%d-%s-%s-%s-%d', $user['user_id'], $user['name'], $user['surname'], $code, time()));
    }
    
    public function generateSignCodes() {
        // get all users and generate codes
        $users = $this->connection->fetchAll('SELECT * FROM [reg_users]');
        
        if ($users) {
            foreach ($users as $user) {
                $data = array(
                            'signin_hash%s'     =>  $this->generateUserHash($user, 'sign_in'),
                            'signoff_hash%s'    =>  $this->generateUserHash($user, 'sign_off')
                );
                
                $this->connection->query('UPDATE [reg_users] SET', $data, 'WHERE [user_id] = %i', $user['user_id']);
            }
        }
        
    }
    
    public function getNumberOfUsersWithoutSignInCode() {
	return $this->connection->fetchSingle('SELECT COUNT(*) FROM [reg_users] WHERE [signin_hash] IS NULL');
    }
    
    public function getNumberOfAllUsers($onlyActive = FALSE) {
        return $this->connection->fetchSingle('SELECT COUNT(*) FROM [reg_users] %if WHERE [is_active] = 1', $onlyActive);
    }
    
    // invitation part    
    public function getUninvitedUsers($limit) {
        return $this->connection->query('SELECT * FROM [reg_users] WHERE [invitation_sent] = 0 AND [signin_hash] IS NOT NULL LIMIT %i', $limit)->fetchAssoc('user_id');
    }
    
    public function setInvitationMailingAsSent($userIds) {
        $this->_setMailingAsSent($userIds, 'invitation_sent');
    }
    
    // sign off procedure
    public function getUserBySignoffHash($signoffHash) {
        return $this->connection->fetch('SELECT * FROM [reg_users] WHERE [signoff_hash] = %s', $signoffHash);
    }
    
    public function signOffUser($userId) {
        return $this->connection->query('UPDATE [reg_users] SET [is_active] = 0 WHERE [user_id] = %s', $userId);
    }
    
    // sign in rocedure
    public function getUserBySigninHash($signinHash) {
        return $this->connection->fetch('SELECT * FROM [reg_users] WHERE [signin_hash] = %s', $signinHash);
    }
    
    public function signInUser($userId) {
        return $this->connection->query('UPDATE [reg_users] SET [is_active] = 1 WHERE [user_id] = %s', $userId);
    }
    
    
    // mailings
    public function getUsersForMailing($flagName, $limit) {
        return $this->connection->query('SELECT * FROM [reg_users] WHERE %n = 0 AND [is_active] = 1 LIMIT %i', $flagName, $limit)->fetchAssoc('user_id');
    }

    // for test pusposes
    public function getTestUsersFormMailing($flagName, $ids) {
	return $this->connection->query('SELECT * FROM [reg_users] WHERE %n = 0 AND [user_id] IN %in', $flagName, (array) $ids)->fetchAssoc('user_id');
    }
    
    public function setMailingAsSent($flagName, $userIds) {        
        return $this->connection->query('UPDATE [reg_users] SET %n = 1 WHERE [user_id] IN %in', $flagName, $userIds);
    }
    
}