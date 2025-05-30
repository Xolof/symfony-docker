<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    protected string $fileName;

    /**
     * Constructor
     *
     * @param string           $targetDirectory The directory in which to save the file.
     * @param SluggerInterface $slugger         An interface for making slugs.
     */
    public function __construct(
        protected string $targetDirectory,
        protected SluggerInterface $slugger
    ) {
    }

    /**
     * Execute the file upload.
     *
     * @param UploadedFile $file A file uploaded through a form.
     *
     * @return string
     */
    public function upload(UploadedFile $file): string
    {
        $originalFilename = pathinfo((string) $file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        $file->move($this->getTargetDirectory(), $fileName);

        return $fileName;
    }

    /**
     * Get the directory in which to save the file.
     *
     * @return string
     */
    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }
}
