<?php
namespace App\_lib\Fido;

class FidoProvider
{
    private $schema = 'Fido';
    private $files = array();

    public function __construct()
    {
        $this->register();
    }

    /**
     * 呼び出されたクラスを名前空間から
     * 新規にエンティティを生成し返す
     * @param String $name
     * @param Array $argument
     */
    public function __call(String $name, Array $argument)
    {
        if (isset($this->files[$name])) {
            require_once($this->files[$name][1]);
            $class = 'App\_lib\\'. $this->schema .'\\' . $this->files[$name][0];
            return new $class();
        } else {
            throw new Exception("Error Class Not Found", 1);
        }
    }

    /**
     * クラスファイルを検索
     */
    private function register()
    {
        $path = dirname(__FILE__) . '/*.{php}';
        foreach (glob($path, GLOB_BRACE) as $lib_file) {
            $this->loadClass($lib_file);
        }
    }

    /**
     * クラスファイルパス、クラス名を保存
     */
    private function loadClass($class_path)
    {
        $_path = explode('/', $class_path);
        $Class = explode('.', end($_path));
        $this->files[$Class[0]] = [
            $Class[0], $class_path
        ];
    }

}