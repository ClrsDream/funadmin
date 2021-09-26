<?php
namespace app\api\controller\v1;

use fun\auth\JwtToken as TokenApi;
use think\facade\Request;

/**
 * Jwt验证
 */
class JwtToken extends TokenApi
{

    public $appsecret = 123456;
    public $authapp = true;
    public function __construct(Request $request)
    {
        parent::__construct($request);
        //跨域
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Headers:Accept,Referer,Host,Keep-Alive,User-Agent,X-Requested-With,Cache-Control,Content-Type,Cookie,token');
        header('Access-Control-Allow-Credentials:true');
        header('Access-Control-Allow-Methods:GET, POST, PATCH, PUT, DELETE,OPTIONS');
    }



}