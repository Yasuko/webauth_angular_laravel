<?php

namespace App\_lib\Proxmox;

use App\_lib\Proxmox\Helper\ProxmoxHelper;
use App\_lib\Proxmox\Helper\ProxmoxParseHelper;
use App\_lib\Proxmox\Helper\ProxmoxRequestHelper;

class ProxmoxRepository
{
    use ProxmoxHelper;
    use ProxmoxParseHelper;
    use ProxmoxRequestHelper;

    private $Builder        = 'Auth';
    private $Target         = 'Proxmox';
    private $Ticket         = null;
    private $CSRFToken      = null;

    private $Response       = array();
    private $ResponseBody   = array();

    private $Cookie         = array();
    private $Header         = array();

    private $baseURL        = 'https://192.168.2.220:8006/api2/json/';
    private $Server         = '192.168.2.220';
    private $Node           = 'infotec-proxmox1';
    private $endpoints      = array(
        'GetToken'      => 'access/ticket',
        'ListLXC'       => 'nodes/%s/lxc',
        'AddLXC'        => 'nodes/%s/lxc',
        'DelLXC'        => 'nodes/%s/lxc/%s',
    );

    private $ERROR          = array();
    /**
     * 接続に使うトークン情報を取得
     *
     * @param string $ticket
     * @param string $token
     * @return self
     */
    public function setToken(string $ticket, string $token): self
    {
        if (isset($ticket) && isset($token)) {
            $this->Ticket = $ticket;
            $this->CSRFToken = $token;
        } else {
            $this->setError('Ticket and Token Note Set');
        }
        return $this;
    }

    /**
     * トークン情報を返す
     *
     * @return array
     *  array(Ticket:'ticket string', CSRFToken:'token string')
     */
    public function getToken(): array
    {
        return array(
            'Ticket' => $this->Ticket,
            'CSRFToken' => $this->CSRFToken
        );
    }

    /**
     * LXCコンテナのリストを返す
     *
     * @return array
     */
    public function getLXCList(): array
    {
        return $this->LXCStack;
    }

    /**
     * PrxomoxAPIからLXCコンテナリストを取得
     *
     * @return self
     */
    public function getAllLXCFromAPI(): self
    {
        $path = $this->baseURL . sprintf(
            $this->endpoints['ListLXC'], $this->Node
        );
        try {
            $this->ini()
            ->request( $this->buildRequestToken(
                $path,      // 接続URL
                array(),    // 送信データ
                'GET'       // 使用するメソッド
            ))
            ->parseLXCList();
        } catch (\Throwable $th) {
            $this->setERROR('lisetLXC Seaquens ERROR');
            return $this;
        }
        return $this;
    }

    /**
     * ProxmoxAPIにLXCコンテナ追加要求を投げる
     *
     * @return self
     */
    public function addLXCToAPI(): self
    {
        $path = $this->baseURL . sprintf(
                        $this->endpoints['AddLXC'], $this->Node
                    );
        try {
            $this->ini()
            ->request($this->buildRequestToken(
                $path,
                array(
                    'net0'          => 'name=eth0,bridge=vmbr0',
                    'ostemplate'    => 'local:vztmpl/alpine-3.9-default_20190224_amd64.tar.xz',
                    'vmid'          => $this->getNewVmid(),
                    'storage'       => 'datastore1'
                ),
                'POST'
            ));


        } catch (\Throwable $th) {
            $this->setERROR('lisetLXC Seaquens ERROR');
            return $this;
        }
        return $this;
    }

    /**
     * ProxmoxAPIにLXCコンテナ削除要求を投げる
     *
     * @return self
     */
    public function delLXCToAPI(int $vmid = 0): self
    {
        $path = $this->baseURL . sprintf(
                        $this->endpoints['DelLXC'],
                        $this->Node,
                        701
                    );
        try {
            $this->ini()
            ->request($this->buildRequestToken(
                $path,
                array(),
                'DELETE'
            ));


        } catch (\Throwable $th) {
            $this->setERROR('lisetLXC Seaquens ERROR');
            return $this;
        }
        return $this;
    }

    /**
     * 有効なトークン、チケットを保持しているか確認
     * 保持していない場合は新たに取得
     *
     * @return self
     */
    public function checkToken(): self
    {
        if (!isset($this->CSRFToken) && !isset($this->Ticket)) {
            $this->getNewToken();
        }
        return $this;
    }

    /**
     * 新しいチケット、トークンを取得
     *
     * トークンの乱立になるので
     * このクラスはプライベートのまま推奨
     * @return self
     */
    private function getNewToken(): self
    {
        // 接続先URL作成
        $path = $this->baseURL . sprintf(
            $this->endpoints['GetToken'], $this->Node
        );
        try {
            $this->ini()
            ->request($this->buildRequestAuth(
                $path,
                'moriya@pam',
                'mori32makase',
                'POST'
            ))
            ->parseTicketAndToken();
        } catch (\Throwable $th) {
            dd($th);
            $this->setERROR('lisetLXC Seaquens ERROR');
            return $this;
        }
        return $this;
    }


    /**
     * サーバーレスポンスをDDで出力
     * 処理が止まるので注意
     *
     * @return void
     */
    public function showResponseDump(): void
    {
        dd($this->Response);
    }

    /**
     * サーダーレスポンスボディをDDで出力
     * 処理が止まるので注意
     *
     * @return void
     */
    public function showResponseBodyDump(): void
    {
        dd($this->ResponseBody);
    }

    /**
     * LXCコンテナ用VMIDを生成
     * 
     * @return int
     */
    private function getNewVmid(): int
    {
        $vmid = 700;

        for ($i=0; $i < 150; $i++) { 
            if (!in_array($vmid + $i, $this->Vmid, TRUE)) {
                $this->Vmid[] = $vmid;
                return $vmid;
            }
        }
    }


    /**
     * エラーを登録
     *
     * @param string $message
     * @return void
     */
    private function setERROR(string $message): void {
        $this->ERROR[] = array('name' => $message);
    }

}

