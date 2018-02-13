<?php

// @see http://php.net/manual/zh/function.preg-match.php#105924

return [
    [
        '/', [
            'controller' => 'index',
            'action' => 'index',
        ]
    ],

    // ===================== 测试 =====================

    [
        // 测试
        '/test', [
            'controller' => 'index',
            'action' => 'test',
        ]
    ],

];
