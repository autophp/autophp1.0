<?php
/**
 * Created by codegenerator.
 * User: xiaofeng
 * Date: 16/3/13
 * Time: 下午1:20
 */

namespace TPH\Controller;


use Think\Controller;

class CviewmodelController extends Controller
{
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
        else//执行视图模型生成器
        {
            $code = self::createViewModel(I('moduleName'), 'Wifes');
            //生成文件
            echo createFile(I('moduleName'), I('tablename_a'), $code, 'Model');
            die;
        }
    }

    public function get_table_column()
    {
        if(IS_POST)
        {
            $tablename = I('tablename');
            $result = M(removeTablePrefix($tablename))->getDbFields();
            $this->ajaxReturn($result);
        }
    }

    protected function createViewModel($moduleName, $tableName)
    {
        $code = $this->get_namespace_code($moduleName);
        $code .= $this->get_use_code();
        $code .= $this->get_class_code($tableName);
        $content = "这里是核心,一个是表名,一个是字段,一个是on,还有一个就是顺序";
        $code .= $this->get_view_fields_code($content);
        $code .= $this->get_file_end_code();


//
//        $code = $code . 'use Think\ViewModel;';
//
//
//        $code = $code . 'class ' . $firstTableName . 'ViewModel extends ViewModel{';
//
//        $firstTableName = "'" . $firstTableName . "'";
//        $secondTableName = "'" . $secondTableName . "'";
//
//
//        $content = $firstTableName . '=>array(' . $firstArr . '),';
//        $content = $content . $secondTableName . '=>array(' . $secondArr . '),';
//
//        $code = $code .
//            'public $viewFields = array(' . $content . ')'
//            . '}';

        dump($code);
        return $code;
    }


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
        return 'use Think\Model\ViewModel;' . "\r\n";
    }

    //获取class的code
    protected function get_class_code($tableNameWithoutPrefix)
    {
        return 'class ' . $tableNameWithoutPrefix . 'ViewModel extends ViewModel{
        ' . "\r\n";
    }

    protected function get_view_fields_code($content)
    {
        return 'public $viewFields = array(' . $content . ');';
    }

    //获取数据库表名常量
    protected function get_const_code($tableName)
    {
        return 'const table_' . strtolower($tableName) . ' = ' . "'" . $tableName . "';\r\n";
    }

    //获取插入的字段名
    protected function get_viewfield_code($fieldStr)
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

    //获取文件的结束code
    protected function get_file_end_code()
    {

        return "\r\n" . '}' . "\r\n";
    }
}