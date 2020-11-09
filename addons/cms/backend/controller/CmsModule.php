<?php
/**
 * funadmin
 * ============================================================================
 * 版权所有 2018-2027 funadmin，并保留所有权利。
 * 网站地址: https://www.funadmin.com
 * ----------------------------------------------------------------------------
 * 采用最新Thinkphp6实现
 * ============================================================================
 * Author: yuege
 * Date: 2019/8/2
 */

namespace addons\cms\backend\controller;

use app\common\controller\AddonsBackend;
use addons\cms\common\model\CmsModule as CmsModuleModel;
use addons\cms\common\model\CmsField;
use app\common\model\FieldType;
use app\common\traits\Curd;
use think\App;
use think\exception\ValidateException;
use think\facade\Config;
use think\facade\Db;
use think\facade\Request;
use think\facade\View;
use function Composer\Autoload\includeFile;

class CmsModule extends AddonsBackend
{
    use Curd;
    public $prefix = '';
    public $filepath = '';
    public $_list = '';
    public $_column = '';
    public $_show = '';
    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->modelClass = new CmsModuleModel();
        $view_config = include_once($this->addon_path.'frontend'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'view.php');
        $this->prefix = Config::get('database.connections.mysql.prefix');
        $theme = $view_config['view_base'];
        $theme = $theme?$theme.DIRECTORY_SEPARATOR:'';
        //取得当前内容模型模板存放目录
        $this->filepath = $this->addon_path.'view'.DIRECTORY_SEPARATOR.'frontend' . DIRECTORY_SEPARATOR;
        //取得栏目频道模板列表
        $this->_column = str_replace($this->filepath . DIRECTORY_SEPARATOR.$theme, '', glob($this->filepath .DIRECTORY_SEPARATOR.$theme  . 'column*'));
        $this->_column = array_combine(array_values($this->_column),$this->_column);
        //取得栏目列表模板列表
        $this->_list = str_replace($this->filepath . DIRECTORY_SEPARATOR.$theme, '', glob($this->filepath . DIRECTORY_SEPARATOR .$theme. 'list*'));
        $this->_list = array_combine(array_values($this->_list),$this->_list);
        //取得内容页模板列表
        $this->_show = str_replace($this->filepath . DIRECTORY_SEPARATOR.$theme, '', glob($this->filepath . DIRECTORY_SEPARATOR .$theme. 'show*'));
        $this->_show = array_combine(array_values($this->_show),$this->_show);

    }
    // 模型添加
    public function add()
    {
        if ($this->request->isAjax()) {
            //获取数据库所有表名
            $tablename = $this->request->param('tablename/s');
            $tablename = str_replace('addons_','',$tablename);
            $tablename = str_replace($this->addon.'_','',$tablename);
            $tablename = $this->prefix .'addons_'.$this->addon.'_'. $this->request->param('tablename');
            if(strpos($tablename,'addons_'.$this->addon.'_muster')){$this->error(lang('Table is exist'));}
            $tables = $this->modelClass->getTables();
            if (in_array($tablename, $tables)) {
                $this->error(lang('table is already exist'));
            }
            $post = $this->request->post();
            $rule = [
                'modulename|模型名称' => [
                    'require' => 'require',
                    'max'     => '100',
                    'unique'  => 'addons_cms_module',
                ],
                'tablename|表名' => [
                    'require' => 'require',
                    'max'     => '50',
                    'unique'  => 'addons_cms_module',
                ],
                'listfields|列表页字段' => [
                    'require' => 'require',
                    'max'     => '255',
                ],
                'intro|描述' => [
                    'max' => '200',
                ],
                'sort|排序' => [
                    'require' => 'require',
                    'number'  => 'number',
                ]
            ];
            try {
                $this->validate($post, $rule);
            } catch (\ValidateException $e) {
                $this->error($e->getMessage());
            }
            $post['template']=isset($post['template'])? jsno_encode($post['template'],true): "";
            $module = $this->modelClass->save($post);
            if (!$module) {
                $this->error(lang('Add Fail'));
            }
            $moduleid =  $this->modelClass->getLastInsID();
            $this->modelClass->addTable($tablename,$this->prefix,$moduleid);
            $this->success(lang('Add Success'));
        }
        $view =[
            'title'=>lang('add'),
            'formData' => null,
            '_column'=>$this->_column,
            '_list'=>$this->_list,
            '_show'=>$this->_show,
            ''
        ];

        View::assign($view);
        return view();
    }


    // 模型修改
    public function edit(){
        $id    = $this->request->param('id');
        $list   = $this->modelClass->find($id);
        if ($this->request->isAjax()) {
            $post =$this->request->post();
            $rule = [];
            try {
               $this->validate($post, $rule);
            }catch (ValidateException $e){
                $this->error($e->getMessage());
            }
            $post['template'] = json_encode($post['template'],true);
            if ($list->save($post) !== false) {
                $this->success(lang('Edit Success'));
            } else {
                $this->success(lang('Edit Fail'));
            }
        }
        $list['template'] = json_decode($list['template'],JSON_UNESCAPED_UNICODE);
        $view = [
            'title'=>lang('edit'),
            'formData' => $list,
            '_column'=>$this->_column,
            '_list'=>$this->_list,
            '_show'=>$this->_show,
        ];
        View::assign($view);
        return view('add');
    }
    // 模型删除
    public function delete(){
        if ($this->request->isAjax()) {
            $ids = $this->request->param('id');
            $list = $this->modelClass->find($ids);
            $tables = $this->prefix.$list->tablename;
            $res = $list->delete();
            if($res){
                Db::execute("DROP TABLE IF EXISTS `".$tables."`");
                CmsField::where('moduleid',$list->id)->delete();
                $this->success(lang('delete success'));
            }else{
                $this->error(lang('delete fail'));
            }

        }
    }


    /****************************模型字段管理******************************/

    /*
     * 字段列表
     */
    public function field(){

        if($this->request->isAjax()){
            //不可控字段
            $sysfield = array('cateid','title','keywords','description','hits','status','create_time','update_time','template');
            $list = CmsField::where("moduleid",'=',$this->request->param('id/d'))
                ->order('sort asc,id asc')
                ->select()->toArray();
            foreach ($list as $k=>$v){
                if(in_array($v['field'],$sysfield)){
                    $list[$k]['del']=0;
                }else{
                    $list[$k]['del']=1;
                }
            }
            return $result = ['code' => 0, 'msg' => lang('get info success'), 'data' => $list, 'count' => count($list)];
        }
        $view = [
            'moduleid' => Request::param('id')
        ];
        View::assign($view);
        return view();
    }


    // 添加字段
    public function fieldAdd(){
        if ($this->request->isAjax()) {
            //增加字段
            $post = Request::param();
            try{
                $result = $this->validate($post, 'CmsField');

            }catch (\Exception $e){
                $this->error($e->getMessage());

            }
            try {
                $res = CmsField::addField($post);
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
            $this->success(lang('add success'));

        }

        $view = [
            'moduleid'  => Request::param('moduleid'),
            'info'      => null,
            'title'=>lang('add'),
            'fieldType'=>FieldType::select(),
        ];
        View::assign($view);
        return view('field_add');
    }



    // 编辑字段
    public function fieldEdit(){
        if ($this->request->isAjax()) {
            //增加字段
            $post = Request::param();
            try{
                $result = $this->validate($post, 'CmsField');

            }catch (\Exception $e){
                $this->error($e->getMessage());

            }
            try {
                $fieldid  = $post['id'];
                $res = CmsField::editField($post,$fieldid);
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
            $this->success(lang('add success'));

        }

        $id = Request::param('id');
        $fieldInfo = CmsField::where('id','=',$id)
            ->find();

        $view = [
            'moduleid'  => $fieldInfo['moduleid'],
            'fieldType'=>FieldType::select(),
            'info'      => $fieldInfo,
            'title'=>lang('edit'),
        ];
        View::assign($view);
        return view('field_add');
    }

    // 删除字段
    public function fieldDel() {
        $ids = Request::param('ids');
        $f  = Db::name('field')->find($ids[0]);
        //删除字段表中的记录
        CmsField::destroy($ids[0]);
        $moduleid = $f['moduleid'];
        $field    = $f['field'];
        $name   = $this->modelClass->where('id',$moduleid)->value('tablename');
        $tablename = $this->prefix.$name;
        //实际查询表中是否有该字段
        if(CmsField::isset_field($tablename,$field)){
            Db::name($tablename)->execute("ALTER TABLE `$tablename` DROP `$field`");
        }
        $this->success(lang('删除成功'));
    }

    // 字段排序
    public function fieldSort(){
        $post = Request::post();
        if (CmsField::update($post) !== false) {
            $this->success(lang('edit success'));
        } else {
            $this->error(lang('edit fail'));
        }
    }

    // 字段状态
    public function fieldState(){
        if ($this->request->isAjax()) {
            $id = $this->request->param('ids|id');
            $status = CmsField::where('id','=',$id)
                ->value('status');
            $status = $status == 1 ? 0 : 1;
            if (CmsField::where('id','=',$id)->update(['status'=>$status]) !== false) {
                $this->success(lang('edit success'));
            } else {
                $this->error(lang('edit fail'));
            }
        }
    }




}
