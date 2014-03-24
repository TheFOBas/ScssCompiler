<?php


namespace Plugin\ScssCompiler;


class ScssCompiler
{



    /**
     * @param string $themeName
     * @param string $lessFile
     * @return string
     */
    public function compileFile($fileName)
    {
        require_once('Lib/leafo/scss.inc.php');

        //get config variables
        $configModel = \Ip\Internal\Design\ConfigModel::instance();
        $config = $configModel->getAllConfigValues(ipConfig()->theme());

        //get options
        $model = \Ip\Internal\Design\Model::instance();
        $theme = $model->getTheme(ipConfig()->theme());
        $options = $theme->getOptionsAsArray();

        //create vars
        $variableScss = $this->generateScssVariables($options, $config);

        //get scss
        $string_sass = file_get_contents(ipThemeFile($fileName));

        //merge vars
        $string_sass = $variableScss . $string_sass;

        //compile
        $scss_compiler = new \scssc();
        $scss_compiler->setImportPaths(ipThemeFile(''));
        $string_css = $scss_compiler->compile($string_sass);

        //put css
        file_put_contents(ipThemeFile($this->getNewName($fileName)), $string_css);

        return true;
    }

    private function generateScssVariables($options, $config)
    {
        $scss = '';

        foreach ($options as $option) {
            if (empty($option['name']) || empty($option['type'])) {
                continue; // ignore invalid nodes
            }

            if (!empty($config[$option['name']])) {
                $rawValue = $config[$option['name']];
            } elseif (!empty($option['default'])) {
                $rawValue = $option['default'];
            } else {
                continue; // ignore empty values
            }

            switch ($option['type']) {
                case 'color':
                    $scssValue = $rawValue;
                    break;
                case 'hidden':
                case 'range':
                    $scssValue = $rawValue;
                    if (!empty($option['units'])) {
                        $scssValue .= $option['units'];
                    }
                    break;
                default:
                    $scssValue = json_encode($rawValue);
            }

            $scss .= "\n\${$option['name']}: {$scssValue}; ";
        }

        return $scss;
    }

    private function getNewName($fileName){
        return substr($fileName, 0, -4) . 'css';
    }


}