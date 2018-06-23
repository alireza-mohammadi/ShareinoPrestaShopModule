<?php

class dokmeAuth
{
    public function auth()
    {
        if (empty($_SERVER['HTTP_AUTHORIZATION'])) {
            echo $this->_response(403);
            return false;
        }

        $authorization = $this->_readToken($_SERVER['HTTP_AUTHORIZATION']);
        if (!$authorization) {
            echo $this->_response(401);
            return false;
        }

        if ($authorization !== Configuration::get('SELLER_TOKEN')) {
            echo $this->_response(401);
            return false;
        }

        return true;
    }

    protected function _response($status = null)
    {
        $message = array(
            401 => 'Invalid authorization token.',
            403 => 'No authorization token was found.'
        );

        return json_encode(array('status' => false, 'error_code' => $status, 'message' => $message[$status]));
    }

    protected function _readToken($authorization)
    {
        if ($this->_startsWith($authorization, 'Bearer ')) {
            return substr($authorization, 7);
        }

        return false;
    }

    protected function _startsWith($haystack, $needle)
    {
        $length = strlen($needle);
        return substr($haystack, 0, $length) === $needle;
    }

}