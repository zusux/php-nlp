<?php
namespace tools;

class relation{

    private $debug = false;

    public function handler($section,&$rel){
        $titleLine = "";
        foreach($section as $i=>$line){
            if($i == 0){
                $line = $this->replaceS($line);
                $titleLine = $line;
                $section[$i] = $line;
            }
            if($this->isEmpty($line)){
                $section[$i] = "";
            }else{
                if(!$this->isLable($line)){
                    if( $this->searchPos($line,$symbol)){
                        $arr = explode($symbol,$line);
                        $count = floor(count($arr)/2);
                        for($k=0;$k<$count;$k++){
                            $key = trim($arr[$k*2]);
                            $key = $this->replaceS($key);
                            $key = $this->subreplaceS($key);
                            $key = $this->replaceK($key);
                            $rel[$key] = trim($arr[$k*2+1]);
                        }
                        $section[$i] = "";
                    }
                }
            }
        }
        $map = [];
        $map['title'] = $titleLine;
        $map['line'] = $section;
        return $map;
    }



    private function searchPos($line,&$symbol){
        $symbols = ["："];
        foreach($symbols as $item){
            $flag = mb_strpos($line,$item);
            if( $flag !== false){
                $symbol = $item;
                return true;
            }
        }
        return false;
    }

    private function isEmpty($line){
        if($line){
            return preg_match("/^\s+$/",$line,$matches);
        }else{
            return true;
        }
    }

    private function isLable($str){
        $bool = preg_match("/\[[(line)(table)]\d+\]/",$str,$matches);
        return $bool;
    }

    private function replaceS($line){
        return preg_replace("/^[(\s)( )(（)]*?[(一)(二)(三)(四)(五)(六)(七)(八)(九)(十)]+\s*?[、：:\.]/u","",$line,1);
    }
    private function subreplaceS($line){
        return preg_replace("/^[(\s)( )(（)]*?[0-9]+\s*?[、：:\.]/u","",$line,1);
    }

    private function replaceK($line){
        return preg_replace("/^[（）]/u","",$line,1);
    }

    private function tokenize($str)
    {
        $arr = array();
        $PATTERN = '/[\pZ\pC]+/u';
        return preg_split($PATTERN,$str,null,PREG_SPLIT_NO_EMPTY);
    }

}
