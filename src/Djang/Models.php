<?php
/**
 * $package CRM
 * PHP version 7
 *
 * @category CRM
 * @package  Models
 * @author   jambonbill <jambonbill@gmail.com>
 * @license  https://github.com/jambonbill  Jambon License 1.01
 * @link     https://github.com/jambonbill
 *
 * Base class
 */

namespace Djang;

use PDO;
use Exception;


/**
 * DB models definition and creation
 *
 * @brief Jambonbill CRM Models functions
 */
class Models
{


    // Tables
    // ---------------
    // auth_group
    // auth_permission
    // auth_user
    // auth_user_agent
    // auth_user_groups
    // auth_user_profile
    // auth_user_user_permissions
    // django_content_type
    // django_session

    private $config;
    private $_db;


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
            $this->config = json_decode(file_get_contents($config_file_path));
            //print_r($this->config->pdo);
            $this->_connect();
        }
    }


    /**
     * Db connection
     *
     * @return bool [success]
     */
    private function _connect()
    {

        $db_host = $this->config->pdo->host;
        $db_name = $this->config->pdo->name;
        $db_driver=$this->config->pdo->driver;
        $db_user = $this->config->pdo->user;
        $db_pass = $this->config->pdo->pass;

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
     * PDO db connector
     *
     * @return [type] [description]
     */
    public function db()
    {
        return $this->_db;
    }



    public function verify()
    {
        echo __FUNCTION__."()\n";

        $files=glob("sql/*.sql");
        foreach($files as $file){
            $sql=file_get_contents($file);
            echo "$sql\n";
        }

        //SELECT 1 FROM testtable LIMIT 1;
        $sql="SELECT DATABASE();";
        $this->db()->query($sql) or die("Error:$sql");
        //$r=$this->
        return true;
    }

    public function createDatabase()
    {
        //todo
    }


    public function showTables()
    {
        $sql="SHOW TABLES;";
        $q=$this->db()->query($sql) or die(print_r($this->db()->errorInfo(), true) . "<hr />$sql");
        $dat=[];
        while($r=$q->fetch()){
            $dat[]=$r;
        }
        return $dat;

    }


    public function createTables()
    {

        echo __FUNCTION__."()\n";

        //get models

        $files=glob(__DIR__."/sql/*.sql");
        //print_r($files);

        foreach($files as $file){
            $sql=file_get_contents($file);
            //echo "$file\n";
            echo "$sql\n";
            $this->db()->query($sql) or die(print_r($this->db()->errorInfo(), true) . "<hr />$sql");
        }

        return true;

    }

}