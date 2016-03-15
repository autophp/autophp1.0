<?php
namespace Users\Model;

use Think\Model;

class UsersModel extends Model
{

    const table_Users = 'Users';

    protected $insertFields = 'filed1,field2';
    protected $updateFields = 'filed1,field2';

    public function create_Users()
    {
        if($this->create())
        {
            return $this->add();
        }
        else
        {
            return false;
        }
    }

    public function update_users()
    {
        if($this->create())
        {
            return $this->save();
        }
        else
        {
            return false;
        }
    }

    public function get_Userslist()
    {
        return $this->select();
    }

    public function delete_Users($con)
    {
        return $this->where($con)->delete();
    }


    public function get_Users_sum($con, $field)
    {
        return $this->where($con)->sum($field);
    }

    public function get_Users_count($con, $field)
    {
        return $this->where($con)->count($field);
    }

}