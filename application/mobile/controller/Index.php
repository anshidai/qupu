<?php

namespace app\mobile\controller;

use think\Request;
use app\components\helper\ArrayHelper;
use app\components\Urls;

class Index extends Base
{
    public function index(Request $request)
    {
        header('Location: '.Urls::url('mobile_ciyu_category'));
        exit;
        return $this->fetch();
    }

   
}
