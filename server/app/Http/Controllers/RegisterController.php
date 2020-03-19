<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\AppUser;
use App\Credentials;
use App\RedisServer;

use App\_lib\Fido\Fido;
// use \CBOR\CBOREncoder;

class RegisterController extends Controller
{

    private $authData;
    private $rpid   = 'yasukosan.dip.jp';
    private $rpname = 'yasukosan.desuyo';

    /**
     * 新規登録処理開始
     *
     * @param Request $request
     * @return array
     */
    public function register(Request $request): array
    {
        // ユーザー情報を全削除
        // AppUser::deleteAppUserAll();

        // 作成済みのユーザーと被っていないか確認
        if (!AppUser::checkNewUser($request['username'])) {
            return false;
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
            ->buildClientCredentialOptionToCreate()
            // FIDO-U2Fサポートを追加
            ->addEC2KeyType()
            // clientCredentialOptionを取得
            ->getClientCredentialOption();

        // RedisにRPIDを登録
        $redis = new RedisServer();
        $redis->setKeyForFIDO($cr->getHashKeys());

        // データベースに登録
        AppUser::addAppUser(AppUser::buildRegistData(
            $request['username'],
            $cr->getHashKeys()
        ));

        return $clientCredentialOption;
    }

    /**
     * 認証受付処理
     *
     * @param Request $request
     * @return string
     */
    public function registrationFinish(Request $request): string
    {

        // Credentialヘルパー関数取得
        $cr = Fido::CredentialRepository();

        // registrationId検証
        $redis = new RedisServer();
        $keys = $redis->searchKeyForFIDO($request['registrationId']);
        if (!$keys) {
            return false;
        }
        // ユーザーデータ取得
        $user = AppUser::getAppUserForRegistrationToken($request['registrationId']);
        if (!$user) {
            return false;
        }

        // Attestationヘルパー関数取得
        $at = Fido::AttestationRepository()
                ->easySetupToRegist($request['credential'], $keys);

        // RP情報の検証
        if (
            $cr->setRP(['id' => $this->rpid, 'name' => $this->rpname])
                ->checkRP($at->callClientDataJsonRepository()->getclientData())
        ){
                // 取得した公開鍵の保存
                Credentials::addCredential(
                    $at->getCredentialSaveData($user)
                );
        } else {
            return false;
        }

        return 'success';
    }
}
