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

            //$name=$file->getClientFilename();
            $name=$file->getClientFilename();
         /*
            $name= str_replace(' ', '_', $name);
            $process = new Process(
                "cat >".$realUploadPath.DS.$name );
            $process->start($loop);
            $process->stdin->end($file->getStream()->getContents());
            $process->on('error', function (Exception $e) {
                echo 'Ошибка: ' . $e->getMessage() . PHP_EOL;
            });
           */
            $output = new WritableResourceStream(STDOUT, $loop);
            $output->write($file);
            });
            $readable->on('end', function () use ($output) {
                $output->end();
                $output->write('Hello!');
            });
            $ret[$n] = $this->uploadPath . '/' . $name;

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