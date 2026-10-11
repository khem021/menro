<?php

namespace App\Support;

/**
 * Builds the pattern for a case-insensitive "contains" search that behaves the
 * same on PostgreSQL and MySQL. Use it as
 *
 *     ->whereRaw('LOWER(name) LIKE ?', [Like::contains($term)])
 *
 * The term is escaped so that a typed % or _ matches itself rather than acting
 * as a wildcard.
 */
class Like
{
    public static function contains(string $term): string
    {
        return '%' . self::escape(mb_strtolower(trim($term))) . '%';
    }

    public static function escape(string $term): string
    {
        return addcslashes($term, '%_\\');
    }
}
