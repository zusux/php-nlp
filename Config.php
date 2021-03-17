<?php
namespace main;


use zusux\Db;
use zusux\PhpMongodb;

class Config {

    private static $instance = null;
    private $_conf;
    private $envFile = "env.yaml";

    private function __construct($filePath=""){
        if($filePath){
            $this->envFile = $filePath;
        }
        $this->init();
    }
    private function __clone(){}
    public static function getInstance($filePath="")
    {
        //检测当前类属性$instance是否已经保存了当前类的实例
        if (self::$instance == null) {
            //如果没有,则创建当前类的实例
            self::$instance = new self($filePath);
        }
        //如果已经有了当前类实例,就直接返回,不要重复创建类实例
        return self::$instance;
    }

    private function init(){
        if(!$this->_conf){
            $yaml = file_get_contents($this->envFile);
            // convert the YAML back into a PHP variable
            //yaml_emit("my.yaml",$config);
            $this->_conf = \yaml_parse($yaml);
        }
    }

    public function saveConf(){
        return \yaml_emit($this->envFile,$this->_conf);
    }

    public function getConfig(){
        if(!$this->_conf){
            $this->init();
        }
        return $this->_conf;
    }

    //mysql 数据库
    public function getDb(){
        $mysql = $this->_conf["mysql"];
        $db = Db::instance($mysql);
        return $db;
    }

    //mongo 数据库
    public function getMongo(){
        $mongoConf = $this->_conf["mongo"];
        $mongo = PhpMongodb::getInstance($mongoConf);
        return $mongo;
    }
}






