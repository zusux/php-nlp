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

        //[0,0,0,3,5,5,5,5,5,0,0,0,0,9,9,9,9] 提取一段连续相同的索引

        $flag = true;
        $current = [];
        $result = [];
        $count = count($arr);
        foreach($arr as $k=>$value){
            $next = $k +1;
            if($arr[$k] == $arr[$next] && $flag){
                //比较当前和下一个数据是否一样 并判断是否第一次进来
                $current[0] = $k;
                $flag = false;
            }else if($arr[$k] != $arr[$next] && !$flag){
                //比较当前和下一个数据是否不一样 并判断是否不是第一次进来
                $current[1] = $k;
                if($value){
                    $current['t'] = $value;
                    $result[] = $current;
                }
                $current = [];
                $flag = true;
            }

            if($k == $count - 2){
                //在数组倒数第二个时跳出
                if($k > 0){
                    if($arr[$k] == $arr[$next] && !$flag){
                        //如果当前和最后一个值一样 并且不是第一次进来则收集数据
                        $current[1] = $k+1;
                        if($value){
                            $result[] = $current;
                        }
                    }
                }
                break;
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