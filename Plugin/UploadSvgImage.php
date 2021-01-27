<?php

namespace Web200\ImageResize\Plugin;

use Closure;
use enshrined\svgSanitize\Sanitizer;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\File\Uploader;
use ReflectionProperty;
use Web200\ImageResize\Provider\Config;

class UploadSvgImage
{
    /**
     * Config
     *
     * @var Config $config
     */
    protected $config;

    /**
     * AuthorizeSvgUpload constructor.
     *
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * @param Uploader    $subject
     * @param callable    $proceed
     * @param string      $destinationFolder
     * @param string|null $newFileName
     */
    public function aroundSave(
        Uploader $subject,
        callable $proceed,
        $destinationFolder,
        $newFileName = null
    ) {

        if ($subject->getFileExtension() === 'svg' && $this->config->isSvgEnabled()) {
            $this->sanitizeTmpImages($subject);
            return $this->save($subject, $destinationFolder, $newFileName);
        }

        return $proceed($destinationFolder, $newFileName);
    }

    /**
     * Sanitize tmp images
     *
     * @return void
     */
    protected function sanitizeTmpImages(Uploader $uploader): void
    {
        /** @var Sanitizer $sanitizer */
        $sanitizer = new Sanitizer();

        /** @var bool $tmpFile */
        $tmpFile = $this->getTmpFile($uploader);
        if (isset($tmpFile['tmp_name'])) {
            /** @var string $dirtySVG */
            $dirtySVG = file_get_contents($tmpFile['tmp_name']);
            /** @var string $cleanSVG */
            $cleanSVG = $sanitizer->sanitize($dirtySVG);
            file_put_contents($tmpFile['tmp_name'], $cleanSVG);
        }
    }

    /**
     * Save
     *
     * @param Uploader    $subject
     * @param string      $destinationFolder
     * @param string|null $newFileName
     *
     * @return string[]|bool
     * @throws FileSystemException
     */
    protected function save(Uploader $subject, string $destinationFolder, string $newFileName = null)
    {
        $this->validateDestination($destinationFolder);

        /** @var string[] $tmpFile */
        $tmpFile = $this->getTmpFile($subject);
        if (!isset($tmpFile['tmp_name'])) {
            return false;
        }

        /** @var bool $enableFilesDispersion */
        $enableFilesDispersion = $this->isEnableFileDispersion($subject);
        /** @var bool $allowRenameFiles */
        $allowRenameFiles = $this->isAllowRenameFiles($subject);
        /** @var string $destinationFile */
        $destinationFile = $destinationFolder;
        /** @var string $fileName */
        $fileName = isset($newFileName) ? $newFileName : $tmpFile['name'];
        $fileName = Uploader::getCorrectFileName($fileName);
        if ($enableFilesDispersion) {
            $fileName = strtolower($fileName);
            /** @var string $dispersionPath */
            $dispersionPath  = Uploader::getDispersionPath($fileName);
            $destinationFile .= $dispersionPath;
            $this->createDestinationFolder($destinationFile);
        }

        if ($allowRenameFiles) {
            $fileName = Uploader::getNewFileName($this->addDirSeparator($destinationFile) . $fileName);
        }

        $destinationFile = $this->addDirSeparator($destinationFile) . $fileName;
        try {
            if ($this->moveFile($tmpFile['tmp_name'], $destinationFile)) {
                if ($enableFilesDispersion) {
                    $fileName = str_replace('\\', '/', $this->addDirSeparator($dispersionPath)) . $fileName;
                }
                /** @var string[] $result */
                $result         = $tmpFile;
                $result['path'] = $destinationFolder;
                $result['file'] = $fileName;

                $this->afterSaveCallback($result);

                return $result;
            }
        } catch (\Exception $e) {
            if (file_exists($destinationFile)) {
                $result = true;
            } else {
                throw $e;
            }
        }

        return false;
    }

    /**
     * Validates destination directory to be writable
     *
     * @param string $destinationFolder
     *
     * @return void
     * @throws FileSystemException
     */
    protected function validateDestination(string $destinationFolder): void
    {
        $this->createDestinationFolder($destinationFolder);
        if (!is_writable($destinationFolder)) {
            throw new FileSystemException(__('Destination folder is not writable or does not exists.'));
        }
    }

    /**
     * Create destination folder
     *
     * @param string $destinationFolder
     *
     * @return void
     * @throws FileSystemException
     */
    function createDestinationFolder(string $destinationFolder): void
    {
        if (!$destinationFolder) {
            return;
        }

        if (substr($destinationFolder, -1) == '/') {
            $destinationFolder = substr($destinationFolder, 0, -1);
        }

        if (!(@is_dir($destinationFolder)
            || @mkdir($destinationFolder, 0777, true)
        )) {
            throw new FileSystemException(__('Unable to create directory %1.', $destinationFolder));
        }
    }

    /**
     * Get tmp file
     *
     * @param Uploader $uploader
     *
     * @return null|string[]
     */
    protected function getTmpFile(Uploader $uploader): ?array
    {
        /** @var bool $closure */
        $closure = Closure::bind(function (Uploader $uploader) {
            return $uploader->_file;
        }, null, Uploader::class);

        return $closure($uploader);
    }


    /**
     * Is enable file dispersion
     *
     * @param Uploader $uploader
     *
     * @return bool
     */
    protected function isEnableFileDispersion(Uploader $uploader): bool
    {
        /** @var bool $closure */
        $closure = Closure::bind(function (Uploader $uploader) {
            return $uploader->_enableFilesDispersion;
        }, null, Uploader::class);

        return $closure($uploader);
    }

    /**
     * Is allow rename files
     *
     * @param Uploader $uploader
     *
     * @return bool
     */
    protected function isAllowRenameFiles(Uploader $uploader): bool
    {
        /** @var bool $closure */
        $closure = Closure::bind(function (Uploader $uploader) {
            return $uploader->_allowRenameFiles;
        }, null, Uploader::class);

        return $closure($uploader);
    }

    /**
     * Add directory separator
     *
     * @param string $dir
     * @return string
     */
    protected function addDirSeparator(string $dir): string
    {
        if (substr($dir, -1) != '/') {
            $dir .= '/';
        }
        return $dir;
    }

    /**
     * Move files from TMP folder into destination folder
     *
     * @param string $tmpPath
     * @param string $destPath
     * @return bool
     */
    protected function moveFile(string $tmpPath, string $destPath): bool
    {
        if (is_uploaded_file($tmpPath)) {
            return move_uploaded_file($tmpPath, $destPath);
        } elseif (is_file($tmpPath)) {
            return rename($tmpPath, $destPath);
        }
        return false;
    }

    /**
     * After save call back function
     *
     * @param $result
     *
     * @return UploadSvgImage
     */
    public function afterSaveCallback(array $result): UploadSvgImage
    {
        return $this;
    }
}
