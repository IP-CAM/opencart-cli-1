<?php

/**
 * Class BaseCommandCli
 *
 * @author Istvan Dobrentei
 * @link https://www.dobrenteiistvan.hu
 */
class BaseCommandCli
{
    const SUCCESS_RESULT = 'ok';
    const FAILED_RESULT = 'error';

    protected function result($code, $value=0)
    {
        $result = array('result' => $code, 'value' => $value);
        echo json_encode($result);
    }
}