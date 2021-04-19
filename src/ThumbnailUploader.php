<?php

namespace PierreMiniggio\YoutubeThumbnailUploader;

use PierreMiniggio\YoutubeThumbnailUploader\Exception\BadVideoIdException;
use PierreMiniggio\YoutubeThumbnailUploader\Exception\ThumbnailFeatureNotAvailableException;
use RuntimeException;

class ThumbnailUploader
{

    public function upload(
        string $accessToken,
        string $videoId,
        string $fileName
    ): void
    {

        $explodedPath = explode(DIRECTORY_SEPARATOR, $fileName);
        $postname = end($explodedPath);
        $ext = explode('.', $postname)[1];

        $mimeType = match ($ext) {
            'png' => 'image/png',
            'jpeg', 'jpg' => 'image/jpeg',
            default => 'application/octet-stream'
        };

        $curl = curl_init('https://www.googleapis.com/upload/youtube/v3/thumbnails/set?videoId=' . $videoId);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => ['file' => curl_file_create($fileName, $mimeType, $postname)]
        ]);
        $authorization = 'Authorization: Bearer ' . $accessToken;
        curl_setopt($curl, CURLOPT_HTTPHEADER, [$authorization]);

        $response = curl_exec($curl);

        if ($response === false) {
            throw new RuntimeException('Curl error' . curl_error($curl));
        }

        $jsonResponse = json_decode($response);

        if ($jsonResponse === null) {
            throw new RuntimeException('Bad youtube API return');
        }

        if (
            ! empty($jsonResponse->error)
            && $jsonResponse->error->code === 403
            && $jsonResponse->error->message = 'The authenticated user doesn\'t have permissions to upload and set custom video thumbnails.'
        ) {
            throw new ThumbnailFeatureNotAvailableException($jsonResponse->error->message);
        }

        if (
            ! empty($jsonResponse->error)
            && $jsonResponse->error->code === 403
        ) {
            throw new RuntimeException($jsonResponse->error->message);
        }

        if (
            ! empty($jsonResponse->error)
            && $jsonResponse->error->code === 404
        ) {
            throw new BadVideoIdException($jsonResponse->error->message);
        }

        if (! empty($jsonResponse->error)) {
            throw new RuntimeException($jsonResponse->error->message);
        }
    }
}
