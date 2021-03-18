<?php
namespace main;

require_once "./tools/relation.php";
require_once "./tools/table.php";
require_once "./tools/section.php";
require_once "./Config.php";
require_once "./vendor/autoload.php";

use tools\relation;
use tools\table;
use tools\section;

(new example())->run();

class example{

    public function run(){
        $url = "http://www.ccgp.gov.cn/cggg/dfgg/zbgg/202103/t20210315_16021377.htm";
        $html = $this->getHtml($url);
        $this->processHtml($html,$map,$table);
        print_r($map);
        print_r($table);
    }

    public function processHtml($html,&$mapData=[],&$tableData =[]){
        //file_put_contents("html.html",$html);
        $pregScript = "/<script[\s\S]*?<\/script>/i";
        $pregStyle = "/<style[\s\S]*?<\/style>/i";
        $pregD = "/<div[\s\S]*?>([\s\S]*?)<\/div>/i";
        $pregP = "/<p[\s\S]*?>([\s\S]*?)<\/p>/i";
        $content = preg_replace($pregScript,"",$html,-1);
        $content = preg_replace($pregStyle,"",$content,-1);
        $content = preg_replace($pregD,"$1\r\n",$content,-1);
        $content = preg_replace($pregP,"$1\r\n",$content,-1);
        $content = str_replace(['&nbsp;','&gt;','&lt;','&raquo;'],'',$content);
        $content = str_replace('.','·',$content);
        $content = preg_replace('/<tr[\s\S]*?>([\s\S]*?)<\/tr>/i',"$1\r\n",$content,-1);
        $content = preg_replace('/<td[\s\S]*?>([\s\S]*?)<\/td>/i',"$1\t",$content);
        $content = strip_tags($content);
        $content = preg_replace("/\s*?\t+\s*?\t*?\s*?/","\t",$content,-1);
        $content = str_replace(["\r\n", "\n", "\r"],"|n",$content);
        $content = preg_replace("/[(\|n)]+/","|n",$content);
        $lineArr = explode("|n",$content);

        //file_put_contents("./line.txt",print_r($lineArr,true));
        $newArr = [];
        foreach($lineArr as $k=>$line){
            $line = trim($line);
            if($line ){
                $newArr[] = $line;
            }
        }

        //file_put_contents("./newline.txt",print_r($newArr,true));

        $tableData = (new table())->handler($newArr,$mapData);
        //段落索引
        $section = new section();
        $sectionIndex  = $section->handler($newArr);

        $sectionRaw = [];
        foreach($sectionIndex as $i=>$item){
            if(count($item)>0){
                $sections = array_slice($newArr,$item[0],$item[1]-$item[0]+1,true);
                $sectionRaw[] = $sections;
            }
        }


        //从段落中抽取关系
        $relation = new relation();

        $sectionLeftData = [];
        foreach($sectionRaw as $section ){
            $sectionLeftData[]  = $relation ->handler($section,$mapData);
        }
        //file_put_contents("./table.txt",print_r($tableData,true));
        //file_put_contents("./mapData.txt",print_r($mapData,true));
        return;
    }

    public function getHtml($url){
        $content = file_get_contents($url);
        return $content;
    }
}






