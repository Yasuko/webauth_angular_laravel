<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\AppUser;
use App\Credentials;
use App\RedisServer;
use App\CacheUser;

use App\_lib\Fido\Fido;

class AuthController extends Controller
{

    private $authData;
    private $rpid   = 'yasukosan.dip.jp';
    private $rpname = 'yasukosan.desuyo';

    /**
     * 認証開始処理
     *
     * @param Request $request
     * @return array
     */
    public function authenticate(Request $request): array
    {
        // ユーザーの作成済みチェック
        $user = AppUser::getAppUserWithCredentialsForUsername($request->username);
            
        if (!$user) {
            return $user;
        }

        // Fidoヘルパー関数取得
        $cr = Fido::CredentialRepository();

        $clientCredentialOption = $cr
            // RP情報登録
            ->setRP([
                'id' => $this->rpid,
                'name' => $this->rpname])
            // クライアントからの情報を登録
            ->setClientRequest($request)
            // clientCredentailOptionを作成
            ->buildClientCredentialOptionToGet($user)
            // clientCredentialOptionを取得
            ->getClientCredentialOption();

        // RedisにRPID、Challenge、AssertionIdを登録
        // $redis = new RedisServer();
        // $redis->setKeyForFIDO($cr->getHashKeys());
        // データベースにRPIDを登録
        CacheUser::addCache(
            $cr->getHashKeys()
        );

        return $clientCredentialOption;
    }

    /**
     * 認証受付処理
     *
     * @param Request $request
     * @return string
     */
    public function authenticateFinish(Request $request): string
    {
        // AssertionId検証
        /*
        $redis = new RedisServer();
        $keys = $redis->searchKeyForFIDO($request['assertionId']);
         */
        $keys = CacheUser::searchCache($request['assertionId']);
        if (!$keys) {
            return false;
        }

        // ユーザーデータ取得
        $user = AppUser::getAppUserWithCredentialsForUserId($keys[0]);
        if (!$user) {
            return 'not user';
        }

        // AttestationRepository取得
        $count = Fido::AttestationRepository()
                ->easySetupToAuth($request['credential'], $user, $keys);

        if (!is_int($count)) {
            return $count;
        }
        // カウンター更新
        Credentials::updateCountById($user->toArray(), (int)$count);

        return true;
    }
}
