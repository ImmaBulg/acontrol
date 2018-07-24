<?php
namespace app\traits;

use dezmont765\yii2bundle\traits\TCallStaticUnique;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 15.03.2017
 * Time: 11:28
 * @method static place_top_left_menu
 * @method static place_top_right_menu
 */
trait TBaseLayout
{
    use TCallStaticUnique;


    public static function layout(array $active = []) {
        $nav_bar = [
            TBaseLayout::place_top_left_menu() => self::getTopLeftMenu($active),
            TBaseLayout::place_top_right_menu() => self::getTopRightMenu($active),
        ];
        return $nav_bar;
    }


    public static function getTopLeftMenu(array $active = []) {
        return [];
    }


    public static function getTopRightMenu(array $active = []) {
        return [];
    }
}