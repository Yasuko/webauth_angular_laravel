<?php
namespace App\_lib;

class MasterProvider
{
    private $files      = array();  // 取得したファイル一覧
    private $dirs       = array();  // 取得したディレクトリ一覧
    private $schema     = '';       // 呼び出しターゲットディレクトリ
    private $file       = '';       // 呼び出しClassファイル名
    private $cahcheKey  = 'ownLibMaster';

    public function __construct()
    {

    }

    /**
     * 呼び出されたクラスを名前空間から
     * 新規にエンティティを生成し返す
     * @param String $name
     * @param Array $argument
     * @return Object
     */
    public function __call(String $name, Array $argument): Object
    {
        $this->schema   = $name;
        var_dump($name);
        $this->file     = $argument[0];
        $this->register();

        if (isset($this->files[$name])) {
            require_once($this->files[$name][1]);
            $class = 'App\_lib\\'. $this->schema .'\\' . $this->files[$this->file][0];
            //$class = 'App\_lib\\'. $argument[0] .'\\' . $this->files[$name][0];
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
        /*
        $path = dirname(__FILE__) . '/'. $this->schema .'/*.{php}';
        foreach (glob($path, GLOB_BRACE) as $lib_file) {
            $this->loadClass($lib_file);
        }*/
        $path = glob(
                    dirname(__FILE__) . '/'. $this->schema .'/'. $this->file .'.php',
                    GLOB_BRACE)
                ;
        $this->loadClass($path[0]);
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