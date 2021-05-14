<?php
/**
 * FunAdmin
 * ============================================================================
 * 版权所有 2017-2028 FunAdmin，并保留所有权利。
 * 网站地址: https://www.FunAdmin.com
 * ----------------------------------------------------------------------------
 * 采用最新Thinkphp6实现
 * ============================================================================
 * Author: yuege
 * Date: 2019/9/21
 */

namespace app\common\controller;
use app\backend\service\AuthService;
use app\common\service\AdminLogService;
use app\common\traits\Jump;
use app\common\traits\Curd;
use think\App;
use think\facade\Config;
use think\facade\Lang;
use think\facade\View;
use think\helper\Str;
class AddonsBackend extends AddonsController
{
    /**
     * @var
     * 后台入口
     */
    protected $entrance;
    /**
     * @var
     * 模型
     */
    protected $modelClass;
    /**
     * @var
     * 页面大小
     */
    protected $pageSize;

    /**
     * @var
     * 页数
     */
    protected $page;
    /**
     * 模板布局, false取消
     * @var string|bool
     */
    protected $layout = '../app/backend/view/layout/main.html';

    /**
     * 主题
     * @var
     */
    protected $theme;

    /**
     * 快速搜索时执行查找的字段
     */
    protected $searchFields = 'id';
    /**
     * 允许修改的字段
     */
    protected $allowModifyFields = [
        'status',
        'title',
        'auth_verify'
    ];
    /**
     * 是否是关联查询
     */
    protected $relationSearch = false;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->entrance = config('entrance.backendEntrance');
        (new AuthService())->checkNode();
        $this->pageSize = request()->param('limit/d', 15);
        //加载语言包
        $this->loadlang(strtolower($this->controller));
        $this->_initialize();
        $this->theme();
        View::assign('addon',$this->addon);
    }

    /**
     * 获取主题路径
     */
    public function theme(){
        $theme = cache($this->addon.'_theme');
        if($theme){
            $this->theme = $theme;
        }else{
            $view_config_file = $this->addon_path.'frontend'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'view.php';
            if(file_exists($view_config_file)){
                $view_config = include_once($this->addon_path.'frontend'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'view.php');
                $this->prefix = Config::get('database.connections.mysql.prefix');
                $theme = $view_config['view_base'];
                $addonsconfig = get_addons_config($this->addon);
                if(isset($addonsconfig['theme']) && $addonsconfig['theme']['value']){
                    $theme = $addonsconfig['theme']['value'];
                }
                $this->theme = $theme?$theme.DIRECTORY_SEPARATOR:'';
                cache($this->addon.'_theme',$this->theme);
            }
        }
    }

    public function _initialize()
    {
        [$modulename, $controllername, $actionname] = [$this->module, $this->controller, $this->action];
        $controllername = str_replace('\\','.',$controllername);
        $controllers = explode('.', $controllername);
        $jsname = '';
        foreach ($controllers as $vo) {
            empty($jsname) ? $jsname = strtolower(parse_name($vo,1)) : $jsname .= '/' . strtolower(parse_name($vo,1));
        }
        $controllername = strtolower(Str::camel(parse_name($controllername,1)));
        $actionname = strtolower(Str::camel(parse_name($actionname,1)));
        $requesturl = strtolower("addons/{$this->addon}/{$modulename}/{$controllername}/{$actionname}");
        $autojs = file_exists(app()->getRootPath()."public".DS."static".DS.'addons'.DS."{$this->addon}".DS."{$modulename}".DS."js".DS."{$jsname}.js") ? true : false;
        $jspath ="addons/{$this->addon}/{$modulename}/js/{$jsname}.js";
        $auth = new AuthService();
        $authNode = $auth->nodeList();
        $config = [
            'entrance'    => $this->entrance,//入口
            'modulename'    => $modulename,
            'addonname'    => $this->addon,
            'moduleurl'    => rtrim(url("/{$modulename}", [], false), '/'),
            'controllername'       =>$controllername,
            'actionname'           => $actionname,
            'requesturl'          => $requesturl,
            'jspath' => "{$jspath}",
            'autojs'           => $autojs,
            'authNode'           => $authNode,
            'superAdmin'           => session('admin.id')==1,
            'lang'           =>  strip_tags( Lang::getLangset()),
            'site'           =>  syscfg('site'),
            'upload'           =>  syscfg('upload'),

        ];
        //保留日志
        $logdata = [
            'module'=>$this->module,
            'controller'=>$this->controller,
            'action'=>$this->action,
            'addons'=>$this->addon,
            'url'=>$requesturl,
        ];
        AdminLogService::instance()->saveaddonslog($logdata);
        View::assign('config',$config);
        parent::_initialize(); // TODO: Change the autogenerated stub
    }

}