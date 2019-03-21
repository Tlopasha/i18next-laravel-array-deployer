<?php
/**
 * Created by PhpStorm.
 * User: tommasomatteini
 * Date: 2019-03-21
 * Time: 17:26
 */

namespace TommasoMatteini\i18NextLaravelArrayDeployer;


class ArrayDeployer
{

    private $data = array();

    public function __construct($data) {
        $this->data = $data;
        array_walk_recursive($this->data, array(__CLASS__, 'i18nextAdapt') );
    }

    private static function i18nextAddInterpolation($values, $prefix = '{{', $suffix = '}}') {
        return $value = $prefix . ltrim($values[0], ':') . $suffix;
    }

    private static function i18nextAdapt(&$values) {
        $values = preg_replace_callback('/\B:\w+/', array(__CLASS__, 'i18nextAddInterpolation'), json_decode(json_encode($values), TRUE));
    }

    
    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

}