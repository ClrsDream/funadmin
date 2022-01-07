<?php

namespace app\common\command;

use think\facade\Cache;
use think\facade\Config;
use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;
class Install extends Command
{

    protected $lockFile;
    protected function configure()
    {
        $database = Config::get('database');
        $default = $database['default'];
        $config = $database['connections'][$default];
        $this->setName('install')
            ->addOption('hostname', 'm', Option::VALUE_OPTIONAL, 'hostname', $config['hostname'])
            ->addOption('hostport', 'r', Option::VALUE_OPTIONAL, 'hostport', $config['hostport'])
            ->addOption('database', 'd', Option::VALUE_OPTIONAL, 'database', $config['database'])
            ->addOption('prefix', 'x', Option::VALUE_OPTIONAL, 'prefix', $config['prefix'])
            ->addOption('username', 'u', Option::VALUE_OPTIONAL, 'mysql username', $config['username'])
            ->addOption('password', 'p', Option::VALUE_OPTIONAL, 'mysql password', $config['password'])
            ->addOption('force', 'f', Option::VALUE_OPTIONAL, 'force override', false)
            ->setDescription('FunAdmin install command');
    }

    /**
     *
     * @param Input $input
     * @param Output $output
     * @return int|void|null
     */
    protected function execute(Input $input, Output $output)
    {

        $force = $input->getOption('force');
        $this->lockFile = public_path() . "install.lock";
        if (is_file($this->lockFile) && !$force) {
            $this->output->highlight("已经安装了,如需重新安装请输入 -f 或 --force");
            exit();
        }
        $this->detectionEnv();
        $this->install($input);
    }
    /**
     * 环境检测
     *
     * @time 2019年11月29日
     * @return void
     */
    protected function detectionEnv(): void
    {
        $this->output->info('environment begin to check...');
        if (version_compare(PHP_VERSION, '7.4.0', '<')) {
            $this->output->error('php version should >= 7.4.0');
            exit();
        }
        $this->output->info('php version ' . PHP_VERSION);
        if (!function_exists('session_start')) {
            $this->output->error('session extension not install');
            exit();
        }
        $this->output->info('session extension is installed');
        if (!function_exists('curl_exec')) {
            $this->output->error('curl extension not install');
            exit();
        }
        $this->output->info('curl extension is installed');

        if (!extension_loaded('fileinfo')) {
            $this->output->error('fileinfo extension not install');
            exit();
        }
        $this->output->info('fileinfo extension is installed');

        if (!extension_loaded('openssl')) {
            $this->output->error('openssl extension not install');
            exit();
        }
        $this->output->info('openssl extension is installed');

        if (!extension_loaded('pdo')) {
            $this->output->error('pdo extension not install');
            exit();
        }
        $this->output->info('pdo extension is installed');

        if (!is_writable(root_path().'runtime')) {
            $this->output->error('runtime path is  not witeable');
            exit();
        }
        $this->output->info('runtime  is witeable');

        $this->output->info('🎉 environment checking finished');
    }

