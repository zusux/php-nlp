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

(new main())->run();

class main{

    public function run(){
        $db = Config::getInstance()->getDb();
        $mongo = Config::getInstance()->getMongo();
        $page = 1;
        $limit = 5;
        while(true){
            $offset = ($page -1) * $limit ;

            $data = $db->table("ttg_x_recruitment")->limit($limit,$offset)->select();
            if(!$data){
                break;
            }
            foreach($data as $k=>$item){
                $record = $db->table("ttg_x_recruitment_content")
                    ->where('recruitment_id',$item ['id'])
                    ->find();
                if($record){
                    $this->processHtml($record['content'],$map,$table);
                    $data[$k]['map'] = $map;
                    $data[$k]['table'] = $table;
                }
            }
            try {
                $count = $mongo
                    ->database("recruitment")
                    ->table("data")
                    ->insertAll($data);
                echo $count." ";
            }catch (\Exception $exception){
                echo sprintf("exception: [%s]\r\n",$exception->getMessage());
            }

            $page ++;
        }


    }

    public function processHtml($html,&$mapData=[],&$tableData =[]){

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
        $content = preg_replace("/\t+/","\t",$content,-1);
        $content = str_replace(["\r\n", "\n", "\r"],"|n",$content);
        $content = preg_replace("/[(\|n)]+/","|n",$content);
        $lineArr = explode("|n",$content);


        $newArr = [];
        foreach($lineArr as $k=>$line){
            $line = trim($line);
            if($line ){
                $newArr[] = $line;
            }
        }

        //file_put_contents("./line.txt",print_r($newArr,true));

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
        $content = file_get_contents("http://www.ccgp.gov.cn/cggg/dfgg/zbgg/202103/t20210315_16021377.htm");
        return $content;
    }
}






