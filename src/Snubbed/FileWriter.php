<?php
/**
 * Created by Gary Hockin.
 * Date: 29/04/15
 * @GeeH
 */

namespace Snubbed;


class FileWriter
{
    public function write($filename, $contents)
    {
        return file_put_contents($filename, $contents);
    }
}