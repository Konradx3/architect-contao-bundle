<?php

namespace Architect\ContaoCommandBundle\Helper;

use Symfony\Component\Filesystem\Filesystem;

class FileManager
{
    /**
     * Checks whether the file exists at the given path.
     *
     * @param string $filePath File path.
     * @return bool
     */
    public static function fileExists(string $filePath): bool
    {
        $filesystem = new Filesystem();
        return $filesystem->exists($filePath);
    }

    /**
     * Creates a directory path.
     *
     * @param string $directoryPath Path to the directory.
     */
    public static function createDirectoryPath(string $directoryPath): void
    {
        $filesystem = new Filesystem();
        $filesystem->mkdir($directoryPath);
    }

    /**
     * Creates a new file at the given path.
     *
     * @param string $filePath File path.
     * @param string $content Content to save to file (optional).
     */
    public static function createFile(string $filePath, string $content = ''): void
    {
        $filesystem = new Filesystem();
        $directoryPath = pathinfo($filePath, PATHINFO_DIRNAME);
        self::createDirectoryPath($directoryPath);

        $filesystem->dumpFile($filePath, $content);
    }

    /**
     * Checks whether the specified file can be opened.
     *
     * @param string $filePath Path file.
     * @return bool
     */
    public static function canOpenFile(string $filePath): bool
    {
        return file_exists($filePath);
    }


    /**
     * Adds content to an existing file.
     *
     * @param string $filePath Path file.
     * @param string $content Content to be added.
     * @return bool
     */
    public static function appendToFile(string $filePath, string $content): bool
    {
        try
        {
            $fileHandle = fopen($filePath, 'a');

            if ($fileHandle === false)
            {
                return false;
            }

            fwrite($fileHandle, $content);
            fclose($fileHandle);

            return true;
        }
        catch (\Throwable $e)
        {
            return false;
        }
    }
}