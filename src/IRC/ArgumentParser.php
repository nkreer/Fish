<?php

namespace IRC;

/**
 * @class ArgumentParser
 * @author Niklas Kreer
 * @license Public Domain
 *
 * Parse Command Line Arguments
 */
class ArgumentParser{

    /**
     * @param $string
     * @param $options
     * @return array
     */
    public static function parse($string, $options){
        $string = explode(" ", $string);
        $result = [];
        foreach($string as $key => $arg){
            foreach($options as $option){
                if($arg === "--".$option or $arg === "-".$option[0]){
                    if(!empty($string[$key + 1])){
                        $result[$option] = $string[$key + 1];
                        unset($string[$key]);
                        unset($string[$key + 1]);
                    } else {
                        $result[$option] = false;
                        unset($string[$key]);
                    }
                }
            }
        }
        $result["text"] = implode(" ", $string);
        return $result;
    }

}