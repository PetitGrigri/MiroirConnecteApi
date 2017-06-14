<?php
namespace MirrorApiBundle\DBAL\Types;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class PositionType extends Type
{

    const ENUM_POSITION         = 'enum_position';
    const POSITION_TOP_LEFT     = 'top_left';
    const POSITION_TOP_RIGHT    = 'top_right';
    const POSITION_TOP          = 'top';
    const POSITION_BOTTOM_LEFT  = 'bottom_left';
    const POSITION_BOTTOM_RIGHT = 'bottom_right';
    const POSITION_BOTTOM       = 'bottom';
    const POSITION_LEFT         = 'left';
    const POSITION_RIGHT        = 'right';

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return "ENUM('".PositionType::POSITION_BOTTOM_LEFT."', ".
            "'".self::POSITION_TOP_LEFT."', ".
            "'".self::POSITION_TOP_RIGHT."', ".
            "'".self::POSITION_TOP."', ".
            "'".self::POSITION_BOTTOM_LEFT."', ".
            "'".self::POSITION_BOTTOM_RIGHT."', ".
            "'".self::POSITION_BOTTOM."', ".
            "'".self::POSITION_LEFT."', ".
            "'".self::POSITION_RIGHT."')";
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $value;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!in_array($value, array(self::POSITION_TOP_LEFT,
                                    self::POSITION_TOP_RIGHT,
                                    self::POSITION_TOP,
                                    self::POSITION_BOTTOM_LEFT,
                                    self::POSITION_BOTTOM_RIGHT,
                                    self::POSITION_BOTTOM,
                                    self::POSITION_LEFT,
                                    self::POSITION_RIGHT ))) {
            throw new \InvalidArgumentException("Invalid POSITION");
        }
        return $value;
    }

    public function getName()
    {
        return self::ENUM_POSITION;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}