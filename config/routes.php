<?php

// @see http://php.net/manual/zh/function.preg-match.php#105924

return [
    ['/', 'index::index', 'GET'],
    ['/user/{id:\d+}', 'user::view', 'GET'],
    // 为了便于在浏览器中演示同样添加 GET 方法
    ['/user/register', 'user::register', ['POST', 'GET']],
];
