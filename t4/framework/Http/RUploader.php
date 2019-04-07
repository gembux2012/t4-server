<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 02.04.19
 * Time: 8:44
 */

namespace T4\Http;

use T4\Core\Exception;
use T4\Mvc\Application;
use React\ChildProcess\Process;
use React\Stream\WritableResourceStream;
use function React\Promise\Stream\unwrapWritable;
use RuntimeException;


class RUploader
{
    public function __construct($name = '', $exts = [])
    {
        $this->formFieldName = $name;
        $this->setAllowedExtensions($exts);
    }

    public function setPath($path)
    {
        $this->uploadPath = $path;
        return $this;
    }

    public function setAllowedExtensions($exts)
    {
        $this->allowedExtensions = $exts;
    }

    public function __invoke($name = '')
    {

        if (empty($this->formFieldName) && !empty($name))
            $this->formFieldName = $name;

        if (empty($this->formFieldName))
            throw new Exception('Empty form field name for file upload');

        if (empty($this->uploadPath))
            throw new Exception('Invalid upload path');

        $realUploadPath = \T4\Fs\Helpers::getRealPath($this->uploadPath);
        if (!is_dir($realUploadPath)) {
            try {
                \T4\Fs\Helpers::mkDir($realUploadPath);
            } catch (\T4\Fs\Exception $e) {
                throw new Exception($e->getMessage());
            }
        }

            $request = Application::instance()->request->request;
            $loop = Application::instance()->loop;

        $files = $request->getUploadedFiles()[$this->formFieldName];
        if(!$files){
            throw new Exception('File for \'' . $this->formFieldName . '\' is not uploaded');
        }
        $ret = [];
        /** @var \Psr\Http\Message\UploadedFileInterface $file */
        foreach ($files as $n => $file){

            $name=$file->getClientFilename();
            $max_filesize=ini_get('upload_max_filesize ')ж
            if ($file->getSize() > ini_get($max_filesize)){
                throw new RuntimeException('Файл больше '.$max_filesize.' Mb');
                }

            $filesystem = \React\Filesystem\Filesystem::create($loop);
           // $file = unwrapWritable($filesystem->file($realUploadPath.DS.$name)->open('cw'));
            $file_w = $filesystem->file($realUploadPath.DS.$name);
           $file_w->putContents($file->getStream()->getContents());


            $ret[$n][$file->getClientMediaType()] = $this->uploadPath . '/' . $name;
        }
            return $ret;

    }

    protected function suggestUploadedFileName($path, $name)
    {
        if (!file_exists($path . DS . $name))
            return strtolower($name);

        $filename = pathinfo($name, \PATHINFO_FILENAME);
        $extension = pathinfo($name, \PATHINFO_EXTENSION);
        preg_match('~(.*?)(_(\d+))?$~', $filename, $m);
        $i = isset($m[3]) ? (int)$m[3] + 1 : 1;

        while (file_exists($path . DS . ($file = $m[1] . '_' . $i . '.' . $extension)))
            $i++;

        return strtolower($file);
    }


}