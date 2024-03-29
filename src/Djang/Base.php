<?php
/**
 * Djang Base
 * PHP version 7+
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
use PDOException;
use Exception;
use Monolog\Logger;
use MySQLHandler\MySQLHandler;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpKernel\Kernel;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;



/**
 * This Base class deal with db connection and the basic user identificaton.
 *
 * @brief Jambonbill Djang base functions
 */
class Base extends Kernel
{    

    private $_db;
    private $_UD;
    private $_config;
    private $_user;
    private $log = null;//logger


    public function registerBundles(): array 
    {
        // Register your bundles here if needed
        return [];
    }
    
    public function registerContainerConfiguration(\Symfony\Component\Config\Loader\LoaderInterface $loader) {
        // Load your container configuration if needed
    }

    /**
     * Main constructor
     * config path is optional
     */
    public function __construct()
    {

        //$this->kernel = $kernel;

        //exit($this->kernel->getProjectDir());

        // Create a Dotenv instance
        $dotenv = new Dotenv();
        
        // Load the values from the .env file based on the APP_ENV
        //$env = $_SERVER['APP_ENV'] ?? 'dev';
        
        $dotenv->loadEnv($this->getProjectDir().'/.env');
        $dotenv->loadEnv($this->getProjectDir().'/.env.local');

        //dd($_ENV);
        
        /*
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
        }
        */

        try {
            $this->_config = Yaml::parseFile($this->getProjectDir().'/config/djang.yaml');
            //dd($value);
        } catch (ParseException $e) {
            //printf("Unable to parse the YAML string: %s", $e->getMessage());
        }




        $this->_connect();//DB connection

        //User
        $this->_UD=new UserDjango($this->_db);
        $session = $this->_UD->djangoSession();//
        //dd($session);
        
        if ($session) {
            $this->_user = $this->_UD->auth_user($session['session_data']);
            // Update session keys
            $_SESSION['{email}']=   $this->_user['email'];
            $_SESSION['{username}']=$this->_user['username'];
            $_SESSION['{first_name}']=$this->_user['first_name'];
            $_SESSION['{last_name}']=$this->_user['last_name'];
        }
        

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
        $databaseUrl = $_ENV['DATABASE_URL'] ?? null;
        if ($databaseUrl) {
            // Parse the DATABASE_URL using Symfony's UrlParser
            //$urlParser = new UrlParser();
            $parts = parse_url($databaseUrl);
            //dd($parts);
            $db_name = substr($parts['path'], 1); // mydb
        }

        $db_type = explode(':', $parts['scheme'])[0]; // mysql
        $db_user = $parts['user']; // user
        $db_pass = $parts['pass']; // pass
        $db_host = $parts['host']; // localhost
        $port = $parts['port']; // 3306

        try {
            $dsn = $db_type . ":host=" . $db_host . ";dbname=" . $db_name . ";charset=utf8";
            $this->_db = new PDO($dsn, $db_user, $db_pass);
            $this->_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

        } catch (PDOException $e) {
            //self::$failed = true;
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
     * Log in.
     * @param  string $email    [description]
     * @param  string $password [description]
     * @return [type]           [description]
     */
    public function login(string $email, string $password): bool
    {
        /*
        if (!$email) {
            throw new Exception("Error : no email", 1);
        }

        if (!$password) {
            throw new Exception("Error : no password", 1);
        }
        */

        if ($this->_UD->login($email, $password)) {
            
            $session = $this->_UD->djangoSession();//
            
            if ($session) {
                $this->_user = $this->_UD->auth_user($session['session_data']);
                $this->logAgent();
            }
            
        } else {
            return false;//log fail
        }

        $this->log()->addInfo(__FUNCTION__, ['userid' => $this->userId()]);//LOG
        return true;
    }


    /**
     * Login accept staff only
     * @param  string $email    [description]
     * @param  string $password [description]
     * @return [type]           [description]
     */
    public function loginStaff(string $email, string $password)
    {
        if (!$email) {
            throw new Exception("Error : no email", 1);
        }

        if (!$password) {
            throw new Exception("Error : no password", 1);
        }

        if ($this->_UD->loginStaff($email, $password)) {
            $session = $this->_UD->djangoSession();//
            $this->_user = $this->_UD->auth_user($session['session_data']);
            $this->logAgent();
        } else {
            return false;//log fail
        }

        $this->log()->addInfo(__FUNCTION__, ['userid' => $this->userId()]);//LOG
        return true;
    }


    /**
     * Login with the cron user.
     * @return [type] [description]
     */
    public function loginCron()
    {
        //Get creds in config
        $conf=$this->config();

        if (!isset($conf->cron)) {
            throw new Exception("Error : cron profile not found.", 1);
        }

        $user=$conf->cron->username;
        $pass=$conf->cron->password;

        if (!$user) {
            throw new Exception("Error : no email", 1);
        }

        if (!$pass) {
            throw new Exception("Error : no pass", 1);
        }

        return $this->login($user, $pass);
    }



    /**
     * End user session
     *
     * @return [type] [description]
     */
    public function logout()
    {
        $this->log()->addInfo(__FUNCTION__, ['userid' => $this->userId()]);//LOG
        $this->_UD->logout();
        return true;
    }


    /**
     * Log user agent and ip
     * @return [type] [description]
     */
    private function logAgent()
    {
        $agent='';
        $ip='';

        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $agent=substr($_SERVER['HTTP_USER_AGENT'], 0, 255);
        }

        if (php_sapi_name() == "cli") {
            $agent='cli';
        }

        if (isset($_SERVER['REMOTE_ADDR'])) {
            $ip=trim($_SERVER['REMOTE_ADDR']);
        }

        $sql="INSERT INTO auth_user_agent (aua_user_id, aua_user_agent, aua_ip, aua_created) ";
        $sql.="VALUES (".$this->userId().",".$this->db()->quote($agent).",".$this->db()->quote($ip).", NOW());";

        $this->db()->query($sql) or die(print_r($this->db()->errorInfo(), true) . "<hr />$sql");

        $id=$this->db()->lastInsertId();
        return $id;
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
        if (isset($this->_user['id'])) {
            return $this->_user['id'];
        }
        return false;
    }


    /**
     * Alias for userId()
     * @return [type] [description]
     */
    public function uid()
    {
        return $this->userId();
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

    public function authUserProfile(int $uid)
    {

        $sql="SELECT * FROM auth_user_profile WHERE aup_user_id=$uid LIMIT 1;";

        $q=$this->db()->query($sql) or die(print_r($this->db()->errorInfo(), true) . "<hr />$sql");
        $r=$q->fetch(PDO::FETCH_ASSOC);

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
        //must make sure the refferer is local
        $this->log()->addInfo(print_r($_SERVER['PHP_SELF'],1), ['userid' => $this->userId()]);
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