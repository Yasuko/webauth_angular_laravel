<?php

namespace App\_lib\Ansible;

use App\_lib\Ansible\Helper\AnsibleHelper;
use App\_lib\Ansible\Helper\AnsibleParseHelper;
use App\_lib\Ansible\Helper\AnsibleRequestHelper;

class AnsibleRepository
{
    use AnsibleHelper;
    use AnsibleParseHelper;
    use AnsibleRequestHelper;

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

    public function getLXCList(): array
    {
        return $this->LXCStack;
    }

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

    public function delLXCToAPI(): Bool
    {

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

    private function buildQuery(): self
    {
        
    }

    private function buildHeader(): self
    {
        $this->Header = array(
            'Content-Type'          => 'application/x-www-form-urlencoded',
            'CSRFPreventionToken'   => $this->Token
        );
    }

    private function buildCookie(): self
    {
        $this->Cookie = array(
            'PVEAuthCookies'    => $this->Cookie
        );
    }

    private function parseLXCLixt(): self
    {

    }

    /**
     * BitBacketからリポジトリ一覧を取得
     *
     * @param String $repos
     * @return Bool
     */
    public function getAllUserRepositorys(): Bool
    {
        // 呼び出しクエリビルダ
        $build  = 'buildRequest' . $this->Builder;
        // 呼び出しパーサー
        $parser = 'parseRepositoryFor'.$this->Target;

        $this->ini()    // 初期化
            ->request(  // リクエスト作成
                $this->$build('repositories/' . $this->User)
            )
            ->$parser() // 取得データの分解
            ->pageNation($parser);  // 次ページ判定
        return true;
    }

    /**
     * BitBacketの指定リポジトリからブランチ一覧を取得
     *
     * @return Bool
     */
    public function getAllUserBranches(): Bool
    {
        $build  = 'buildRequest' . $this->Builder;
        $parser = 'parseBranchesFor'.$this->Target;

        $this->ini()
            ->request(  // リクエストパス、オプション設定
                $this->$build(
                    'repositories/' . $this->User . '/' 
                    . $this->Repository->name . '/refs/branches'
                )
            )
            ->$parser()
            ->pageNation($parser);
        return true;
    }

    /**
     * BitBacketの指定リポジトリからコミット一覧を取得
     * ブランチ指定がある場合はブランチも指定
     *
     * @return Bool
     */
    public function getAllUserCommits(): Bool
    {
        $build  = 'buildRequest' . $this->Builder;
        $parser = 'parseCommitsFor'.$this->Target;

        $this->ini()
            ->request(
                $this->$build(
                    'repositories/' . $this->User . '/'
                    . $this->Repository->name . '/commits' 
                    . '/' . $this->Branch->name
                )
            )
            ->$parser()
            ->pageNation($parser);
        return true;
    }


    public function showResponseDump(): void
    {
        dd($this->Response);
    }

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

