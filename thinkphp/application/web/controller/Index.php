<?php

namespace app\web\controller;

use app\web\controller\Base;

class Index extends Base {

    public function index(){
        return $this->end(1, [1]);
    }

    public function mysql(){
        try {
            $dbh = new \PDO('mysql:host=10.0.4.23;dbname=phone_activity', 'hubo', '1234567Myc' );
            $dbh->query('set names utf8;');
            $rows = $dbh->query('SELECT * from activity')->fetchAll(\PDO::FETCH_ASSOC);
            $rs = array();
            foreach($rows as $row) {
                $rs[] = $row;
            }
            $dbh = null;
            dump($rows);
        } catch (\Exception $e) {
            $dbh->rollBack();
            echo 'Failed: ' . $e->getMessage();
        }
    }

    public function python(){
        $cmd = system("/usr/bin/python /home/zhangkefei/tmp/1_check_es12.py 三角兽",$ret);
        echo("ret is $ret ");
    }
}

 ?>
