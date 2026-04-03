<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use Kit\Terminal\Output\Table\Table;

$table = new Table([
    'width' => 100,
    'caption' => '这是一个表格',
    'header' => [
        'id' => 'ID',
        'name' => '姓名',
        'age' => '年龄',
    ],
    'body' => [
        ['id' => 1, 'name' => '张三', 'age' => 18],
        ['id' => 2, 'name' => '李g四', 'age' => 20],
        ['id' => 3, 'name' => '王五', 'age' => 22],    
    ],
    'footer' => [
        ['id' => '总计', 'age' => 60],
        ['id' => '平均', 'age' => 20],
    ],
]);
$table->render();
