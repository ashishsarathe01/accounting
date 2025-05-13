<?php

if (!function_exists('formatIndianNumber')) {
    function formatIndianNumber($number) {
        //$number = 45.2;
        $number = (string)$number;
        $number = str_replace(',', '', $number);
        $decimal = '';

        if (strpos($number, '.') !== false) {
            list($number, $decimal) = explode('.', $number);
            $decimal = '.' . $decimal;
        }

        $lastThree = substr($number, -3);
        $rest = substr($number, 0, -3);        
        if ($rest != '') {
            
            if(strlen($decimal)==2){
                $decimal = $decimal.'0';
            }else if(strlen($decimal)==0){
                $decimal = $decimal.'.00';
            }
            $rest = preg_replace("/\B(?=(\d{2})+(?!\d))/", ",", $rest);            
            return $rest . ',' . $lastThree . $decimal;
        } else {     
            if(strlen($decimal)==2){
                $decimal = $decimal.'0';
            }
            if($lastThree!="" && $decimal==""){
                $decimal = ".00";
            }
            return $lastThree . $decimal;
        }
    }
}