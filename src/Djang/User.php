<?php
/**
 * $package CRM User
 * PHP version 7
 *
 * @category CRM
 * @package  User
 * @author   jambonbill <jambonbill@gmail.com>
 * @license  https://github.com/jambonbill  Jambon License 1.01
 * @link     https://github.com/jambonbill
 *
 * Base class
 */

namespace Djang;

use PDO;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;


/**
 * User class
 *
 * @category CRM
 * @package  User
 * @author   Jambonbill <jambonbill@gmail.com>
 * @license  https://github.com/jambonbill  Jambon License 1.01
 * @link     https://github.com/jambonbill
 */
class User
{
    private $_Base;


    /**
     * Dependency injection
     *
     * @param Base $B [description]
     */
    public function __construct(Base $B)//bring your own db
    {
        $this->_Base=$B;
        //$this->_user=$B->user();
    }


    /**
     * Return db connector
     * @return [type] [description]
     */
    public function db()
    {
        return $this->_Base->db();
    }


    /**
     * [user_id description]
     *
     * @return [type] [description]
     */
    private function _uid()
    {
        return $this->_Base->userId();
    }


    /**
     * [log description]
     *
     * @return logger
     */
    private function log()
    {
        return $this->_Base->log();
    }


    /**
     * return auth_user record
     *
     * @param integer $id [description]
     *
     * @return [type]      [description]
     */
    public function auth_user($id=0)
    {
        $id*=1;

        if (!$id) {
            return false;
        }

        $sql="SELECT * FROM auth_user WHERE id=$id LIMIT 1;";
        $q=$this->db()->query($sql) or die("Errror:".print_r($this->db()->errorInfo(), true)."<hr />$sql");

        return false;
    }



    /**
     * Return the list of users
     *
     * @return [type] [description]
     */
    public function users()
    {

        $sql="SELECT * FROM auth_user WHERE 1;";
        $q=$this->db()->query($sql) or die("Errror:".print_r($this->db()->errorInfo(), true)."<hr />$sql");

        $dat=[];
        while ($r=$q->fetch(PDO::FETCH_ASSOC)) {
            $dat[]=$r;
        }
        return $dat;
    }


    /**
     * Remove this
     * @return [type] [description]
     */
    public function testlog()
    {
        $this->log()->addInfo(__FUNCTION__, ['user_id'=>333]);
    }


    /**
     * Create a active django/edx user
     * @param  string $email       must be unique, 75 chars max
     * @param  string $first_name  first name, 30 chars max
     * @param  string $last_name   last name, 30 chars max
     * @return integer             user_id
     */
    public function create($email = '', $first_name = '', $last_name = '')
    {

        $this->log()->addInfo(__FUNCTION__."($email,[...])", ['user_id'=>$this->_uid()]);


        // echo "userCreate();\n";
        $email=trim(strtolower($email));

        if (!$email) {//email is the primary identifier in edx
            return false;
        }

        if ($uid = $this->exist($email)) {
            return $uid;
        }

        // first name must be set
        $username='';
        if (!$username) {
            $username=explode("@", $email)[0];// we take the first part of the email as username if we dont have anything better
            //return false;
        }

        // date joined
        $date_joined="NOW()";

        $sql = "INSERT INTO auth_user (username, password, first_name, last_name, email, is_active, date_joined)";
        $sql.=" VALUES (".$this->db()->quote($username).", 'no password', ".$this->db()->quote($first_name).", ".$this->db()->quote($last_name).", '$email', 1, $date_joined);";

        $q=$this->db()->query($sql) or die("Errror:".print_r($this->db()->errorInfo(), true)."<hr />$sql");

        $userid=$this->db()->lastInsertId();

        //$this->userProfileCreate($userid);//not strictly necessary

        return $userid;
    }


    /**
     * Update user record
     * email, first_name, last_name
     * @return [type] [description]
     */
    public function update($user_id=0, $data=[])
    {
        $user_id*=1;

        if (!$user_id) {
            return false;
        }

        //print_r($data);exit;

        $this->log()->addInfo(__FUNCTION__ . "($user_id, data)", ['user_id' => $this->_uid()]);

        $sql="UPDATE auth_user SET email=".$this->db()->quote($data['email']);
        $sql.=", first_name=".$this->db()->quote($data['first_name']);
        $sql.=", last_name=".$this->db()->quote($data['last_name']);
        $sql.=" WHERE id=$user_id LIMIT 1;";

        $this->db()->query($sql) or die($this->db()->errorInfo()[2]."\n$sql");

        $this->updateProfile($user_id, $data);

        return true;
    }


