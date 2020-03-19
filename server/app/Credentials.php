<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\AppUser;

/**
 * credentials
 * 
 * id               varchar(128)    NOTNULL NONE    
 * app_user_id      bigint(20)      NOTNULL NONE    ユーザーID
 * count            bigint(20)      NOTNULL NONE    カウンター
 * public_key       text            NOTNULL NONE    公開鍵
 */
class Credentials extends Model
{
    protected $table = 'credentials';
    const CREATED_AT = null;
    const UPDATED_AT = null;

    /**
     * 認証情報を保存
     *
     * @param array $credential
     * @return void
     */
    public static function addCredential(array $credential): void
    {
        $_credential = new self;
        $_credential->id                = $credential['id'];
        $_credential->app_user_id       = $credential['app_user_id'];
        $_credential->count             = $credential['count'];
        $_credential->credential_id     = $credential['credential_id'];
        $_credential->public_key        = $credential['public_key_cose'];
        $_credential->save();

    }

    /**
     * 認証情報を更新
     *
     * @param array $credential
     * @return void
     */
    public static function updateCredentialById(array $credential): void
    {
        $_credential = self::where('id', $credential['id'])->first();
        $_credential->id                = $credential['id'];
        $_credential->app_user_id       = $credential['app_user_id'];
        $_credential->count             = $credential['count'];
        $_credential->credential_id     = $credential['credential_id'];
        $_credential->public_key        = $credential['public_key_cose'];
        $_credential->save();
    }

    public static function updateCountById(array $user, int $count): void
    {
        $_credential = self::where('id', $user['userid'])->first();
        $_credential->count             = $count;
        $_credential->save();
    }

    /**
     * 認証情報を削除
     *
     * @param string $id
     * @return self
     */
    public static function deleteCredentailById(string $id): void
    {
        self::where('id', $id)->delete();
    }


    public static function getCredentialsWithAppUserForUsername(): object
    {
        return self::where('username', $username)
                ->addUser()
                ->get();
    }


    /**
     * AppUserとのリレーション用
     *
     * @return object
     */
    public function addUser(): object
    {
        return $this->belongsTo('App\AppUser');
    }
}