    /**
     * 开始安装
     * @return void
     */
    protected function install($input): void{
        $host = strtolower($this->output->ask($this->input, '👉 Set mysql hostname default(127.0.01)'))??$input->getOption('hostname');
        $port = strtolower($this->output->ask($this->input, '👉 Set mysql hostport default (3306)'))??$input->getOption(' ');
        $mysqlDatabase = strtolower($this->output->ask($this->input, '👉 Set mysql database default (funadmin)'))??$input->getOption('database');
        $mysqlPreFix = strtolower($this->output->ask($this->input, '👉 Set mysql table prefix default (fun_)'))??$input->getOption('prefix');
        $mysqlUserName = strtolower($this->output->ask($this->input, '👉 Set mysql username default (root)'))??$input->getOption('username');
        $mysqlPassword = strtolower($this->output->ask($this->input, '👉 Set mysql password'))??$input->getOption('password');
        $adminUserName = strtolower($this->output->ask($this->input, '👉 Set admin username'))??'admin';
        $adminPassword = strtolower($this->output->ask($this->input, '👉 Set admin password'))??'123456';
        $rePassword = strtolower($this->output->ask($this->input, '👉 Set admin repeat password'))??'123456';
        $email = strtolower($this->output->ask($this->input, '👉 Set admin email'))??'admin@admin.com';
        if(!$mysqlUserName || !$mysqlPassword){
            $this->output->error('Please enter the mysql account and password');
            exit();
        }
        if(!$adminUserName || !$adminPassword){
            $this->output->error('Please enter the admin account and password');
            exit();
        }
        //判断两次输入是否一致
        if ($adminPassword != $rePassword) {
            $this->output->error('两次输入密码不一致！');
            exit();
        }
        if(!preg_match('/^[0-9a-z_$]{6,16}$/i', $adminPassword)){
            $this->output->error('密码必须6-16位,且必须包含字母和数字,不能有中文和空格');
            exit();
        }
        if (!preg_match("/^\w+$/", $adminUserName)) {
            $this->output->error('用户名只能输入字母、数字、下划线！');
            exit();
        }
        if (strlen($adminUserName) < 3 || strlen($adminUserName) > 12) {
            $this->output->error('用户名请输入3~12位字符！');
            exit();
        }
        if (strlen($adminPassword) < 5 || strlen($adminPassword) > 16) {
            $this->output->error('Please enter 5~16 characters for the password!');
            exit();
        }
        $databaseConfigFile = root_path(). "config"  . DIRECTORY_SEPARATOR . "database.php";
        $entranceConfigFile = root_path(). "config" . DIRECTORY_SEPARATOR . "backend.php";
        $sql_file = public_path().'install'.DIRECTORY_SEPARATOR.'funadmin.sql';
        //检测能否读取安装文件
        $sql = @file_get_contents($sql_file);
        if (!$sql) {
            $this->output->error("无法读取/public/install/funadmin.sql文件，请检查是否有读权限");
            exit();
        }
        try {
            // 连接数据库
            $link = @new mysqli("{$host}:{$port}", $mysqlUserName, $mysqlPassword);
            $error = $link->connect_error;
            if (!is_null($error)) {// 转义防止和alert中的引号冲突
                $error = addslashes($error);
                $this->output->error("数据库链接失败:$error");
                exit();
            }
            $link->query('set global wait_timeout=2147480');
            $link->query("set global interactive_timeout=2147480");
            $link->query("set global max_allowed_packet=104857600");
            $link->query("SET NAMES 'utf8mb4'");
            if ($link->server_info < 5.5) {
                exit("MySQL数据库版本不能低于5.5,请将您的MySQL升级到5.5及以上");
            }
            // 创建数据库并选中
            if (!$link->select_db($mysqlDatabase)) {
                $create_sql = 'CREATE DATABASE IF NOT EXISTS ' . $mysqlDatabase . ' DEFAULT CHARACTER SET utf8mb4;';
                $link->query($create_sql) or exit('创建数据库失败');
                $link->select_db($mysqlDatabase);
            }
            $link->query("USE `{$mysqlDatabase}`");//使用数据库
            // 写入数据库
            $sqlArr = file(WWW_ROOT . DIRECTORY_SEPARATOR . "install" . DIRECTORY_SEPARATOR . 'funadmin.sql');
            $sql = '';
            foreach ($sqlArr as $value) {
                if (substr($value, 0, 2) == '--' || $value == '' || substr($value, 0, 2) == '/*')
                    continue;
                $sql .= $value;
                if (substr(trim($value), -1, 1) == ';' and $value != 'COMMIT;') {
                    $sql = str_ireplace("`fun_", "`{$mysqlPreFix}", $sql);
                    $sql = str_ireplace('INSERT INTO ', 'INSERT IGNORE INTO ', $sql);
                    try {
                        $link->query($sql);
                    } catch (\PDOException $e) {
                        exit($e->getMessage());
                    }
                    $sql = '';
                }
            }
            sleep(1);
            $password = password_hash($adminPassword, PASSWORD_BCRYPT);
            $result = $link->query("UPDATE {$mysqlPreFix}admin SET `email`='{$email}',`username` = '{$adminUserName}',`password` = '{$password}' WHERE `username` = 'admin'");
            $result2 = $link->query("UPDATE {$mysqlPreFix}member SET `email`='{$email}',`username` = '{$adminUserName}',`password` = '{$password}' WHERE `username` = 'admin'");
            $databaseConfig = @file_get_contents($databaseConfigFile);
            //替换数据库相关配置
            $config = <<<Fun
<?php
use think\\facade\Env;
return [
    // 默认使用的数据库连接配置
    'default'         => Env::get('database.driver', 'mysql'),
    // 自定义时间查询规则
    'time_query_rule' => [],
    // 自动写入时间戳字段
    // true为自动识别类型 false关闭
    // 字符串则明确指定时间字段类型 支持 int timestamp datetime date
    'auto_timestamp'  => true,
    // 时间字段取出后的默认时间格式
    'datetime_format' => 'Y-m-d H:i:s',
    // 数据库连接配置信息
    'connections'     => [
        'mysql' => [
            // 数据库类型
            'type'              => Env::get('database.type', 'mysql'),
            // 服务器地址
            'hostname'          => Env::get('database.hostname', '{$host}'),
            // 数据库名
            'database'          => Env::get('database.database', '{$mysqlDatabase}'),
            // 用户名
            'username'          => Env::get('database.username', '{$mysqlUserName}'),
            // 密码
            'password'          => Env::get('database.password', '{$mysqlPassword}'),
            // 端口
            'hostport'          => Env::get('database.hostport', '{$port}'),
            // 数据库连接参数
            'params'            => [],
            // 数据库编码默认采用utf8
            'charset'           => Env::get('database.charset', 'utf8mb4'),
            // 数据库表前缀
            'prefix'            => Env::get('database.prefix', '{$mysqlPreFix}'),
            // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
            'deploy'            => 0,
            // 数据库读写是否分离 主从式有效
            'rw_separate'       => false,
            // 读写分离后 主服务器数量
            'master_num'        => 1,
            // 指定从服务器序号
            'slave_no'          => '',
            // 是否严格检查字段是否存在
            'fields_strict'     => true,
            // 是否需要断线重连
            'break_reconnect'   => false,
            // 监听SQL
            'trigger_sql'       => true,
            // 开启字段缓存
            'fields_cache'      => false,
            // 字段缓存路径
            'schema_cache_path' => app()->getRuntimePath() . 'schema' . DIRECTORY_SEPARATOR,
        ],
        // 更多的数据库配置信息
    ],
];
Fun;
            $putConfig = @file_put_contents($databaseConfigFile, $config);
            if (!$putConfig) {
                $this->output->error('Installation failed, please make sure database.php has write permission！:' . $error);
                exit();
            }
            $adminStr = <<<Fun
<?php
// [ 应用入口文件 ]
namespace think;
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    header("Content-type: text/html; charset=utf-8");
    exit('PHP 7.4.0 及以上版本系统才可运行~ ');
}
if (!is_file(\$_SERVER['DOCUMENT_ROOT'].'/install.lock'))
{
    header("location:/install.php");exit;
}
require __DIR__ . '/../vendor/autoload.php';
// 执行HTTP应用并响应
\$http = (new  App())->http;
\$response = \$http->name('backend')->run();
\$response->send();
\$http->end(\$response);
?>
Fun;
            $adminName = '';
            $x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $adminName = substr(str_shuffle($x), 0, 10) . '.php';
            $backendFile = "." . DIRECTORY_SEPARATOR . $adminName;
            if (!file_exists($backendFile)) {
                @touch($backendFile);
            }
            @file_put_contents($backendFile, $adminStr);
            if (!file_exists($entranceConfigFile)) {
                @touch($entranceConfigFile);
            }
            $key = 'backendEntrance';
            $config = file_get_contents($entranceConfigFile); //加载配置文件
            $config = preg_replace("/'{$key}'.*?=>.*?'.*?'/", "'{$key}' => '/{$adminName}/'", $config);
            @file_put_contents($entranceConfigFile, $config); // 写入配置文件
            $result = @file_put_contents($this->lockFile, 'ok');
            if (!$result) {
                $this->output->error("Installation failed, please make sure install.lock has write permission！:$error");
                exit();
            }
            $this->output->highlight('admin: '.$adminUserName.'password:'.$adminPassword,'adminName:'.$adminName);

        } catch (\Exception $e) {
            $this->output->error($e->getMessage());
        }
        exit();
    }
}