    public function updateProfile($user_id, $data)
    {
        $user_id*=1;

        if (!$user_id) {
            return false;
        }

        $sql="UPDATE auth_user_profile SET aup_updated=NOW(), aup_updater=".$this->_uid();

        if ($data['aup_gender']) {
            $sql.=", aup_gender=".$this->db()->quote($data['aup_gender']);
        }


        if ($data['aup_date_of_birth']) {
            $sql.=", aup_date_of_birth=".$this->db()->quote($data['aup_date_of_birth']);
        }


        $sql.=", aup_nationality=".$this->db()->quote($data['aup_nationality']);
        $sql.=", aup_country=".$this->db()->quote($data['aup_country']);
        $sql.=", aup_phone_number=".$this->db()->quote($data['aup_phone_number']);
        $sql.=", aup_mailing_address=".$this->db()->quote($data['aup_mailing_address']);
        $sql.=" WHERE aup_user_id=$user_id LIMIT 1;";

        $this->db()->query($sql) or die($this->db()->errorInfo()[2]."\n$sql");

        $this->log()->addInfo(__FUNCTION__ . "($user_id, data)", ['user_id' => $this->_uid()]);

        return true;
    }


    /**
     * Active a user record
     *
     * @param  integer $user_id [description]
     *
     * @return [type]           [description]
     */
    public function activate($user_id=0)
    {
        $user_id *= 1;

        if (!$user_id) {
            return false;
        }

        $sql = "UPDATE `auth_user` SET is_active=1 WHERE id='$user_id' LIMIT 1;";
        $this->db()->query($sql) or die($this->db()->errorInfo()[2]."\n$sql");

        $this->log()->addInfo(__FUNCTION__ . "($user_id)", ['user_id' => $this->_uid()]);

        return true;
    }


    /**
     * Deactivate user record
     *
     * @param  integer $user_id [description]
     *
     * @return [type]           [description]
     */
    public function deactivate($user_id=0)
    {
        $user_id *= 1;

        if (!$user_id) {
            return false;
        }

        $sql = "UPDATE `auth_user` SET is_active=0 WHERE id='$user_id' LIMIT 1;";
        $this->db()->query($sql) or die($this->db()->errorInfo()[2]."\n$sql");
        $this->log()->addInfo(__FUNCTION__ . "($user_id)", ['user_id' => $this->_uid()]);

        return true;
    }


    /**
     * Create user profile record
     * @param  integer $user_id [description]
     * @return [type]           [description]
     */
    public function userProfileCreate($user_id=0)
    {
        $user_id*=1;

        if ($user_id<1) {
            return false;
        }

        // Make sure the profile is note already created //
        $sql="SELECT aup_id FROM `auth_user_profile` WHERE aup_user_id=$user_id LIMIT 1;";
        $q=$this->db()->query($sql) or die("Error:" . print_r($this->db()->errorInfo(),1) . "<hr />$sql");

        if ($r=$q->fetch()) {
            return +$r['aup_id'];
        }

        // Create profile //
        $sql = "INSERT INTO `auth_user_profile` (aup_user_id, aup_updated)";
        $sql .= " VALUES (" . $this->db()->quote($user_id) . ", NOW() );";

        $this->db()->query($sql) or die("Error:" . print_r($this->db()->errorInfo(),1) . "<hr />$sql");
        $ID = $this->db()->lastInsertId();

        $this->log()->addInfo(__FUNCTION__."(".$user_id.")", ['user_id' => $this->_uid()]);
        return $ID;
    }




    /**
     * Return the user_id, for a given username or email adress
     * @return [type] [description]
     */
    public function exist($username='')
    {
        $username=trim($username);

        //detect email//
        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            //a valid email address
            $sql="SELECT id FROM auth_user WHERE email LIKE ".$this->db()->quote($username)." LIMIT 1;";
        } else {
            //not a email address
            $sql="SELECT id FROM auth_user WHERE username LIKE ".$this->db()->quote($username)." LIMIT 1;";
        }

        $q=$this->db()->query($sql) or die(print_r($this->db()->errorInfo(), true));

        $r=$q->fetch(PDO::FETCH_ASSOC);
        if ($r) {
            return $r['id'];
        }
        return false;
    }


    /**
     * Update user Password
     * Password must be encrypted first !
     *
     * @param integer $user_id [description]
     * @param string  $pass    [description]
     *
     * @return [type]           [description]
     */
    public function updatePassword($user_id = 0, $password = '')
    {
        $user_id*=1;

        if (!$password || !$user_id) {
            return false;
        }

        $sql = "UPDATE auth_user SET password=".$this->db()->quote($password)." WHERE id=$user_id LIMIT 1;";
        $q=$this->db()->query($sql) or die(print_r($this->db()->errorInfo(), true));

        $this->log()->addInfo(__FUNCTION__."($user_id,password)", ['user_id'=>$this->_uid()]);

        return true;
    }




    /**
     * Return the complete user profile
     * @param  integer $user_id [description]
     * @return [type]           [description]
     */
    public function profile($user_id=0)
    {
        $user=[];
        $user=$this->_Base->authUser($user_id);

        if (!$user) {
            return false;
        }

        $profile=$this->_Base->authUserProfile($user['id']);

        if ($profile) {
            $user=array_merge($user,$profile);
        }

        return $user;
    }

}