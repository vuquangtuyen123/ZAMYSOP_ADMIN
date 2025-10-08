<?php
session_start();

$c = $_GET['c'] ?? 'login';   // tên controller mặc định
$a = $_GET['a'] ?? 'login';   // tên action mặc định

// Ghép thành tên class controller (LoginController)
$controllerName = ucfirst($c) . 'Controller';

// Đường dẫn file controller
$controllerFile = __DIR__ . '/../controller/' . $c . '_controller.php';

if (file_exists($controllerFile)) {
    require_once $controllerFile;

    if (class_exists($controllerName)) {
        $controller = new $controllerName();

        if (method_exists($controller, $a)) {
            $controller->$a();
        } else {
            echo "Không tìm thấy action: $a trong $controllerName";
        }
    } else {
        echo "Không tìm thấy class: $controllerName";
    }
} else {
    echo "Không tìm thấy controller file: $controllerFile";
}
