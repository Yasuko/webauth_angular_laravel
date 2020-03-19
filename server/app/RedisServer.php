<?php

namespace App;

// use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;

class RedisServer
{
    // Redisインスタンスの格納
    private $redis = null;
    
    public function __construct()
    {
        $this->setup();
    }

    /**
     * Redisから値を取得
     *
     * @param string $key 取得キー名
     * @return string 取得文字列
     */
    public function getKey(string $key): string
    {
        return $this->redis->get($key);
    }

    /**
     * Redisに値を登録する
     *
     * @param string $key 登録キー名
     * @param string $param 登録する値
     * @return void
     */
    public function setKey(string $key, string $param): void
    {
        $this->redis->set($key, $param);
    }

    /**
     * Redisに値を登録し
     * URLを返す
     *
     * @param string $key
     * @param string $param
     * @return string
     */
    public function setKeyToURL(string $key, string $param): string
    {
        $url = $key . '.' . $this->base_url;
        $this->redis->set($key, $param);
        return $url;
    }
    /**
     * Redisからキーを削除する
     * （データも消える）
     *
     * @param string $key
     * @return void
     */
    public function delKey(string $key): void
    {
        $this->redis->del([$key]);
    }

    /**
     * FIDO登録開始用、ハッシュキーの初期登録
     *
     * @param array $keys
     * @return void
     */
    public function setKeyForFIDO(array $keys): void
    {
        $this->setKey(
            $keys['registrationId'],
            json_encode(
                array(
                    $keys['userId'],
                    $keys['challenge']
                ),
                TRUE,
            ),
        );
    }

    /**
     * FIDO登録処理した、ハッシュキーが存在するか
     *
     * 存在時は紐付けされたデータ配列を返しキーを削除
     * 無い場合はFlaseを返す
     * @param string $key
     * @return array | bool
     */
    public function searchKeyForFIDO(string $key)
    {
        $val = $this->getKey($key);
        if ($val) {
            $this->delKey($key);
            
            $val = json_decode($val, TRUE);
            return array(
                $val[0], $val[1]
            );
        }

        return false;
    }

    /**
     * Redisインスタンスの作成
     * サーバーへの接続を行う
     *
     * @return void
     * インスタンス本体は「redis」変数に格納
     */
    private function setup(): void
    {
        if (!$this->redis) {
            $this->redis = Redis::connection();
        }
    }
    
}
