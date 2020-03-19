<?php

namespace App\_lib\GitLib\Helper;

trait GitGetSetHelper
{
    // git 情報
    private $GitID      = 0; // アカウントのindex：
    private $ReposID    = 0; // アカウントに紐付けされたリポジトリIndex
    private $BranchID   = 0; // リポジトリに紐付けされたIndex
    private $TypeID     = 0; // GitTypeのIndex
    private $User       = '';
    private $Pass       = '';
    private $Token      = '';
    private $Repository = '';
    private $Branch     = '';
    private $Commit     = '';


    /**
     * ユーザー名登録
     *
     * @param string $user
     * @return self
     */
    public function setUser(Object $user = null, string $pass = null): self
    {
        if ($user) {
            $this->User = $user;
        }
        if ($pass) {
            $this->Pass = $pass;
        }
        return $this;
    }

    /**
     * Gitログイン情報を設定
     *
     * @param Object $gitserver
     * @param integer $id
     * @param integer $type
     * @return self
     */
    public function setGit(Object $gitserver = null, int $type): self
    {
        if ($gitserver) {
            // Gitサーバーへの接続情報取得
            $this->GitID = $gitserver->id;
            $this->User  = $gitserver->user;
            $this->Pass  = $gitserver->passwd;

            //　GitType情報の保存
            $this->TypeID = $type;

            // トークンが設定されている場合保存
            if ($gitserver->token) {
                $this->setToken($gitserver->token);
            }
        }
        return $this;
    }

    /**
     * トークン情報をセット
     *
     * @param string $token
     * @return self
     */
    public function setToken(string $token = ''): self
    {
        if (!empty($token)) {
            $this->Token = $token;
        }
        return $this;
    }
    /**
     * リポジトリ登録
     *
     * @param Object $repos
     * @return self
     */
    public function setRepository(Object $repos = null): self
    {
        if ($repos) {
            $this->Repository = $repos;
        }
        return $this;
    }

    /**
     * ブランチ登録
     *
     * @param Object $branch
     * @return this
     */
    public function setBranch(Object $branch = null): self
    {
        if ($branch) {
            $this->Branch = $branch;
        }
        return $this;
    }

    /**
     * コミット登録
     *
     * @param string $comit
     * @return this
     */
    public function setCommit(Object $comit = null): self
    {
        if ($commit) {
            $this->Commit = $commit;
        }
        return $this;
    }


    /**
     * 取得済みのリポジトリ一覧を取得
     *
     * @param String $param
     * @return Array
     */
    public function getRepositorys(String $param = 'all') : Array
    {
        if ($param === 'all') {
            return $this->RepositoryStack;
        }
        /**
         * リポジトリを選んで返す処理追加予定
         *
         */
        return array();
    }

    /**
     * 取得済みのブランチ一覧を取得
     *
     * @param String $repos 対象のリポジトリ名
     * @return Array
     */
    public function getBranches(String $repos = ''): Array
    {
        if (!empty($repos) && $repos != 'all') {
            return $this->BranchStack[$repos];
        } else {
            return $this->BranchStack;
        }
        return array();
    }

    /**
     * 取得済みのコミット一覧を取得
     *
     * @param String $repos　対象のリポジトリ
     * @param String $branch 対象のブランチ（デフォルト「master」）
     * @return Array
     */
    public function getCommits(String $repos = '', String $branch = 'master'): Array
    {
        if (!empty($repos)) {
            return $this->CommitStack[$repos][$brnach];
        }
        return $this->CommitStack;
    }
}

