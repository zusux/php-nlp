<?php
namespace tools;
class section{
    //处理程序
    public function handler(&$lineArr){
        return $data = $this->search($lineArr);
    }
    //匹配段落
    private function search(&$lineArr){
        $matchRes = [];
        $has = false;
        foreach($lineArr as $k=>$line){
            $isMatch  = $this->matchS($line);
            if($isMatch){
                $has = true;
            }
            $matchRes[$k] = $isMatch;
        }
        if(!$has){
            foreach($lineArr as $k=>$line){
                $isMatch  = $this->submatchS($line);
                if($isMatch){
                    $has = true;
                }
                $matchRes[$k] = $isMatch;
            }
        }

        //print_r($matchRes);
        //echo $this->dump($matchRes);
        //file_put_contents("section.txt",print_r($matchRes,true));
        //[1]
        //[1,0,1,0,1,0,0,0,1,0,1,0]  提取1开头 0结尾的一段数据
        $flag = true;
        $current = [];
        $result = [];
        $count = count($matchRes);
        foreach($matchRes as $k=>$value){

            if($value){
                if(isset($current[0])){
                    if(!isset($current[1])){
                        $current[1] = $k - 1;
                        $result[] = $current;
                        $current = [];
                    }else{
                        $result[] = $current;
                        $current = [];
                    }
                }
                $current[0] = $k;
            }else{
                if(!isset($current[0])){
                    $current[0] = $k;
                }else{
                    $current[1] = $k;
                }
            }

            if($k == $count -1){
                //最后一个
                $current[1] = $k;
                $result[] = $current;
            }
        }

        //echo $this->dump($result);
        return $result;
    }

    //正则匹配
    private function matchS($line){
        return preg_match("/^(\s)*?[(一)(二)(三)(四)(五)(六)(七)(八)(九)(十)]+\s*?[(、)(：)(:)(·)]+/",$line,$matches);
    }

    private function submatchS($line){
        return preg_match("/^(\s)*?[0-9]+\s*?[(、)(：)(:)(·)]+/",$line,$matches);
    }

    public function dump($arr){
        $str = "[";
        foreach($arr as $k=>$item){
            if(is_array($item)){
                $str .= "\t".$this->dump($item).", \r\n";
            }else{
                $str .= $item.", ";
            }
        }
        $str .= "]";
        return $str;
    }
    //是否是系统标签
    private function isLable($str){
        return preg_match("/\[[(line)(table)(section)]\d+\]/",$str,$matches);
    }

}
