<?php
/**
 * Created by JetBrains PhpStorm.
 * User: savy_m
 * Date: 24/05/13
 * Time: 15:34
 * To change this template use File | Settings | File Templates.
 */

namespace MicroMuffin;

class Tools
{
    /**
     * @param string $name
     * @param null   $default
     * @return mixed
     */
    public static function getParam($name, $default = null)
    {
        return isset($_GET[$name]) ? $_GET[$name] : $default;
    }

    /**
     * @param string $str
     * @param int    $nbChars
     * @return string
     */
    public static function capitalize($str, $nbChars = 1)
    {
        for ($i = 0; $i < $nbChars; $i++)
        {
            $str[$i] = strtoupper($str[$i]);
        }
        return $str;
    }

    /**
     * @param string $sStr
     * @return string
     */
    public static function sanitizeForUrl($sStr)
    {
        $ISOTransChar = array("'"      => '-', ' "' => '-',
                              'áŕĺäâă' => 'a', 'ÁŔĹÄÂĂ' => 'A', 'éčëę' => 'e', 'ÉČËĘ' => 'E',
                              'íěďîĄ'  => 'i', 'ÍĚĎÎ' => 'I', 'óňöôőđ' => 'o', 'ř' => '0', 'ÓŇÖÔŐŘ' => 'O',
                              'ľúůüű'  => 'u', 'ÚŮÜŰ' => 'U', 'ý˙' => 'y', 'Ý' => 'Y',
                              'ć'      => 'ae', 'Ć' => 'AE', '' => 'oe', '' => 'OE',
                              'ß'      => 'B', 'ç' => 'c', 'Ç' => 'C', 'Đ' => 'D', 'ń' => 'n', 'Ń' => 'N',
                              'Ţ'      => 'p', 'ţ' => 'P', '' => 's', '' => 'S');

        $tmp = array();
        for ($c = 0; $c < strlen($sStr); $c++)
        {
            $carac = $sStr{$c};
            foreach ($ISOTransChar as $chars => $r)
            {
                if (strpos($chars, $sStr{$c}) > -1 || strpos(utf8_decode($chars), $sStr{$c}) > -1)
                {
                    $carac = $r;
                    break;
                }
            }
            $tmp[] = $carac;
        }

        $newStr = implode("", $tmp);
        $newStr = preg_replace('/--+/', '-', $newStr);
        $newStr = preg_replace('/([^a-z0-9_-])/i', '', $newStr);
        $newStr = preg_replace('/([-])$/', '', $newStr);
        $newStr = strtolower($newStr);

        return $newStr;
    }

    /**
     * @param int $car
     * @return string
     */
    public static function randomStr($car)
    {
        $string = "";
        $chaine = "abcdefghjklmnpqrstuvwxyABCDEFGHJKLMNPQRSTUVWXYZ123456789";
        srand((double)microtime() * 1000000);
        for ($i = 0; $i < $car; $i++)
            $string .= $chaine[rand() % strlen($chaine)];

        return $string;
    }

    /**
     * @param string $stripAccents
     * @return string
     */
    public static function stripAccents($stripAccents)
    {
        return strtr($stripAccents, 'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ', 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
    }

    /**
     * @param string $str
     * @return string
     */
    public static function removeSFromTableName($str)
    {
        if (strtolower($str[strlen($str) - 1]) == 's')
            $str = substr($str, 0, -1);
        return $str;
    }
}