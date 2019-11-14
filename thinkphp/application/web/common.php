<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
//防SQL注入
function check_sql($word){
    $words = array();
    $words[] = "add";
    $words[] = "count";
    $words[] = "create";
    $words[] = "delete";
    $words[] = "drop";
    $words[] = "from";
    $words[] = "grant";
    $words[] = "insert";
    $words[] = "truncate";
    $words[] = "update";
    $words[] = "use";
    $words[] = "like";
    $words[] = "or";
    $words[] = "cas";
    $words[] = "rename";
    $words[] = "alter";
    $words[] = "modify";
    $words[] = "select";
    $words[] = "join";
    $words[] = "union";
    $words[] = "where";
    $words[] = "and";
    $words[] = "execute";
    $words[] = "chr";
    $words[] = "mid";
    $words[] = "master";
    $words[] = "truncate";
    $words[] = "char";
    $w = strtolower($word);
    if(stristr($w,'--')){
        return '';
    }
    if(preg_match("/[\(\)=\'\"]/",$w)){
        foreach($words as $t){
            if(preg_match("/$t\b/",$w)){
                return '';
            }
        }
    }
    return $word;
}

function array_sort($arr,$keys,$mode='nokeep',$type='asc'){
    $keysvalue = $new_array = array();
    foreach ($arr as $k=>$v){
        $keysvalue[$k] = iconv('UTF-8', 'GB2312',$v[$keys]);
    }
    if($type == 'asc'){
        asort($keysvalue);
    }else{
        arsort($keysvalue);
    }
    reset($keysvalue);
    if($mode=='keep'){
        foreach ($keysvalue as $k=>$v){
            $new_array[$k] = $arr[$k];
        }
    }else{
        foreach ($keysvalue as $k=>$v){
            $new_array[] = $arr[$k];
        }
    }

    return $new_array;
}
function download($filename){
    if(preg_match("/^http\:\/\//i",$filename)){
        header("location:$filename");
    }else{
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        $simplename = basename($filename);
        header('Content-Disposition: attachment; filename='.$simplename);
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filename));
        ob_clean();
        flush();
        readfile($filename);
    }
}
