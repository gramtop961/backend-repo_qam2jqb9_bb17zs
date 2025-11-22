<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../helpers.php';

class UploadController {
    public static function upload() {
        global $config;
        if (!isset($_FILES['image'])) {
            send_json(['error' => 'No file uploaded'], 400);
        }
        $file = $_FILES['image'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            send_json(['error' => 'Upload error'], 400);
        }
        $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!isset($allowed[$mime])) {
            send_json(['error' => 'Invalid file type'], 415);
        }
        $ext = $allowed[$mime];
        $name = uniqid('img_', true) . '.' . $ext;
        $dest = rtrim($config['upload_dir'], '/').'/'.$name;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            send_json(['error' => 'Failed to move uploaded file'], 500);
        }
        $publicPath = '/api/uploads/' . $name; // adjust if your api path differs
        send_json(['success'=>true, 'path'=>$publicPath, 'filename'=>$name]);
    }
}
