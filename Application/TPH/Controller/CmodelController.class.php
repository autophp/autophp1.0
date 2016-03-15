<?php
/**
 * Created by codegenerator.
 * User: xiaofeng
 * Date: 16/3/13
 * Time: 下午12:56
 */

namespace TPH\Controller;


use Think\Controller;

/**
 * 模型创建类
 * Class CmodelController
 * @package TPH\Controller
 */
class CmodelController extends Controller
{
    //创建model文件入口
    public function index()
    {
        if(IS_GET)
        {
            //获取模块名
            $this->assign('moduleName', getModuleNameList());
            //获取所有表的名字
            $tableNameList = getTableNameList();
            $this->assign('modelName', $tableNameList);
            //获取默认表的字段名和其他参数
            $default_model = I('model') ? I('model') : $tableNameList[0];

            $this->assign('models', getTableInfoArray($default_model));

            $this->display();
        }
        else
        {
            $moduleName = I('moduleName');
            $modelName = I('tablename');
            $modelName = removeTablePrefix($modelName);

            //获取允许更新的字段

            //获取允许插入的字段
            $insertFieldsStr = insertFieldsStr(I('insert_fields'));
            $updateFilesStr = updateFieldsStr(I('update_fields'));
            self::createModel($moduleName, $modelName, $insertFieldsStr, $updateFilesStr);
        }
    }

    //执行创建model文件
    protected function createModel($moduleName, $tableName, $insertFieldsStr, $updateFilesStr)
    {
        $moduleName = ucfirst($moduleName);

        $tableName = strtolower($tableName);
        $className = ucfirst($tableName);

        $code = $this->get_namespace_code($moduleName);
        $code .= $this->get_use_code();
        $code .= $this->get_class_code(ucfirst($className));
        $code .= $this->get_const_code(ucfirst($tableName));


        $code .= $this->get_divider($tableName, '构造模型的header头');


        $code .= $this->get_insertfield_code($insertFieldsStr);
        $code .= $this->get_updatefield_code($updateFilesStr);


        $code .= $this->get_divider($tableName, '增删改查');

        $code .= $this->get_table_create_code($tableName);
        $code .= $this->get_table_update_code($tableName);
        $code .= $this->get_table_list_code($tableName, 'money');
        $code .= $this->get_table_is_has_code($tableName, array('username' => 1));
        $code .= $this->get_table_delete_code($tableName);

        $code .= $this->get_divider($tableName, '增删改查');


        $code .= $this->get_divider($tableName, '统计总额,最大,最新,平均,总笔数');

        $code .= $this->get_table_sum_code($tableName, 'money');
        $code .= $this->get_table_min_code($tableName, 'money');
        $code .= $this->get_table_max_code($tableName, 'money');
        $code .= $this->get_table_avg_code($tableName, 'money');
        $code .= $this->get_table_count_code($tableName, array('username' => 'comeon'));


        $code .= $this->get_divider($tableName, '统计总额,最大,最新,平均,总笔数');


        $code .= $this->get_divider($tableName, '增加/减少');

        $code .= $this->get_increace_code($tableName, 'money');
        $code .= $this->get_decreace_code($tableName, 'money');

        $code .= $this->get_divider($tableName, '增加/减少');

        //字段今\昨\周\月\总数据

        $code .= $this->get_divider($tableName, '累计数值sum');

        $code .= $this->get_table_sum_today_code($tableName, 'ctime', 'money');
        $code .= $this->get_table_sum_yesterday_code($tableName, 'ctime', 'money');
        $code .= $this->get_table_sum_week_code($tableName, 'ctime', 'money');
        $code .= $this->get_table_sum_month_code($tableName, 'ctime', 'money');
        $code .= $this->get_table_sum_all_code($tableName, 'money');

        $code .= $this->get_divider($tableName, '累计数值sum');


        $code .= $this->get_divider($tableName, '累计笔数count');

        $code .= $this->get_table_count_today_code($tableName, 'ctime', 'money');
        $code .= $this->get_table_count_yesterday_code($tableName, 'ctime', 'money');
        $code .= $this->get_table_count_week_code($tableName, 'ctime', 'money');
        $code .= $this->get_table_count_month_code($tableName, 'ctime', 'money');
        $code .= $this->get_table_count_all_code($tableName, 'money');

        $code .= $this->get_divider($tableName, '累计笔数count');


        $code .= $this->get_divider($tableName, '获取制定字段值/更新制定字段值');
        $code .= $this->get_table_field($tableName, 'sex');
        $code .= $this->get_table_field_change($tableName, 'sex');
        $code .= $this->get_divider($tableName, '获取制定字段值/更新制定字段值');

        $code = $code . "\r\n" . '}';

        dump($code);

        echo createFile($moduleName, $tableName, $code, 'Model');
    }

