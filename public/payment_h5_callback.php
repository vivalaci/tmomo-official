<?php
// /www/wwwroot/tmomo.hk/public/payment_h5_callback.php

// 记录回调数据
$log_file = __DIR__ . '/payment_callback.log';
file_put_contents($log_file, date('Y-m-d H:i:s') . " - 收到H5回调请求\n", FILE_APPEND);
file_put_contents($log_file, "回调参数: " . print_r($_POST, true) . "\n", FILE_APPEND);

// 重定向到前端页面
$redirect_url = 'https://tmomo.hk/h5/#/pages/user-order/user-order?payment_success=1';
header('Location: ' . $redirect_url);
exit;