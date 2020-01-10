<?php
/**
 * SDK Exception
 *
 * @author xzh
 * @date 2018/3/22 下午10:03
 */

namespace app\components\Xzh\lib;


use Exception;

class XzhException extends Exception
{

    /**
     * XzhException constructor.
     * @param XzhError $error
     */
    public function __construct(XzhError $error)
    {
        parent::__construct($error->getErrorMsg(), $error->getErrorCode());
    }

}