<?php
/**
 * $package CRM
 * PHP version 7
 *
 * @category CRM
 * @package  PackageName
 * @author   jambonbill <jambonbill@gmail.com>
 * @license  https://github.com/jambonbill  Jambon License 1.01
 * @link     https://github.com/jambonbill
 *
 * Base class
 */

namespace Djang;

use PDO;
use Exception;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use MySQLHandler\MySQLHandler;

/**
 * This Base class deal with db connection and the basic user identificaton.
 *
 * @brief Jambonbill Djang base functions
 */
class Base
{
    private $_db;
    private $_UD;
    private $_config;
    private $_user;
    private $log = null;//logger

    /**
     * Main constructor
     */
    public function __construct($path='')
    {


        if ($path) {
            $config_file_path=realpath($path);
        }else{
            $config_file_path='../../profiles/'.$_SERVER['HTTP_HOST'].'.json';
        }


        if (!is_file($config_file_path)) {
            throw new Exception('Error: no config file "' . $config_file_path . '"');
        } else {
            // Load configuration
            $this->_config = json_decode(file_get_contents($config_file_path));
            $err=json_last_error();
            if($err){
                exit("JSON error: $err\n");
            }
            //print_r($this->_config->pdo);exit;
            $this->_connect();
        }

        //User
        $this->_UD=new UserDjango($this->_db);
        $session = $this->_UD->djangoSession();//

        $this->_user = $this->_UD->auth_user($session['session_data']);

        // Update session keys
        $_SESSION['{email}']=   $this->_user['email'];
        $_SESSION['{username}']=$this->_user['username'];
        $_SESSION['{first_name}']=$this->_user['first_name'];
        $_SESSION['{last_name}']=$this->_user['last_name'];

        // Logger // TODO
        $this->log = new Logger('djang');

        $table='djang_log';
        if (isset($this->_config->logger->table)) {
            $table=$this->_config->logger->table;
        }

        $mySQLHandler = new MySQLHandler($this->_db, $table, array('userid'), \Monolog\Logger::DEBUG);

        $this->log->pushHandler($mySQLHandler);//, $additionalFields

        // logger
        //$this->log->addInfo("(test)", ['user_id' => 333]);//test logger
    }


    /**
     * Db connmection
     *
     * @return bool [success]
     */
    private function _connect()
    {

        $db_host = $this->_config->pdo->host;
        $db_name = $this->_config->pdo->name;
        $db_driver=$this->_config->pdo->driver;
        $db_user = $this->_config->pdo->user;
        $db_pass = $this->_config->pdo->pass;

        try {
            $dsn = $db_driver . ":host=" . $db_host . ";dbname=" . $db_name . ";charset=utf8";
            $this->_db = new PDO($dsn, $db_user, $db_pass);
        } catch (PDOException $e) {
            self::$failed = true;
            echo "<li>" . $e->getMessage();
            return false;
        }
        return true;
    }

    /**
     * [db description]
     *
     * @return [type] [description]
     */
    public function db()
    {
        return $this->_db;
    }


    public function config()
    {
        return $this->_config;
    }


    /**
     * Return logger
     *
     * @return [type] [description]
     */
    public function log()
    {
        return $this->log;
    }


    /**
     * Return Userdjango
     *
     * @return [type] [description]
     */
    public function UD()
    {
        return $this->_UD;
    }


    /**
     * End user session
     *
     * @return [type] [description]
     */
    public function logout()
    {
        $this->_UD->logout();
        return true;
    }


    /**
     * [useronly description]
     *
     * @return [type] [description]
     */
    public function useronly()
    {
        if (!$this->_user) {
            echo "user only";
            exit;
        }
    }


    /**
     * [staffOnly description]
     *
     * @return [type] [description]
     */
    public function staffOnly()
    {
        if (!$this->is_staff()) {
            echo "error: staff only";
            exit;
        }
    }


    /**
     * Return current user record
     *
     * @return [type] [description]
     */
    public function user()
    {
        return $this->_user;
    }


    /**
     * [user_id description]
     *
     * @return [type] [description]
     */
    public function userId()
    {
        return $this->_user['id'];
    }


    /**
     * [is_staff description]
     *
     * @return boolean [description]
     */
    public function isStaff()
    {
        return $this->_user['is_staff'];
    }


    /**
     * [is_active description]
     *
     * @return boolean [description]
     */
    public function isActive()
    {
        return $this->_user['is_active'];
    }


    /**
     * [is_superuser description]
     *
     * @return boolean [description]
     */
    public function isSuperuser()
    {
        return $this->_user['is_superuser'];
    }



    /**
     * Return a user record
     *
     * @param integer $id [The user id]
     *
     * @return [type]      [description]
     */
    public function authUser($id=0)
    {
        $id*=1;

        if (!$id) {
            return [];
        }

        $sql="SELECT * FROM auth_user WHERE id=$id LIMIT 1;";
        $q=$this->db()->query($sql) or die(print_r($this->db()->errorInfo(), true) . "<hr />$sql");
        $r=$q->fetch(\PDO::FETCH_ASSOC);

        if ($r) {
            return $r;
        }

        return [];
    }

    public function authUserProfile($uid=0)
    {
        $uid*=1;

        if (!$uid) {
            return [];
        }

        $sql="SELECT * FROM auth_user_profile WHERE aup_user_id=$uid LIMIT 1;";
        $q=$this->db()->query($sql) or die(print_r($this->db()->errorInfo(), true) . "<hr />$sql");
        $r=$q->fetch(\PDO::FETCH_ASSOC);

        if ($r) {
            return $r;
        }

        return [];
    }


    /**
     * Perform a few controls to be on the safe side
     *
     * @return bool [description]
     */
    public function ctrl()
    {
        return true;
    }



    /**
     * Return list of countries, country code, nationalities
     * @return [type] [description]
     */
    public function countries()
    {

        $sql="SELECT gc_code2 AS code, gc_name as name, gc_nationality as nationality FROM `geo_country` WHERE gc_id>0 ORDER BY gc_name;";
        $q=$this->db()->query($sql) or die("Error:" . print_r($this->db()->errorInfo(),1) . "<hr />$sql");

        $dat=[];
        while($r=$q->fetch(PDO::FETCH_ASSOC)){
            $dat[]=$r;
        }
        return $dat;
    }


    /**
     * Return the list of nationalities and associated country codes
     * @return [type] [description]
     */
    public function nationalities()
    {

        $sql="SELECT gc_nationality, gc_code2 AS code FROM `geo_country` WHERE gc_id>0 ORDER BY gc_nationality;";
        $q=$this->db()->query($sql) or die("Error:" . print_r($this->db()->errorInfo(),1) . "<hr />$sql");

        $dat=[];

        while($r=$q->fetch(PDO::FETCH_ASSOC)){
            if(!$r['code'])continue;
            if(!$r['gc_nationality'])continue;
            $dat[$r['code']]=$r['gc_nationality'];
        }
        return $dat;

    }


    /**
     * Return username for given userid
     * @param  integer $user_id [description]
     * @return [type]           [description]
     */
    public function userName($user_id=0)
    {
        $user_id*=1;
        if(!$user_id){
            return false;
        }

        $sql="SELECT username FROM `auth_user` WHERE id=$user_id LIMIT 1;";
        $q=$this->db()->query($sql) or die("Error:" . print_r($this->db()->errorInfo(),1) . "<hr />$sql");

        $r=$q->fetch(PDO::FETCH_ASSOC);
        return $r['username'];
    }


    public function authUsers($userids=[])
    {
        //TODO
    }

}