<?php

// @see http://php.net/manual/zh/function.preg-match.php#105924

return [
    [
        'GET', '/', [
            'controller' => 'index',
            'action' => 'index',
        ]
    ],

    // ===================== 测试 =====================

    [
        // 测试
        'GET', '/test', [
            'controller' => 'index',
            'action' => 'test',
        ]
    ],

];
