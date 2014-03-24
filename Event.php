<?php

namespace Plugin\ScssCompiler;


class Event
{
    public static function ipBeforeController()
    {
        $compiler = new ScssCompiler();
        $files = json_decode(ipStorage()->get('ScssCompiles', 'files'));
        if (is_array($files) && ipConfig()->isDevelopmentEnvironment()){
            foreach ($files as $file){
                if (self::shouldRebuild($file)){
                    $compiler->compileFile($file);
                }
            }
        }
    }

    private static function shouldRebuild($fileName)
    {
        $file = realpath(ipThemeFile($fileName));
        $cssFile = substr($file, 0, -4) . 'css';
        if (!file_exists($cssFile)) {
            return true;
        }
        if (filemtime($file) > filemtime($cssFile)) {
            return true;
        }

        return false;
    }
}
