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
 * Date: 2019/9/21
 */


namespace addons\bbs\frontend\controller;

use app\common\controller\AddonsFrontend;
use app\common\controller\Frontend;
use app\common\model\Addon;
use addons\bbs\common\model\Bbs;
use addons\bbs\common\model\BbsCategory;
use addons\bbs\common\model\BbsLink;
use think\facade\View;
use think\App;

class Comm extends AddonsFrontend
{
    public $BASE_URL= null;
    public $pageSize = 8;
    public function __construct(App $app){

        parent::__construct($app);
        $this->_initialize();
    }
    public function _initialize()
    {
        parent::initialize(); // TODO: Change the autogenerated stub
        $member = $this->isLogin();
        $config = get_addons_config($this->addon);
        if($config==0){
            $this->redirect(url('Error/notice'));
        }
        $this->link();
        $this->cates();
        //跨域
        if($_SERVER["HTTP_HOST"]==$_SERVER["SERVER_NAME"]){
            $BASE_URL = httpType().$_SERVER["HTTP_HOST"].'/'.app('http')->getName();
        }else{
            $BASE_URL = httpType().$_SERVER["HTTP_HOST"].'/';
        }
        $this->BASE_URL = $BASE_URL;
        $view = [
            'member' =>$member,
            'action' =>$this->action,
            'controller' =>$this->controller,
            'module' =>$this->module,
            'BASE_URL' =>$BASE_URL,
        ];
        View::assign($view);

    }

    protected function link(){
        $link =  BbsLink::getlink();
        View::assign('link',$link);
    }

    protected function cates(){
        $cates =  BbsCategory::getList();
        View::assign('cates',$cates);

    }

        //是否登录
    public function isLogin(){
        if(session('member.id')){
            return  session('member');
        }else{
            return false;
        }
    }

    //未登录跳转
    public function LoginErr(){
        if(!session('member')) {
            $this->error('请先登录',url('login/login'));
        }
    }


    //热门
    protected function getHots(){
        $hots =  Bbs::where('status',1)
            ->withCount('comment')
            ->whereWeek('create_time')->order('comment_count desc')->limit(10)->select();

        View::assign('hots',$hots);
    }
    //置顶
    protected function getTop(){

        //置顶文章
        $top =  Bbs::where('status',1)
            ->where('is_top',1)->withCount(['comment'])
            ->with(['user' => function($query){
                $query->field('id,username,avatar,level_id');
            }])->order('create_time desc')->limit(5)->select();
        View::assign('top',$top);
    }


}