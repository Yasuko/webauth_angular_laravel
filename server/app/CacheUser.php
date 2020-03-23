<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * cache_user
 * 
 * cache_id         varchar(255)    NOTNULL NONE    キャッシュID
 * cache_data       text            NOTNULL NONE    キャッシュデータ
 */

class CacheUser extends Model
{
    const CREATED_AT = null;
    const UPDATED_AT = null;

    protected $table = 'cache_user';

    /**
     * RPIDを保存する
     *
     * @param array $cache
     * @return void
     */
    public static function addCache(array $cache): void
    {
        $_cache = new self;
        $_cache->cache_id = $cache['registrationId'];
        $_cache->cache_data = json_encode(
            array(
                $cache['userId'],
                $cache['challenge']
            ),
            TRUE
        );
        $_cache->save();
    }

    /**
     * RPIDを検索し、ヒットした場合ID情報を返却
     * 保存情報は削除する
     *
     * @param string $id
     * @return array | bool
     */
    public static function searchCache(string $id)
    {
        $_cache = self::where('cache_id', $id)->first();
        if ($_cache) {
            $val = json_decode($_cache->cache_data, TRUE);
            self::where('cache_id', $id)->delete();
            return array($val[0], $val[1]);
        }
        return false;
    }
}
