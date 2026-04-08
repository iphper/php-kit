<?php

require_once __DIR__ . '/../../../../vendor/autoload.php';

use Kit\Terminal\Output\Table\Table;

$options = [
    //'width' => 100,
    // 'caption' => '这是一个表格',
    // 'header' => [
    //     'id' => 'ID',
    //     'name' => '姓名',
    // ],
    'body' => [
        ['id' => 1, 'name' => '张三', 'age' => 18],
        ['id' => 2, 'name' => '李g四', ],
        ['id' => 3, 'name' => '王五', ],
    ],
    // 'footer' => [
    //     ['id' => '总计', 'age' => 60],
    //     ['id' => '平均', 'age' => 20],
    // ],
];

(new Table($options))->render();

$options = [
            'caption' => 'abc',
            'header' => [['code' => 'Code', 'country' => 'Country']],
            'body' => [
            ['code' => 'CN', 'country' => '中国'],
            ['code' => 'US', 'country' => '美国'],
        ],
            'footer' => ['Total', 3]
        ];

Table::output($options);

