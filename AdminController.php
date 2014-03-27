<?php

namespace Plugin\ScssCompiler; //Replace "YourPluginName" with actual plugin name

use Ip\Form\Field\Checkbox;

class AdminController extends \Ip\Controller{

    public function index(){

        $form = $this->getFilesForm();
        $formHtml = $form->render();

        return ipView('views/default.php', array('formHtml' => $formHtml));
    }

    public function update(){
        $form = $this->getFilesForm();
        $postData = ipRequest()->getPost();
        $errors = $form->validate($postData);

        if (!$errors){
            if (!isset($postData['file'])){
                ipStorage()->remove('ScssCompiles', 'files');
            } else {
                $files = array();
                foreach ($postData['file'] as $key => $value){
                    $files[] = $key;
                }
                ipStorage()->set('ScssCompiles', 'files', json_encode($files));
            }
        }

        return new \Ip\Response\Json(array('status' => 'sucsess', 'redirectUrl' => ipActionUrl(array('aa' => 'ScssCompiler')) ));
    }

    protected function getFilesForm(){
        //ignore files with underscore
        $files = $this->globRecursive(ipThemeFile('[!_]*') . "*.scss");

        $files_watched = json_decode(ipStorage()->get('ScssCompiles', 'files'));

        $form = new \Ip\Form();
        foreach ($files as $file){
            $file = substr($file, strlen(ipThemeFile('')));
            $form->addField(new \Ip\Form\Field\Checkbox(
                [
                    'name' => "file[$file]",
                    'label' => $file,
                    'checked' => (is_array($files_watched) && in_array($file, $files_watched)) ? 1 : 0
                ]
            ));
        }
        $form->addField(new \Ip\Form\Field\Hidden(array(
            'name' => 'aa',
            'value' => 'ScssCompiler.update'
        )));
        $form->addField(new \Ip\Form\Field\Submit(array(
            'value' => 'Update'
        )));
        return $form;
    }

    /**
     * Recursive glob function from PHP manual (http://php.net/manual/en/function.glob.php)
     */
    protected function globRecursive($pattern, $flags = 0)
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
            $files = array_merge($files, $this->globRecursive($dir . '/' . basename($pattern), $flags));
        }

        return $files;
    }
}