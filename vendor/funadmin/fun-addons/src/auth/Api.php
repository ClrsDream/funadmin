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
 * Date: 2019/10/3
 */
namespace fun\auth;

use think\exception\HttpResponseException;
use think\App;
use think\facade\Request;
use fun\auth\Send;
use fun\auth\Oauth;
use think\Response;

/**
 * api 入口文件基类，需要控制权限的控制器都应该继承该类
 */
class Api
{
    use Send;
    /**
     * @var \think\Request Request实例
     */
    protected $request;
    /**
     * @var
     * 客户端信息
     */
    protected $clientInfo;
    /**
     * 不需要鉴权方法
     */
    protected $noAuth = [];

    protected $uid = '';

    /**
     * 构造方法
     * @param App $app $app对象
     */
    public function __construct(App $app)
    {
        $this->request = Request::instance();
        $this->request->filter('trim,strip_tags,htmlspecialchars');
        $this->init();
        if($this->clientInfo){
            $this->uid = $this->clientInfo['uid'];
        }
    }

    /**
     * 初始化
     * 检查请求类型，数据格式等
     */
    public function init()
    {
        //所有ajax请求的options预请求都会直接返回200，如果需要单独针对某个类中的方法，可以在路由规则中进行配置
        if ($this->request->isOptions()) {
            $this->success('success');
        }
        $oauth = new Oauth();
        if (!$oauth->match($this->noAuth) || $oauth->match($this->noAuth) && Request::header(config('api.authentication'))) {               //请求方法白名单
            $oauth = new Oauth();
            return $this->clientInfo = $oauth->authenticate();
        }

    }
    /**
     * 空方法
     */
    public function _empty()
    {
        $this->error('empty method!');
    }
}