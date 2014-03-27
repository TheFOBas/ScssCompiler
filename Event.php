<?php

namespace Plugin\ScssCompiler;


class Event
{
    public static function ipBeforeController()
    {
        $compiler = new ScssCompiler();
        $files = json_decode(ipStorage()->get('ScssCompiles', 'files'));
        if (is_array($files) && ipConfig()->isDevelopmentEnvironment() && self::shouldRebuild($files)){
            foreach ($files as $file){
                $compiler->compileFile($file);
            }
        }
    }
    private static function shouldRebuild($files)
    {
        $scssFiles = self::globRecursive(ipThemeFile('') . "*.scss");
        foreach ($files as $file){
            $file = realpath(ipThemeFile($file));
            $cssFile = substr($file, 0, -4) . 'css';
            if (!file_exists($cssFile)) {
                return true;
            }
            foreach ($scssFiles as $scssFile){
                if (filemtime($scssFile) > filemtime($cssFile)) {
                    return true;
                }
            }

            return false;
        }
    }

    protected static function globRecursive($pattern, $flags = 0)
    {
        //some systems return false instead of empty array if no matches found in glob function
        $files = glob($pattern, $flags);
        if (!is_array($files)) {
            return array();
        }

        $dirs = glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT);
        if (!is_array($dirs)) {
            return $files;
        }
        foreach ($dirs as $dir) {
            $files = array_merge($files, self::globRecursive($dir . '/' . basename($pattern), $flags));
        }

        return $files;
    }
}
