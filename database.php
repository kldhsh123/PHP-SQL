<?php
// PHP 文件名称：database.php

// 加密密码
$encryptionKey = 'SADSAF';

// 授权码与加密后的存储
$authorizationCodes = [
    'AAA' => true, // 替换为实际的授权码
];

// 是否启用 GET 请求
$enableGetRequest = true; // 允许 GET 请求

// 是否显示详细错误信息
$showDetailedErrors = true; // 设置为 true 以显示详细错误信息

// 存储数据
function store_data($name, $data, $authorizationCode, $encryptionKey) {
    $filename = 'storage/' . $name . '.txt';

    // 确保存储目录存在，如果不存在则创建
    if (!is_dir('storage/')) {
        mkdir('storage/');
    }

    // 加密数据
    $encryptedData = encrypt_data($data, $encryptionKey);

    // 尝试将数据写入文件
    if (file_put_contents($filename, $encryptedData) !== false) {
        return true; // 返回存储成功的标志
    } else {
        return false; // 存储失败
    }
}

// 覆盖存储数据（如果数据存在）
function overwrite_data($name, $data, $authorizationCode, $encryptionKey) {
    $filename = 'storage/' . $name . '.txt';
    if (file_exists($filename)) {
        // 如果文件存在，则覆盖存储数据
        $encryptedData = encrypt_data($data, $encryptionKey);
        if (file_put_contents($filename, $encryptedData) !== false) {
            return true; // 返回存储成功的标志
        } else {
            return false; // 存储失败
        }
    }
    return false; // 文件不存在，无法覆盖数据
}

// 读取数据
function retrieve_data($name, $encryptionKey) {
    $filename = 'storage/' . $name . '.txt';
    if (file_exists($filename)) {
        $encryptedData = file_get_contents($filename);
        $data = decrypt_data($encryptedData, $encryptionKey);
        return $data;
    }
    return null; // 文件不存在，返回 null
}

// 删除数据
function delete_data($name, $authorizationCode) {
    $filename = 'storage/' . $name . '.txt';
    if (file_exists($filename) && isset($authorizationCodes[$authorizationCode])) {
        if (unlink($filename)) {
            return true; // 返回删除成功的标志
        } else {
            return false; // 删除失败
        }
    } else {
        return false; // 文件不存在或未授权，无法删除数据
    }
}

// 加密数据
function encrypt_data($data, $encryptionKey) {
    return openssl_encrypt($data, 'aes-256-cbc', $encryptionKey, 0, substr($encryptionKey, 0, 16));
}

// 解密数据
function decrypt_data($encryptedData, $encryptionKey) {
    return openssl_decrypt($encryptedData, 'aes-256-cbc', $encryptionKey, 0, substr($encryptionKey, 0, 16));
}

// 处理存储请求
if ($_SERVER['REQUEST_METHOD'] === 'POST' || ($enableGetRequest && $_SERVER['REQUEST_METHOD'] === 'GET')) {
    // 接收请求数据
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $requestData = $_POST;
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $requestData = $_GET;
    }

    // 初始化响应数组
    $response = [];

    // 根据不同的 action 处理请求
    $action = isset($requestData['action']) ? $requestData['action'] : '';

    switch ($action) {
        case 'store':
            // 存储数据
            $name = isset($requestData['name']) ? $requestData['name'] : '';
            $data = isset($requestData['data']) ? $requestData['data'] : '';
            $authorizationCode = isset($requestData['authorization_code']) ? $requestData['authorization_code'] : '';

            // 验证授权码
            if (isset($authorizationCodes[$authorizationCode])) {
                if (overwrite_data($name, $data, $authorizationCode, $encryptionKey)) {
                    $response['status'] = 'success';
                    $response['message'] = '数据存储成功（覆盖现有数据）。';
                } else {
                    if (store_data($name, $data, $authorizationCode, $encryptionKey)) {
                        $response['status'] = 'success';
                        $response['message'] = '数据存储成功（新数据）。';
                    } else {
                        $response['status'] = 'error';
                        $response['message'] = '无法存储数据。';
                        if ($showDetailedErrors) {
                            $response['detailed_message'] = '存储数据时发生错误，请检查文件目录权限和文件系统状态。';
                        }
                    }
                }
            } else {
                $response['status'] = 'error';
                $response['message'] = '未经授权的访问。';
            }
            break;

        case 'retrieve':
            // 读取数据
            $name = isset($requestData['name']) ? $requestData['name'] : '';
            $authorizationCode = isset($requestData['authorization_code']) ? $requestData['authorization_code'] : '';

            // 验证授权码
            if (isset($authorizationCodes[$authorizationCode])) {
                $data = retrieve_data($name, $encryptionKey);
                if ($data !== null) {
                    $response['status'] = 'success';
                    $response['data'] = $data;
                } else {
                    $response['status'] = 'error';
                    $response['message'] = '未找到数据。';
                    if ($showDetailedErrors) {
                        $response['detailed_message'] = '无法读取指定的数据文件，请确认文件是否存在并且访问权限正确。';
                    }
                }
            } else {
                $response['status'] = 'error';
                $response['message'] = '未经授权的访问。';
            }
            break;

        case 'delete':
            // 删除数据
            $name = isset($requestData['name']) ? $requestData['name'] : '';
            $authorizationCode = isset($requestData['authorization_code']) ? $requestData['authorization_code'] : '';

            if ($_SERVER['REQUEST_METHOD'] === 'POST' || ($enableGetRequest && $_SERVER['REQUEST_METHOD'] === 'GET')) {
                if (isset($authorizationCodes[$authorizationCode])) {
                    if (delete_data($name, $authorizationCode)) {
                        $response['status'] = 'success';
                        $response['message'] = '数据删除成功。';
                    } else {
                        $response['status'] = 'error';
                        $response['message'] = '无法删除数据（文件不存在或未授权）。';
                        if ($showDetailedErrors) {
                            $response['detailed_message'] = '无法删除指定的数据文件，请确认文件是否存在并且授权码有效。';
                        }
                    }
                } else {
                    $response['status'] = 'error';
                    $response['message'] = '未经授权的访问。';
                }
            } else {
                $response['status'] = 'error';
                $response['message'] = '不支持的请求方法。';
            }
            break;

        default:
            $response['status'] = 'error';
            $response['message'] = '无效的操作。';
            break;
    }

    // 输出 JSON 格式的响应
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    // 输出错误信息
    echo json_encode(['status' => 'error', 'message' => '不支持的请求方法。']);
}
?>
