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
 * Date: 2019/8/26
 */

namespace addons\bbs\common\model;

use app\common\model\BaseModel;

class BbsCategory extends BaseModel {

    protected $name = 'addons_bbs_category';

    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    public static function getList($where = ['status'=>1], $pageSize=10, $order = ['sort', 'id' => 'desc'])
    {
      $cates =  self::where($where)
            ->cache(3600)
            ->order($order)
            ->select();
        return $cates;
    }

}
