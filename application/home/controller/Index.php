<?php

namespace app\home\controller;

use think\Request;
use app\components\Urls;

class Index extends Base
{
    public function index(Request $request)
    {
        header('Location: '.Urls::url('home_ciyu_category'));
        exit;

        return $this->fetch();
    }

   
}
