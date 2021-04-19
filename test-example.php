<?php

use PierreMiniggio\YoutubeThumbnailUploader\ThumbnailUploader;

require __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$uploader = new ThumbnailUploader();
$uploader->upload(
    'accessToken',
    'videoId',
    'thumbnail.png'
);
