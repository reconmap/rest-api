<?php declare(strict_types=1);

namespace Reconmap;

class AppVersion
{
    public const Current = 2_00_00;

    /**
     * @param int $numeric
     * @return string Converts a version from its numeric (10000) form to string (1.0.0)
     */
    public static function numericToString(int $numeric): string
    {
        $patch = $numeric % 100;
        $minor = floor($numeric / 100) % 100;
        $major = floor($numeric / 10000);
        return "$major.$minor.$patch";
    }
}
