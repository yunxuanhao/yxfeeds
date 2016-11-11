<?php
/**
 * Created by yx1@meitu.com
 * Date: 16/11/11
 * Time: 下午4:00
 */

namespace App\Models;


class Model
{
    private static $instance;

    public static function getInstance(){
        if(empty(self::$instance)){
            self::$instance = new self();
        }
        return self::$instance;
    }
}