    const table_Users = 'Users';

    protected function get_divider($tableName = '', $description = '')
    {
        if(!empty($tableName))
        {
            return "\r\n" . "/***************************" . "\r\n" . '*操作表:' . $tableName . ":$description" . "\r\n" . "***************************/";
        }
        else
        {
            return "\r\n" . "/***************************" . "\r\n" . '*' . $description . "\r\n" . "***************************/";
        }
    }

    //获取namespace的code
    protected function get_namespace_code($moduleName)
    {
        return "\r\n" . '<?php' . "\r\n" . 'namespace ' . $moduleName . '\Model;' . "\r\n";
    }

    //获取use的code
    protected function get_use_code()
    {
        return 'use Think\Model;' . "\r\n";
    }

    //获取class的code
    protected function get_class_code($tableName)
    {
        return 'class ' . $tableName . 'Model extends Model{
        ' . "\r\n";
    }

    //获取数据库表名常量
    protected function get_const_code($tableName)
    {
        return 'const table_' . strtolower($tableName) . ' = ' . "'" . $tableName . "';\r\n";
    }

    //获取插入的字段名
    protected function get_insertfield_code($fieldStr)
    {
        if(is_array($fieldStr))
        {
            return "\r\n" . 'protected $insertFields =' . "'" . insertFieldsStr($fieldStr) . "';";
        }
        else
        {
            return "\r\n" . 'protected $insertFields =' . "'" . $fieldStr . "';";
        }
    }

    //获取更新的字段名
    protected function get_updatefield_code($fieldStr)
    {
        if(is_array($fieldStr))
        {
            return "\r\n" . 'protected $updateFields =' . "'" . updateFieldsStr($fieldStr) . "';";
        }
        else
        {
            return "\r\n" . 'protected $updateFields =' . "'" . $fieldStr . "';";
        }
    }

    //获取文件的结束code
    protected function get_file_end_code()
    {

        return "\r\n" . '}' . "\r\n";
    }


