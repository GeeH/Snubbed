<?php
/**
 * Created by Gary Hockin.
 * Date: 29/04/15
 * @GeeH
 */
namespace Snubbed;

/**
 * Class FileWriter
 * @package Snubbed
 * @codeCoverageIgnore
 */
class FileWriter
{

    /**
     * @param $filename
     * @param $contents
     * @return int
     */
    public function write($filename, $contents)
    {
        $this->makeDirectories($filename);

        return file_put_contents($filename, $contents);
    }

    /**
     * @param $filename
     */
    private function makeDirectories($filename)
    {
        $path = pathinfo($filename);
        if (file_exists($path['dirname'])) {
            return;
        }
        $directories   = explode('/', $path['dirname']);
        $directoryName = './';

        foreach ($directories as $directory) {
            $directoryName .= $directory . '/';
            if (!file_exists($directoryName)) {
                mkdir($directoryName);
            }
        }

        return;
    }
}