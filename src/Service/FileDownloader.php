<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Service;

use Shopware\Core\Content\Media\File\MediaFile;
use Shopware\Core\Content\Media\MediaService;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

class FileDownloader
{
    private MediaService $mediaService;

    public function __construct(
        MediaService $mediaService
    ) {
        $this->mediaService = $mediaService;
    }

    /**
     * Borrowed from \Shopware\Core\Content\ImportExport\DataAbstractionLayer\Serializer\Entity\MediaSerializer::fetchFileFromURL
     */
    public function download(string $url, string $extension): ?MediaFile
    {
        $request = new Request();
        $request->query->set('url', $this->ensureHttps($url));
        $request->query->set('extension', $extension);
        $request->request->set('url', $url);
        $request->request->set('extension', $extension);
        $request->headers->set('content-type', 'application/json');

        try {
            $file = $this->mediaService->fetchFile($request);
            if ($file->getFileSize() > 0) {
                return $file;
            }
        } catch (Throwable $throwable) {
        }

        return null;
    }

    private function ensureHttps(string $url): string
    {
        return str_replace('http://', 'https://', $url);
    }
}