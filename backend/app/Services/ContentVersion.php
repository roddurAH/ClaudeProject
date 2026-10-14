<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * A short fingerprint of the offline knowledge base. The app stores it and
 * sends it back as If-None-Match; when nothing changed the server answers
 * 304 Not Modified and no data is downloaded.
 */
class ContentVersion
{
    private const TABLES = ['crops', 'crop_stages', 'problems', 'crop_fertilizer_doses', 'fertilizers', 'articles'];

    public function current(): string
    {
        $parts = [];
        foreach (self::TABLES as $table) {
            $row = DB::table($table)->selectRaw('COUNT(*) as c, MAX(updated_at) as m')->first();
            $parts[] = $table.':'.$row->c.':'.$row->m;
        }

        return substr(sha1(implode('|', $parts)), 0, 16);
    }
}
