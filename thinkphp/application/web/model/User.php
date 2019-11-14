<?php

namespace app\web\model;

use think\Model;

class User extends Model
{
    protected $table = 'user';

    protected $type = [
        'id'    =>  'integer'
    ];
}
