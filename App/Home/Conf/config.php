<?php
return array(
    //'配置项'=>'配置值';
    //'SHOW_PAGE_TRACE' => 'true',
    'URL_MODEL' => 1,
    'SHOW_ERROR_MSG' =>    false,
    //连接数据库
    'DB_TYPE'   => 'mysql', // 设置数据库类型
    'DB_HOST'   => 'localhost', // 设置主机 192.168.0.241
    'DB_NAME'   => 'face', // 设置数据库
    'DB_USER'   => 'root', // 设置用户名
    'DB_PWD'    => '123456', // 设置密码
    'DB_PORT'   => 3306, // 设置端口
    'DB_PREFIX' => '', // 设置前缀
    'DB_CHARSET'=> 'utf8', // 默认编码UTF8
    //保持字段大小写 数据库连接超时时间
    'DB_PARAMS' => array(\PDO::ATTR_CASE => \PDO::CASE_NATURAL,\PDO::ATTR_TIMEOUT => 3,\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION),
    'GPS_LAT'   => '32.05712932',   //纬度
    'GPS_LON'   => '118.62640877',  //经度
    'THEMES'    => array(
        'cupertino','default','black','bootstrap','dark-hive','gray','material','metro','pepper-grinder','sunny'
    )
);