    protected function get_table_create_code($tableName)
    {
//        return "\r\n" . 'public function create_' . $tableName . '(){
//        ' . "\r\n" .
//        '$data = array();' . "\r\n" .
//        'return M(self::table_' . $tableName . ')->add($data);'
//        . "\r\n" . '}' . "\r\n";
//

        return "\r\n" . 'public function create_' . $tableName . '(){
        ' . "\r\n" . ' if($this->create())
        {
            return $this->add();
        }
        else
        {
            return false;
        }' . "\r\n" . '}' . "\r\n";

    }

    protected function get_table_delete_code($tableName)
    {
        return 'public function delete_' . $tableName . '($con){
        ' . "\r\n" .
//        '$con = array();' . "\r\n" .
        'return M(self::table_' . $tableName . ')->where($con)->delete();'
        . "\r\n" . '}' . "\r\n";

    }

    protected function get_table_update_code($tableName)
    {
//        return 'public function update_' . $tableName . '(){
//        ' . "\r\n" .
//        '$con = array();' . "\r\n" .
//        'return M(self::table_' . $tableName . ')->where($con)->add($data);'
//        . "\r\n" . '}' . "\r\n";


        return "\r\n" . 'public function update_' . $tableName . '(){
        ' . "\r\n" . ' if($this->create())
        {
            return $this->save();
        }
        else
        {
            return false;
        }' . "\r\n" . '}' . "\r\n";
    }

    protected function get_table_list_code($tableName, $order_fields, $type = 'desc')
    {
//        return "\r\n" . 'public function get_' . $tableName . '_list(){
//        ' . "\r\n" .
//        'return M(self::table_' . $tableName . ')->select();'
//        . "\r\n" . '}' . "\r\n";

        return "\r\n" . 'public function get_' . $tableName . '_list($p=1){
        ' . "\r\n" .
        'return $this->order("' . $order_fields . ' ' . $type . '")->page($p, 15)->select();'
        . "\r\n" . '}' . "\r\n";
    }

    protected function get_table_list_pagination_code($tableName, $order_fields)
    {
        return "\r\n" . 'public function get_' . $tableName . '_list_pagination($order_fields,$p=1){
        ' . "\r\n" .
        'return $this->order("' . $order_fields . 'desc")->page($p,15)->select();'
        . "\r\n" . '}

' . "\r\n";
    }


    protected function get_table_is_has_code($tableName, $arr)
    {
//        $str = arrToStr($arr);

        return "\r\n" . 'public function is_has_target_' . $tableName . '($con){
    ' . "\r\n" .
//        '$con = ' . $str . "\r\n" .
        'return $this->where($con)->find();'
        . "\r\n" . '}' . "\r\n";
    }


    protected function get_table_count_code($tableName)
    {
//        $str = arrToStr($arr);
        return 'public function get_' . $tableName . '_count($con){' . "\r\n" .
        'return $this->where($con)->count();'
        . "\r\n" . '}' . "\r\n";
    }

    protected function get_table_sum_code($tableName, $field)
    {
//        return 'public function get_' . $tableName . '_sum(){' . "\r\n" .
//        '$con = array();' . "\r\n" .
//        'return M(self::table_' . $tableName . ')->where($con)->sum(' . ".$key." . ');'
//        . "\r\n" . '}' . "\r\n";

        return 'public function get_' . $tableName . '_sum_' . $field . '($con){
    ' . "\r\n" .
//        '$con = array();' . "\r\n" .
        'return $this->where($con)->sum("' . $field . '");'
        . "\r\n" . '}' . "\r\n";
    }

    protected function get_table_max_code($tableName, $field)
    {
//        return 'public function get_' . $tableName . '_sum(){' . "\r\n" .
//        '$con = array();' . "\r\n" .
//        'return M(self::table_' . $tableName . ')->where($con)->sum(' . ".$key." . ');'
//        . "\r\n" . '}' . "\r\n";

        return 'public function get_' . $tableName . '_max_' . $field . '($con){
    ' . "\r\n" .
//        '$con = array();' . "\r\n" .
        'return $this->where($con)->max("' . $field . '");'
        . "\r\n" . '}' . "\r\n";
    }

    protected function get_table_min_code($tableName, $field)
    {
//        return 'public function get_' . $tableName . '_sum(){' . "\r\n" .
//        '$con = array();' . "\r\n" .
//        'return M(self::table_' . $tableName . ')->where($con)->sum(' . ".$key." . ');'
//        . "\r\n" . '}' . "\r\n";

        return 'public function get_' . $tableName . '_min_' . $field . '($con){
    ' . "\r\n" .
//        '$con = array();' . "\r\n" .
        'return $this->where($con)->min("' . $field . '");'
        . "\r\n" . '}' . "\r\n";
    }

    protected function get_table_avg_code($tableName, $field)
    {
//        return 'public function get_' . $tableName . '_sum(){' . "\r\n" .
//        '$con = array();' . "\r\n" .
//        'return M(self::table_' . $tableName . ')->where($con)->sum(' . ".$key." . ');'
//        . "\r\n" . '}' . "\r\n";

        return 'public function get_' . $tableName . '_avg_' . $field . '($con){
    ' . "\r\n" .
//        '$con = array();' . "\r\n" .
        'return $this->where($con)->avg("' . $field . '");'
        . "\r\n" . '}' . "\r\n";
    }

    //doing:统计当天
    protected function get_table_sum_today_code($tableName, $time_colume_name, $key)
    {
//        return 'public function get_' . $tableName . '_sum_today($con){' . "\r\n" .
//        '$con["' . $time_colume_name . '"] = array("gt", date("Y-m-d"));' . "\r\n" .
//
//        'return M(self::table_' . $tableName . ')->where($con)->sum("' . $key . ');'
//        . "\r\n" . '}' . "\r\n";


        return 'public function get_' . $tableName . '_sum_today($con){' . "\r\n" .
        '$con["' . $time_colume_name . '"] = array("gt",strtotime(date("Y - m - d")));' . "\r\n" .

        'return $this->where($con)->sum("' . $key . '");'
        . "\r\n" . '}' . "\r\n";
    }

    //doing:统计当天
    protected function get_table_count_today_code($tableName, $time_colume_name, $key)
    {
//        return 'public function get_' . $tableName . '_sum_today($con){' . "\r\n" .
//        '$con["' . $time_colume_name . '"] = array("gt", date("Y-m-d"));' . "\r\n" .
//
//        'return M(self::table_' . $tableName . ')->where($con)->sum("' . $key . ');'
//        . "\r\n" . '}' . "\r\n";


        return 'public function get_' . $tableName . '_count_today($con){' . "\r\n" .
        '$con["' . $time_colume_name . '"] = array("gt",strtotime(date("Y - m - d")));' . "\r\n" .

        'return $this->where($con)->count();'
        . "\r\n" . '}' . "\r\n";
    }

    protected function get_table_sum_yesterday_code($tableName, $time_colume_name, $key)
    {
//        return 'public function get_' . $tableName . '_sum_today($con){' . "\r\n" .
//        '$con["' . $time_colume_name . '"] = array("gt",date("Y - m - d"));' . "\r\n" .
//
//        'return M(self::table_' . $tableName . ')->where($con)->sum("' . $key . ');'
//        . "\r\n" . '}' . "\r\n";


        return 'public function get_' . $tableName . '_sum_yesterday($con){' . "\r\n" .
        '$con["' . $time_colume_name . '"] = array(array("lt", strtotime(date("Y-m-d"))), array("egt", strtotime(date("Y-m-d")) - 3600 * 24));' . "\r\n" .

        'return $this->where($con)->sum("' . $key . '");'
        . "\r\n" . '}' . "\r\n";
    }


    protected function get_table_count_yesterday_code($tableName, $time_colume_name, $key)
    {
//        return 'public function get_' . $tableName . '_sum_today($con){' . "\r\n" .
//        '$con["' . $time_colume_name . '"] = array("gt",date("Y - m - d"));' . "\r\n" .
//
//        'return M(self::table_' . $tableName . ')->where($con)->sum("' . $key . ');'
//        . "\r\n" . '}' . "\r\n";


        return 'public function get_' . $tableName . '_count_yesterday($con){' . "\r\n" .
        '$con["' . $time_colume_name . '"] = array(array("lt", strtotime(date("Y-m-d"))), array("egt", strtotime(date("Y-m-d")) - 3600 * 24));' . "\r\n" .

        'return $this->where($con)->count();'
        . "\r\n" . '}' . "\r\n";
    }


    protected function get_table_sum_week_code($tableName, $time_colume_name, $key)
    {
//        return 'public function get_' . $tableName . '_sum_today($con){' . "\r\n" .
//        '$con["' . $time_colume_name . '"] = array("gt", date("Y-m-d"));' . "\r\n" .
//
//        'return M(self::table_' . $tableName . ')->where($con)->sum("' . $key . ');'
//        . "\r\n" . '}' . "\r\n";


        return 'public function get_' . $tableName . '_sum_week($con){' . "\r\n" .
        '$con["' . $time_colume_name . '"] = array("gt",strtotime(date("Y - m - d"))- 3600 * 24 * 7);' . "\r\n" .

        'return $this->where($con)->sum("' . $key . '");'
        . "\r\n" . '}' . "\r\n";
    }

    protected function get_table_count_week_code($tableName, $time_colume_name, $key)
    {
//        return 'public function get_' . $tableName . '_sum_today($con){' . "\r\n" .
//        '$con["' . $time_colume_name . '"] = array("gt", date("Y-m-d"));' . "\r\n" .
//
//        'return M(self::table_' . $tableName . ')->where($con)->sum("' . $key . ');'
//        . "\r\n" . '}' . "\r\n";


        return 'public function get_' . $tableName . '_count_week($con){' . "\r\n" .
        '$con["' . $time_colume_name . '"] = array("gt",strtotime(date("Y - m - d"))- 3600 * 24 * 7);' . "\r\n" .

        'return $this->where($con)->count();'
        . "\r\n" . '}' . "\r\n";
    }


    protected function get_table_sum_month_code($tableName, $time_colume_name, $key)
    {
//        return 'public function get_' . $tableName . '_sum_today($con){' . "\r\n" .
//        '$con["' . $time_colume_name . '"] = array("gt",date("Y - m - d"));' . "\r\n" .
//
//        'return M(self::table_' . $tableName . ')->where($con)->sum("' . $key . ');'
//        . "\r\n" . '}' . "\r\n";


        return 'public function get_' . $tableName . '_sum_month($con){' . "\r\n" .
        '$con["' . $time_colume_name . '"] = array("gt", strtotime(date("Y-m-d")) - 3600 * 24 * 30);' . "\r\n" .

        'return $this->where($con)->sum("' . $key . '");'
        . "\r\n" . '}' . "\r\n";
    }


    protected function get_table_count_month_code($tableName, $time_colume_name, $key)
    {
//        return 'public function get_' . $tableName . '_sum_today($con){' . "\r\n" .
//        '$con["' . $time_colume_name . '"] = array("gt",date("Y - m - d"));' . "\r\n" .
//
//        'return M(self::table_' . $tableName . ')->where($con)->sum("' . $key . ');'
//        . "\r\n" . '}' . "\r\n";


        return 'public function get_' . $tableName . '_count_month($con){' . "\r\n" .
        '$con["' . $time_colume_name . '"] = array("gt", strtotime(date("Y-m-d")) - 3600 * 24 * 30);' . "\r\n" .

        'return $this->where($con)->count();'
        . "\r\n" . '}' . "\r\n";
    }

    protected function get_table_sum_all_code($tableName, $key)
    {
//        return 'public function get_' . $tableName . '_sum_today($con){' . "\r\n" .
//        '$con["' . $time_colume_name . '"] = array("gt", date("Y-m-d"));' . "\r\n" .
//
//        'return M(self::table_' . $tableName . ')->where($con)->sum("' . $key . ');'
//        . "\r\n" . '}' . "\r\n";


        return 'public function get_' . $tableName . '_sum_all($con){' . "\r\n" .
        'return $this->where($con)->sum("' . $key . '");'
        . "\r\n" . '}' . "\r\n";
    }

    protected function get_table_count_all_code($tableName, $key)
    {
//        return 'public function get_' . $tableName . '_sum_today($con){' . "\r\n" .
//        '$con["' . $time_colume_name . '"] = array("gt", date("Y-m-d"));' . "\r\n" .
//
//        'return M(self::table_' . $tableName . ')->where($con)->sum("' . $key . ');'
//        . "\r\n" . '}' . "\r\n";


        return 'public function get_' . $tableName . '_count_all($con){' . "\r\n" .
        'return $this->where($con)->count();'
        . "\r\n" . '}' . "\r\n";
    }


    protected function get_increace_code($tableName, $field)
    {
        return 'public function inc_' . $tableName . '_' . $field . '($con,$val){' . "\r\n" .
//        '$con = array();' . "\r\n" .
        'return $this->where($con)->setInc(' . "'$field'" . ',' . '$val' . ');'
        . "\r\n" . '}' . "\r\n";
    }

    protected function get_decreace_code($tableName, $field)
    {
        return 'public function dec_' . $tableName . '_' . $field . '($con,$val){' . "\r\n" .
//        '$con = array();' . "\r\n" .
        'return $this->where($con)->setDec(' . "'$field'" . ',' . '$val' . ');'
        . "\r\n" . '}' . "\r\n";
    }

    //获取状态
    protected function get_table_field($tableName, $fieldStr)
    {
        return 'public function get_' . $tableName . '_' . $fieldStr . '($con){' . "\r\n" .
        'return $this->where($con)->getField("' . $fieldStr . '");'
        . "\r\n" . '}' . "\r\n";
    }

    //变更状态,2个选项则变更;多个选项,则自己制定
    protected function get_table_field_change($tableName, $fieldStr)
    {
        return 'public function get_' . $tableName . '_' . $fieldStr . '_change($con,$value){' . "\r\n" .
        '$data=array("' . $fieldStr . '"=>$value' . ');' . "\r\n" .
        'return $this->where($con)->save($data);'
        . "\r\n" . '}' . "\r\n";
    }

    //获取个数/笔数:今天\昨天\周\月\总

}