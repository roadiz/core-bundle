<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Document;

use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\Documents\Packages;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * Easily create and serve ZIP archives from your Roadiz documents.
 */
final class DocumentArchiver
{
    private Packages $packages;

    /**
     * @param Packages $packages
     */
    public function __construct(Packages $packages)
    {
        $this->packages = $packages;
    }

    /**
     * @param array $documents
     * @param string $name
     * @param bool $keepFolders
     * @return string Zip file path
     */
    public function archive(array $documents, string $name, bool $keepFolders = true): string
    {
        $fs = new Filesystem();
        $filename = (new AsciiSlugger())->slug($name, '_') . '.zip';

        $tmpFileName = tempnam(sys_get_temp_dir(), $filename);
        if (false === $tmpFileName) {
            throw new \RuntimeException('Can\'t create temporary file');
        }

        $zip = new \ZipArchive();
        $zip->open($tmpFileName, \ZipArchive::CREATE);

        /** @var Document $document */
        foreach ($documents as $document) {
            if (null !== $rawDocument = $document->getRawDocument()) {
                $document = $rawDocument;
            }
            if ($document->isLocal()) {
                $documentPath = $this->packages->getDocumentFilePath($document);
                if ($fs->exists($documentPath)) {
                    if ($keepFolders) {
                        $zipPathname = $document->getFolder() . DIRECTORY_SEPARATOR . $document->getFilename();
                    } else {
                        $zipPathname = $document->getFilename();
                    }
                    $zip->addFile(
                        $documentPath,
                        $zipPathname
                    );
                }
            }
        }
        $zip->close();

        return $tmpFileName;
    }

    public function archiveAndServe(array $documents, string $name, bool $keepFolders = true, bool $unlink = true): Response
    {
        $filename = $this->archive($documents, $name, $keepFolders);
        $basename = (new AsciiSlugger())->slug($name, '_')->lower() . '.zip';
        $response = new Response(
            file_get_contents($filename),
            Response::HTTP_OK,
            [
                'cache-control' => 'private',
                'content-type' => 'application/zip',
                'content-length' => filesize($filename),
                'content-disposition' => 'attachment; filename=' . $basename,
            ]
        );

        if ($unlink) {
            unlink($filename);
        }

        return $response;
    }
}
