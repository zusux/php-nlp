<?php
namespace tools;
class table{

    private $debug = false;

    //处理程序
    public function handler(&$lineArr,&$mapData=[]){
        $res = $this->search($lineArr);
        $tablesData = $this->anaysisT($res,$lineArr,$mapData);
        return $tablesData;
    }


    //查询table
    private function search($lineArr){
        $arr = [];
        foreach($lineArr as $k=>$line){
            if($this->matchT($line,$matches)){

                $number = count($matches[0]);
                $arr[$k] = $number;
            }else{
                $arr[$k] = 0;
            }
        }
        //print_r($arr);
        //[0,0,0,3,5,5,5,5,5,0,0,0,0,9,9,9,9] 提取一段连续相同的索引

        $flag = true;
        $current = [];
        $result = [];
        $v = false;
        foreach($arr as $k=>$value){
            //没有值则跳过
            if(!isset($current[0])){
                //如果数组中没有值  第一个
                $v = $value;
                $current[] = $k;
            }else{
                //如果数组中有值
                if($value == $v){
                    //如果相等 继续添加
                    $current[] = $k;
                }else{
                    //如果不相等
                    if($current){
                        //收集数据
                        if($v){
                            reset($current);
                            $a[0] = current($current);
                            $a[1] = end($current);
                            $a['t'] = $v;
                            $result[] = $a;
                            $a = [];
                        }
                    }

                    $current = [];
                    $v = $value;
                    $current[] = $k;
                }
            }


            //最后一个
            if($k +1 == count($arr)){

                if($v){
                    reset($current);
                    $a[0] = current($current);
                    $a[1] = end($current);
                    $a['t'] = $v;
                    $result[] = $a;
                    $a = [];
                }
            }
        }
       return $result;
    }

    //分析表格并结构化
    private function anaysisT($res,&$lineArr,&$mapData){
        //file_put_contents("table.txt",print_r($res,true));
        $return = [];
        foreach($res as $i=>$item){
            $start = $item[0];
            $end = $item[1];
            $length = $end - $start + 1;
            $t = $item['t'];
            //echo "start: $start end: $end \r\n";
            $temp = array_slice($lineArr,$start,$length,true);
            //提取出的行置空数据
            foreach($temp as $key=>$value){
                $lineArr[$key] = "";
            }

            if($t ==1){
                foreach($temp as $k=>$line){
                    $record  = explode("\t",$line);
                    $key = trim($record[0]);
                    $key = $this->replaceS($key);
                    $key = $this->subreplaceS($key);
                    $key = $this->replaceK($key);

                    $mapData[$key] = $record[1];
                }
            }else{
                $first = true;
                $column = [];
                $arr = [];
                foreach($temp as $k=>$line){
                    $record  = explode("\t",$line);
                    if($first){
                        $column = $record;
                        $first = false;
                    }else{
                        foreach($record as $j=>$value){
                            $arr[$k][$column[$j]] = $value;
                        }
                    }
                }
                if($arr){
                    $return[$start] = $arr;
                }
            }

        }


        return $return;
    }

    //匹配制表符
    private function matchT($line,&$matches){
        return preg_match_all("/\t/",$line,$matches);
    }

    //是否是系统标签
    private function isLable($str){
        return preg_match("/\[[(line)(table)]\d+\]/",$str,$matches);
    }

    private function replaceS($line){
        return preg_replace("/^[(\s)( )(（)]*?[(一)(二)(三)(四)(五)(六)(七)(八)(九)(十)]+\s*?[、：:\.]/u","",$line,1);
    }
    private function subreplaceS($line){
        return preg_replace("/^[(\s)( )(（)]*?[0-9]+\s*?[、：:\.]/u","",$line,1);
    }

    private function replaceK($line){
        return preg_replace("/^[(\s)( )(（)]*?/u","",$line,1);
    }
}