<?php

/**
 * FunAdmin
 * ============================================================================
 * 版权所有 2017-2028 FunAdmin，并保留所有权利。
 * 网站地址: http://www.funadmin.com
 * ----------------------------------------------------------------------------
 * 采用最新Thinkphp6 + layui 实现
 * ============================================================================
 * Author: yuege
 * Date: 2019/9/22
 */

namespace fun\helper;


use fun\Form;
use think\facade\View;
use think\helper\Str;

class FormBuilder
{
    /**
     * 表单html
     * @var array
     */
    protected $formHtml = [];

    protected static $instance;
    /**
     * 获取单例
     * @param array $options
     * @return static
     */
    public static function instance($options = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }

        return self::$instance;
    }
    /**
     * @param $name
     * @param $value
     * @param $options
     * @param $list
     * @param $attr
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public  function config($name='',$options=[],$value='')
    {
        $this->formHtml[] = Form::config($name,$options,$value);
        return $this;
    }


    public  function token($name = '__token__', $type = 'md5')
    {
        $this->formHtml[] = Form::token($name = '__token__', $type = 'md5');
        return $this;
    }

    /**
     * 生成文本框(按类型) password .text
     * @param string $name
     * @param string $type
     * @param array $options
     * @return string
     */
    public  function input(string $name = '', string $type = 'text',array $options = [], $value = '')
    {
        $this->formHtml[] = Form::input($name, $type,$options,$value);
        return $this;
    }

    /**
     * @param string $name
     * @param array $options
     * @param  $value
     * @return string
     */
    public  function text(string $name,array $options = [], $value = null)
    {
        $this->formHtml[] = Form::input($name, 'text',$options,$value);
        return $this;
    }

    /**
     * 创建一个密码输入字段
     *
     * @param  string  $name
     * @param  array   $options
     *
     * @return string
     */
    public  function password(string $name, array $options = [],$value='')
    {
        $this->formHtml[] = Form::input($name, 'password', $options,$value);
        return $this;
    }

    /**
     * 创建一个范围输入选择器
     *
     * @param  string  $name
     * @param  null    $value
     * @param  array   $options
     *
     * @return string
     */
    public  function range($name, $options = [], $value = null)
    {
        $this->formHtml[] = Form::input($name,$options,$value);
        return $this;
    }

    /**
     * 创建一个隐藏的输入字段
     *
     * @param  string  $name
     * @param  null    $value
     * @param  array   $options
     *
     * @return string
     */
    public  function hidden($name,  $options = [],$value = null)
    {
        $this->formHtml[] = Form::input($name,'hidden',$options,$value);
        return $this;
    }

    /**
     * 创建一个电子邮件输入字段
     *
     * @param  string  $name
     * @param  null    $value
     * @param  array   $options
     *
     * @return string
     */
    public  function email($name,  $options = [],$value = null)
    {
        $this->formHtml[] = Form::input($name,'email',$options,$value);
        return $this;
    }

    /**
     * 创建一个tel输入字段
     *
     * @param  string  $name
     * @param  null    $value
     * @param  array   $options
     *
     * @return string
     */
    public  function tel($name,  $options = [],$value = null)
    {
        $this->formHtml[] = Form::input($name,'tel',$options,$value);
        return $this;
    }

    /**
     * 创建一个数字输入字段
     *
     * @param  string  $name
     * @param  null    $value
     * @param  array   $options
     *
     * @return string
     */
    public  function number($name,  $options = [],$value = null)
    {
        $this->formHtml[] = Form::input($name,'number',$options,$value);
        return $this;
    }

    /**
     * 创建一个url输入字段
     *
     * @param  string  $name
     * @param  null    $value
     * @param  array   $options
     *
     * @return string
     */
    public  function url($name,  $options = [],$value = null)
    {
        $this->formHtml[] = Form::input($name,'url',$options,$value);
        return $this;
    }

    /**
     * 评分
     * @param $name
     * @param $options
     * @param $value
     * @return string
     */
    public  function rate($name = '', $options = [], $value = '')
    {
        $this->formHtml[] = Form::rate($name,$options,$value);
        return $this;
    }
    /**
     * 滑块
     * @param $name
     * @param $options
     * @param $value
     * @return string
     */
    public  function slider($name = '', $options = [], $value = '')
    {
        $this->formHtml[] = Form::slider($name,$options,$value);
        return $this;
    }
    /**
     * @param $name
     * @param $radiolist
     * @param array $options
     * @param string $value
     * @return string
     */
    public  function radio($name = '', $radiolist=[], $options = [], $value = '')
    {
        $this->formHtml[] = Form::slider($name,$radiolist,$options,$value);
        return $this;
    }

    /**
     * 生成开关
     * @param $name
     * @param $value
     * @param array $options
     * @return string
     * switch是关键字不能用
     */

    public  function switchs($name = '', $switch=[], $options = [], $value = '')
    {
        $this->formHtml[] = Form::switchs($name,$switch,$options,$value);
        return $this;
    }

    /**
     * 多选
     * @param null $name
     * @param array $list
     * @param array $options
     * @param $value
     * @return string
     */
    public  function checkbox($name = '', $list = [], $options = [], $value = '')
    {
        $this->formHtml[] = Form::checkbox($name,$list,$options,$value);
        return $this;
    }

    /**
     * 数组表单
     * @param null $name
     * @param array $options
     * @param array $list
     * @return string
     */
    public  function arrays($name = '', $list = [], $options = [])
    {
        $this->formHtml[] = Form::arrays($name,$list,$options);
        return $this;
    }

    /**
     * 文本
     * @param null $name
     * @param array $options
     * @param $value
     * @return string
     */
    public  function textarea($name = '', $options = [], $value = '')
    {
        $this->formHtml[] = Form::textarea($name,$options,$value);
        return $this;
    }

    /**
     * @param $name
     * @param $select
     * @param $options
     * @param $attr
     * @param $value
     * @return string
     */
    public  function selectn($name = '', $select= [], $options=[], $attr=[], $value='')
    {
        $this->formHtml[] = Form::selectn($name,$select,$options,$attr,$value);
        return $this;
    }
    /**
     * @param $name
     * @param $select
     * @param $options
     * @param $attr
     * @param $value
     * @return string
     */
    public  function selectplus($name = '', $select= [], $options=[], $attr=[], $value='')
    {
        $this->formHtml[] = Form::selectplus($name,$select,$options,$attr,$value);
        return $this;
    }
    /**
     * @param $name
     * @param $select
     * @param $options
     * @param $attr
     * @param $value
     * @return string
     */
    public  function multiselect($name = '', $select=[], $options=[], $attr=[], $value='')
    {
        $this->formHtml[] = Form::multiselect($name,$select,$options,$attr,$value);
        return $this;
    }
    /**
     * @param $name
     * @param $select
     * @param $options
     * @param $attr
     * @param $value
     * @return string
     */
    public  function xmselect($name = '', $select=[], $options=[], $attr=[], $value='')
    {
        $this->formHtml[] = Form::xmselect($name,$select,$options,$attr,$value);
        return $this;
    }

    /**
     * 创建动态下拉列表字段
     * @param $name
     * @param $options
     * @param $value
     * @return string
     */
    public  function selectpage(string $name,array $lists= [],array $options = [],$value=null)
    {
        $this->formHtml[] = Form::selectpage($name,$lists,$options,$value);
        return $this;
    }
    /**
     * @param $name
     * @param $value
     * @param array $options
     * @return string
     * tag
     */
    public function tags($name = '', $options = [], $value = '')
    {
        $this->formHtml[] = Form::tags($name,$options,$value);
        return $this;
    }

    /**
     * @param $name
     * @param $value
     * @param array $options
     * @return string
     * 颜色选择
     */
    public  function color($name = '', $options = [], $value = '')
    {
        $this->formHtml[] = Form::color($name,$options,$value);
        return $this;
    }

    /**
     * @param $name
     * @param $value
     * @param array $options
     * @return string
     * 图标，有点小问题
     */
    public  function icon($name = '', $options = [], $value = '')
    {
        $this->formHtml[] = Form::icon($name,$options,$value);
        return $this;
    }

    /**
     * @param null $name
     * @param array $options
     * @return string
     * 日期
     */
    public  function date($name='', $options=[], $value='')
    {
        $this->formHtml[] = Form::date($name,$options,$value);
        return $this;
    }
    /**
     * 城市选择
     * @param string $name
     * @param $options
     * @return string
     */
    public  function city($name = 'cityPicker', $options = [],$value='')
    {
        $this->formHtml[] = Form::city($name,$options,$value);
        return $this;
    }

    /**
     * 城市选择
     * @param string $name
     * @param $options
     * @return string
     */
    public  function region($name = 'region',  $options = [],$value='')
    {
        $this->formHtml[] = Form::region($name,$options,$value);
        return $this;
    }

    /**
     * @param string $name
     * @param $id
     * @param int $type
     * @param array $options
     * @return string
     * 编辑器
     */
    public  function editor($name = 'container', $options = [], $value = '')
    {
        $this->formHtml[] = Form::editor($name,$options,$value);
        return $this;
    }
    /**
     * 上传
     * @param string $name
     * @param string $formData
     * @param array $options
     * @return string
     */
    public  function upload($name = 'avatar', $options = [], $value = '')
    {
        $this->formHtml[] = Form::upload($name,$options,$value);
        return $this;
    }
    /**
     * @param bool $reset
     * @param array $options
     * @return string
     */
    public  function closebtn($reset = true, $options = [])
    {
        $this->formHtml[] = Form::closebtn($reset,$options);
        return $this;
    }


    /**
     * @param bool $reset
     * @param array $options
     * @return string
     */
    public  function submitbtn($reset=true, $options=[])
    {
        $this->formHtml[] = Form::submitbtn($reset,$options);
        return $this;
    }

    public function submit($reset=true, $options=[]){

        $this->formHtml[] = Form::submitbtn($reset,$options);
        return $this;
    }

    /**
     * @param $script
     * @return void
     */
    public function js($name=[],$options=[]){
        $this->formHtml[] = Form::js($reset,$options);
        return $this;
    }
    public function css($name=[],$options=[]){
        $this->formHtml[] = Form::css($reset,$options);
        return $this;
    }
    /**
     * 渲染视图
     * @return string
     */
    public function assign(){

        View::assign(['formHtml'=>implode(',',$this->formHtml)]);
        return $this;
    }

    /**
     *
     * @param $formValue
     * @return $this
     */
    public function formValue($formValue=[]){
        $formValue = json_encode($formValue);
        $this->formHtml[] = <<<EOF
<script>
  layui.form.val("form", {$formValue});
  layui.form.render();
  </script>;
EOF;
        return $this;
    }
}
