<?php

namespace Cmuset\PgnParser\Enum;

enum ResultEnum: string
{
    case WHITE_WINS = '1-0';
    case BLACK_WINS = '0-1';
    case DRAW = '1/2-1/2';
    case ONGOING = '*';

    public static function fromColor(ColorEnum $color): ResultEnum
    {
        return ColorEnum::WHITE === $color ? self::WHITE_WINS : self::BLACK_WINS;
    }
}
