<?php

namespace org\util;

class Response {
    public $code = 1; //0 is failed, 1 is success.
    public $errorMsg = '';
    public $errorArray = [];
    public $data = '';

    public function judge($truth, $errorObj = '', $callback = null){
        if ($this->code === 0) {
            return $this;
        }
        if ($truth) { //如果判断条件为真
            if ($callback) {
                return $callback($this);
            } else {
                $this->code = 1;
                return $this;
            }
        } else {
            $this->code = 0;
            if ($errorObj && \is_array($errorObj)) {
                $this->errorArray = $errorObj;
            } elseif ($errorObj && \is_string($errorObj)) {
                $this->errorMsg = $errorObj;
            }
            return $this;
        }
    }
}

 ?>
