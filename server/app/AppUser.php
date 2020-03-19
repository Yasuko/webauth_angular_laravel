<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Credentials;
/**
 * app_user
 * 
 * id                       bigint(20)      NOTNULL NONE    AUTO_INCREMENT
 * username                 varchar(255)    NOTNULL NONE    ユーザー名
 * userid                   varchar(255)    NOTNULL NONE    ユーザーID
 * registration_start       timestamp       NOTNULL current_timestamp() 登録開始時間
 * registration_token       varchar(255)    NOTNULL None    登録用トークン
 */
class AppUser extends Model
{
    const CREATED_AT = null;
    const UPDATED_AT = null;

    protected $table = 'app_user';


    /**
     * registrationTokenからユーザー情報を取得
     *
     * @param string $registrationToken
     * @return object
     */
    public static function getAppUserForRegistrationToken(
        string $registrationToken
    ): object
    {
        return self::where('registration_token', $registrationToken)
                ->first();
    }

    /**
     * Undocumented function
     *
     * @param array $appUser
     * @return self
     */
    public static function addAppUser(array $appUser): void
    {
        $_appUser = new self;
        $_appUser->username                 = $appUser['username'];
        $_appUser->userid                   = $appUser['userid'];
        $_appUser->registration_start       = Carbon::now();
        $_appUser->registration_token       = $appUser['registration_token'];
        $_appUser->save();
    }

    /**
     * IDからレコードを更新する
     *
     * @param array $appUser
     * @return void
     */
    public static function updateAppUserById(array $appUser): void
    {
        $_appUser = self::where('id', $appUser['id']);
        $_appUser->username                 = $appUser['username'];
        $_appUser->userid                   = $appUser['userid'];
        $_appUser->registration_start       = Carbon::now();
        $_appUser->registration_token       = $appUser['registration_token'];
        $_appUser->save();
    }

    /**
     * IDからレコードを削除する
     *
     * @param integer $id
     * @return void
     */
    public static function deleteAppUserById(int $id): void
    {
        self::where('id', $id)->delete();
    }

    /**
     * ユーザー名からレコードを削除する
     *
     * @param string $username
     * @return void
     */
    public static function deleteAppUserByUsername(string $username): void
    {
        self::where('username', $username)->delete();
    }

    /**
     * AppUserテーブルの情報を全て削除する
     *
     * @return void
     */
    public static function deleteAppUserAll(): void
    {
        self::query()->delete();
    }

    public function getAppUserForUserId(): object
    {

    }

    /**
     * AppUserにCredential情報を結合したデータを1件返す
     *
     * @param string $username
     * @return object
     */
    public static function getAppUserWithCredentialsForUsername(string $username): object
    {
        return self::where('username', $username)
                ->rightJoin('credentials', 'app_user.id', '=', 'credentials.app_user_id')
                ->get()
                ->first();
    }

    public static function getAppUserWithCredentialsForUserId(string $userid): object
    {
        return self::where('userid', $userid)
                ->rightJoin('credentials', 'app_user.id', '=', 'credentials.app_user_id')
                ->get()
                ->first();
    }

    /**
     * 登録用AppUserデータを作成し返す
     *
     * @param string $username
     * @param array $credentialHash
     * @return array
     */
    public static function buildRegistData(
        string $username, array $credentialHash): array
    {
        return array(
            'username'              => $username,
            'userid'                => $credentialHash['userId'],
            'registration_start'    => Carbon::now(),
            'registration_token'    => $credentialHash['registrationId']
        );
    }

    /**
     * ユーザーが登録済みか判定し返す
     *
     * @param string $username
     * @return bool
     */
    public static function checkNewUser(string $username): bool
    {
        $user = AppUser::where('username', $username)->get();
        if (!$user->isEmpty()) {
            return false;
        }
        return true;
    }
    

    /**
     * Credentailsとのリレーション用
     *
     * @return object
     */
    public function credential()
    {
        // return $this->hasOne('App\Credentials', 'id', 'app_user_id');
        return $this->hasOne('App\Credentials', 'app_user_id');
    }
}
