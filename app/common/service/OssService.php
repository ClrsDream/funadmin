<?php

namespace app\common\service;

use app\common\model\Config as ConfigModel;
use think\App;

class OssService extends AbstractService
{

    public function __construct(App $app)
    {
        parent::__construct($app);
    }
    /**
     * @param $driver 驱动
     * @param $object 远程地址
     * @param $path 本地地址
     * @param $save 本地是否保存
     * @return mixed
     */
    public function uploads($driver,$object, $path,$save)
    {
        $param = [
            'osspath'=>$object,
            'localpath'=>$path,
            'save'=>$save,
        ];
        if($driver=='alioss') return hook('aliossHook',$param);
        if($driver=='qiniuoss') return hook('qiniuossHook',$param);
        if($driver=='tencos')  return hook('tencosHook',$param);
    }
}