<?php
/**
 * CRM Class Auth
 * Heavily inspired by django auth
 * PHP version 7
 *
 * @category CRM
 * @package  Activities
 * @author   jambonbill <jambonbill@gmail.com>
 * @license  https://github.com/jambonbill  Jambon License 1.01
 * @link     https://github.com/jambonbill
 */

namespace Djang;


use PDO;
use Exception;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;


/**
 * CRM Auth
 * https://docs.djangoproject.com/en/2.1/topics/auth/
 * PHP version 7
 *
 * @category CRM
 * @package  CRM Auth
 * @author   jambonbill <jambonbill@gmail.com>
 */
class Auth
{

    private $_Base=null;


    /**
     * Dependency injection
     *
     * @param Base $B [description]
     */
    public function __construct(Base $B)
    {
        $this->_Base=$B;
    }


    /**
     * Return db connector
     *
     * @return [type] [description]
     */
    public function db()
    {
        return $this->_Base->db();
    }


    private function log()
    {
        return $this->_Base->log();
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



    //auth_group -> the group definitions (group names)



    public function groups()
    {
        $sql='SELECT * FROM auth_group ORDER BY name;';
        $q=$this->db()->query($sql) or die("Error:".print_r($this->db()->errorInfo(), true)."<hr />$sql");

        $dat=[];
        while ($r=$q->fetch(PDO::FETCH_ASSOC)) {
            $dat[]=$r;
        }
        return $dat;
    }



    public function groupStats()
    {

        $sql="SELECT COUNT(*) AS n, group_id FROM auth_user_groups GROUP BY group_id;";
        $q=$this->db()->query($sql) or die("Error:".print_r($this->db()->errorInfo(), true)."<hr />$sql");

        $dat=[];
        while ($r=$q->fetch(PDO::FETCH_ASSOC)) {
            $dat[$r['group_id']]=$r['n'];
        }
        return $dat;
    }



    /**
     * Return group record
     * @param  integer $id [description]
     * @return [type]      [description]
     */
    public function group($id=0)
    {
        $id*=1;

        if (!$id) {
            throw new Exception("Error Processing Request", 1);
        }

        $sql='SELECT * FROM auth_group WHERE id='.$id.' LIMIT 1;';
        $q=$this->db()->query($sql) or die("Error:".print_r($this->db()->errorInfo(), true)."<hr />$sql");

        if ($r=$q->fetch(PDO::FETCH_ASSOC)) {
            return $r;
        }

        return false;
    }


    /**
     * Create auth group
     * @param  string $name [description]
     * @return [type]       [description]
     */
    public function groupCreate($name='')
    {
        $name=trim($name);

        if (!$name) {
            throw new Exception("Error Processing Group name", 1);
        }

        $sql="INSERT INTO auth_group (name) VALUES (".$this->db()->quote($name).");";
        $this->db()->query($sql) or die("Error:".print_r($this->db()->errorInfo(), true)."<hr />$sql");

        $id=$this->db()->lastInsertId();
        return $id;
    }


    /**
     * Delete one group
     * @param  integer $id [description]
     * @return [type]      [description]
     */
    public function groupDelete($id=0)
    {
        $id*=1;

        if (!$id) {
            throw new Exception("Error Processing Request", 1);
        }

        $sql="DELETE FROM auth_group WHERE id=$id LIMIT 1;";
        $this->db()->query($sql) or die("Error:".print_r($this->db()->errorInfo(), true)."<hr />$sql");
        return true;
    }



    /**
     * Return list of group permissions
     * @param  integer $group_id [description]
     * @return [type]            [description]
     */
    public function groupPermissions($group_id=0)
    {
        $group_id*=1;

        if (!$group_id) {
            throw new Exception("Error Processing Request", 1);
        }

        $sql='SELECT * FROM auth_group_permissions WHERE group_id="'.$this->db()->quote($group_id).'";';
        $q=$this->db()->query($sql) or die("Error:".print_r($this->db()->errorInfo(), true)."<hr />$sql");

        $dat=[];
        while ($r=$q->fetch(PDO::FETCH_ASSOC)) {
            $dat[]=$r;
        }
        return $dat;
    }



    /**
     * Return user from given group
     * @param  integer $group_id [description]
     * @return [type]            [description]
     */
    public function groupUsers($group_id=0)
    {
        $group_id*=1;

        if (!$group_id) {
            throw new Exception("Error Processing Request", 1);
        }

        $sql='SELECT user_id FROM auth_user_groups WHERE group_id='.$group_id.';';
        $q=$this->db()->query($sql) or die("Error:".print_r($this->db()->errorInfo(), true)."<hr />$sql");

        $dat=[];
        while ($r=$q->fetch(PDO::FETCH_ASSOC)) {
            $dat[]=+$r['user_id'];
        }
        return $dat;
    }



    /**
     * Add user into auth_group
     * @param  integer $group_id [description]
     * @param  integer $user_id  [description]
     * @return [type]            [description]
     */
    public function groupUserAdd($group_id=0, $user_id=0)
    {
        $group_id*=1;
        $user_id*=1;

        //TODO

        $sql="INSERT INTO auth_user_groups (user_id, group_id) VALUES ($user_id, $group_id);";
        $this->db()->query($sql) or die("Error:".print_r($this->db()->errorInfo(), true)."<hr />$sql");

        $id=$this->db()->lastInsertId();
        return $id;
    }



    /**
     * Remove a group-user
     * @param  integer $id [description]
     * @return [type]      [description]
     */
    public function groupUserDelete($id=0)
    {
        $id*=1;

        if (!$id) {
            throw new Exception("Error Processing Request", 1);
        }

        $sql="DELETE FROM auth_user_groups WHERE id=$id LIMIT 1;";
        $this->db()->query($sql) or die("Error:".print_r($this->db()->errorInfo(), true)."<hr />$sql");
        return true;
    }



    /**
     * Return groups for a given user
     * @param  integer $user_id [description]
     * @return [type]           [description]
     */
    public function userGroups($user_id=0)
    {
        $user_id*=1;

        if (!$user_id) {
            throw new Exception("Error Processing Request", 1);
        }

        $sql='SELECT group_id FROM auth_user_groups WHERE user_id='.$user_id.';';
        $q=$this->db()->query($sql) or die("Error:".print_r($this->db()->errorInfo(), true)."<hr />$sql");

        $dat=[];
        while ($r=$q->fetch(PDO::FETCH_ASSOC)) {
            $dat[]=+$r['group_id'];
        }
        return $dat;

    }


    /**
     * Add a permission to a group
     * @param  integer $group_id      [description]
     * @param  integer $permission_id [description]
     * @return [type]                 [description]
     */
    public function groupPermissionAdd($group_id=0, $permission_id=0)
    {
        $group_id*=1;
        $permission_id*=1;

        if (!$group_id) {
            throw new Exception("Error Processing Request", 1);
        }

        if (!$permission_id) {
            throw new Exception("Error Processing Request", 1);
        }

        //make sure permission exist
        $sql="INSERT INTO auth_group_permissions (group_id, permission_id) VALUES ($group_id, $permission_id);";
        $this->db()->query($sql) or die("Error:".print_r($this->db()->errorInfo(), true)."<hr />$sql");
        return $this->db()->lastInsertId();
    }


    /**
     * Delete a group permission
     * @param  integer $id [description]
     * @return [type]      [description]
     */
    public function groupPermissionDelete($id=0)
    {
        $id*=1;

        if (!$id) {
            throw new Exception("Error Processing Request", 1);
        }

        //make sure permission exist

        $sql="DELETE FROM auth_group_permissions WHERE id=$id LIMIT 1;";
        $this->db()->query($sql) or die("Error:".print_r($this->db()->errorInfo(), true)."<hr />$sql");
        return true;

    }


    //auth_group_permissions

    //auth_permission -> the definiton of a permision, ex: "Can add user", "can send mail"




    //auth_user_groups -> (id, user_id, group_id)



    /**
     * Return permissions for given user.
     * Do NOT return permission from any group
     * @param  integer $user_id [description]
     * @return [type]           [description]
     */
    public function userPermissions($user_id=0)
    {
        $user_id*=1;

        if (!$user_id) {
            throw new Exception("Error Processing Request", 1);
        }

        $sql='SELECT * FROM auth_user_user_permissions WHERE user_id='.$user_id.';';
        $q=$this->db()->query($sql) or die("Error:".print_r($this->db()->errorInfo(), true)."<hr />$sql");

        $dat=[];
        while ($r=$q->fetch(PDO::FETCH_ASSOC)) {
            $dat[]=$r;
        }
        return $dat;
    }


    /**
     * Add a user-permission - auth_user_user_permissions
     * @param  integer $user_id       [description]
     * @param  integer $permission_id [description]
     * @return [type]                 [description]
     */
    public function userPermissionAdd($user_id=0, $permission_id=0)
    {
        $user_id*=1;
        $permission_id*=1;

        if (!$user_id) {
            throw new Exception("Error Processing Request", 1);
        }

        // Check that permission exist
        if(!$this->permission($permission_id)){
            throw new Exception("Error Processing permission", 1);
        }

        $sql="INSERT INTO auth_user_user_permissions (user_id, permission_id) VALUES (".$this->db()->quote($user_id).",".$this->db()->quote($permission_id).");";
        $this->db()->query($sql) or die("Error:".print_r($this->db()->errorInfo(), true)."<hr />$sql");

        return $this->db()->lastInsertId();
    }


    /**
     * Delete a user-permission - auth_user_user_permissions
     * @param  integer $id [description]
     * @return [type]      [description]
     */
    public function userPermissionDelete($id=0)
    {
        $id*=1;

        if (!$id) {
            throw new Exception("Error Processing Request", 1);
        }

        $sql="DELETE FROM auth_user_user_permissions WHERE id=".$id." LIMIT 1;";
        $this->db()->query($sql) or die("Error:".print_r($this->db()->errorInfo(), true)."<hr />$sql");
        return $id;
    }



    /**
     * Return one permission
     * @param  integer $id [description]
     * @return [type]      [description]
     */
    public function permission($id=0)
    {
        $id*=1;

        if (!$id) {
            throw new Exception("Error Processing Request", 1);
        }

        $sql='SELECT * FROM auth_permission WHERE id='.$this->db()->quote($id).' LIMIT 1;';
        $q=$this->db()->query($sql) or die("Error:".print_r($this->db()->errorInfo(), true)."<hr />$sql");

        if($r=$q->fetch(PDO::FETCH_ASSOC)){
            return $r;
        }

        return false;
    }


    /**
     * GET list of permissions
     * @return [type] [description]
     */
    public function permissions()
    {
        $sql='SELECT * FROM auth_permission;';
        $q=$this->db()->query($sql) or die("Error:".print_r($this->db()->errorInfo(), true)."<hr />$sql");

        $dat=[];
        while ($r=$q->fetch(PDO::FETCH_ASSOC)) {
            $dat[]=$r;
        }

        return $dat;
    }



    /**
     * Return the Django content type system,
     * which allows permissions to be associated with models.
     * @return [type] [description]
     */
    private function django_content_type()
    {
        $sql='SELECT * FROM django_content_type;';
        $q=$this->db()->query($sql) or die("Error:".print_r($this->db()->errorInfo(), true)."<hr />$sql");

        $dat=[];
        while ($r=$q->fetch(PDO::FETCH_ASSOC)) {
            $dat[]=$r;
        }
        return $dat;
    }


    /**
     * Return list of content types
     * https://docs.djangoproject.com/en/2.2/ref/contrib/contenttypes/
     * @return [type] [description]
     */
    public function django_content_types()
    {
        $sql='SELECT * FROM django_content_type;';
        $q=$this->db()->query($sql) or die("Error:".print_r($this->db()->errorInfo(), true)."<hr />$sql");

        $dat=[];
        while ($r=$q->fetch(PDO::FETCH_ASSOC)) {
            $dat[]=$r;
        }
        return $dat;
    }


    /**
     * Perform a quick integrity check
     * @return [type] [description]
     */
    public function check_content_types()
    {

        $sql="SELECT table_name FROM information_schema.tables WHERE table_schema=DATABASE();";
        $q=$this->db()->query($sql) or die("Error:".print_r($this->db()->errorInfo(), true)."<hr />$sql");
        $tables=[];
        while ($r=$q->fetch(PDO::FETCH_ASSOC)) {
            $tables[]=$r['table_name'];
        }

        $types=$this->django_content_types();

        foreach($types as $r){
            $tablename=$r['app_label'].'_'.$r['model'];
            if(in_array($tablename, $tables)){
                //echo ".";
            }else{
                echo "Error: table `$tablename` not found\n";

            }
        }

        return false;
    }

}