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
    public function write($filename, $contents)
    {
        return file_put_contents($filename, $contents);
    }
}