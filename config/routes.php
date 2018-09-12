<?php

// @see http://php.net/manual/zh/function.preg-match.php#105924

return [
    ['/', 'index::index', 'GET'],
    ['/user/{id}', 'user::view', 'GET'],
];
