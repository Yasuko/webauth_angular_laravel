<?php

namespace App\_lib\Proxmox\Helper;
/**
 * 
 * サーバーからの、リポジトリ、ブランチ、コミットの情報を
 * 配列にパースする処理を担当するライブラリ
 * 
 */
trait ProxmoxParseHelper
{
    // 取得情報の保存先
    private $LXCStack           = array();
    private $BranchStack        = array();
    private $CommitStack        = array();
    private $Vmid               = array();


    /**
     * ProxMoxサーバーから取得した
     * チケットとトークン情報を保存
     *
     * @return self
     */
    private function parseTicketAndToken(): self
    {
        if (array_key_exists('data', $this->ResponseBody)) {
            $this->Ticket = $this->ResponseBody['data']['ticket'];
            $this->CSRFToken = $this->ResponseBody['data']['CSRFPreventionToken'];
        } else {
            $this->setERROR('Server Response ERROR');
        }
        return $this;
    }

    /**
     * ProxMoxサーバーから取得した
     * LXCコンテナのリストを保存
     * VMID一覧を保存
     *
     * @return self
     */
    public function parseLXCList(): self
    {
        if (array_key_exists('data', $this->ResponseBody)) {
            foreach ($this->ResponseBody['data'] as $key => $list) {

                $this->LXCStack[] = $list;
                $this->Vmid[] = $list['vmid'];
            }
        } else {
            $this->setERROR('Server Response ERROR');
        }
        return $this;
    }

    /**
     * 取得したリポジトリ一覧オブジェクトから
     * 必要な情報を抜き出す
     * ※Bitbacket専用
     *
     * @return self
     */
    private function parseRepositoryForBitBacket() : self
    {
        if (array_key_exists('values', $this->ResponseBody)) {
            foreach ($this->ResponseBody['values'] as $repo) {
                $branch = ($repo['mainbranch']['name'] === null) ? 'master' : $repo['mainbranch']['name'];
                $_rep[$repo['name']] = array(
                    'gitserver_id'  => $this->GitID,
                    'branch_url'    => '',
                    'name'          => $repo['name'],
                    'mainbranch'    => $branch,
                );
                $this->RepositoryStack += $_rep;
            }
        }
        return $this;
    }

    /**
     * 取得したブランチ一覧オブジェクトから
     * 必要な情報を抜き出す
     * ※BitBacket専用
     * 
     * @return self
     */
    private function parseBranchesForBitBacket(): self
    {
        if (array_key_exists('values', $this->ResponseBody)) {
            foreach ($this->ResponseBody['values'] as $branch) {
                $_bra[$branch['name']] = array(
                    'gitserver_id'  => $this->GitID,
                    'gittype_id'    => $this->TypeID,
                    'repos_id'      => $this->Repository->id,
                    'commit_url'    => '',
                    'name'          => $branch['name'],
                );
                $this->BranchStack += $_bra;
            }
        }
        return $this;
    }

    /**
     * 取得したコミット一覧オブジェクトから
     * 必要な情報を抜き出す
     * ※BitBacket専用
     *
     * @return self
     */
    private function parseCommitsForBitBacket(): self
    {

        if (array_key_exists('values', $this->ResponseBody)) {
            foreach ($this->ResponseBody['values'] as $commit) {
                $str = str_replace(array("\r\n", "\r", "\n"), "\n", $commit['message']);
                $name = explode("\n", $str);
                $_com[] = array(
                    'gitserver_id'  => $this->GitID,
                    'gittype_id'    => $this->TypeID,
                    'repos_id'      => $this->Repository->id,
                    'branch_id'     => $this->Branch->id,
                    'name'          => $name[0],
                    'message'       => $commit['message'],
                    'hash'          => $commit['hash'],
                    'date'          => $commit['date'],
                );
                $this->CommitStack += $_com;
            }
        }
        return $this;
    }



    /**
     * 取得したリポジトリ一覧オブジェクトから
     * 必要な情報を抜き出す
     * ※GitHub専用
     *
     * @return self
     */
    private function parseRepositoryForGitHub() : self
    {
        foreach ($this->ResponseBody as $repo) {
            $_rep[$repo['name']] = array(
                'gitserver_id'  => $this->GitID,
                'branch_url'    => '',
                'name'          => $repo['name'],
                'mainbranch'    => $repo['default_branch'],
            );
            $this->RepositoryStack += $_rep;
        }
        return $this;
    }

    /**
     * 取得したブランチ一覧オブジェクトから
     * 必要な情報を抜き出す
     *
     * @return self
     */
    private function parseBranchesForGitHub(): self
    {
        foreach ($this->ResponseBody as $branch) {
            $_bra[$branch['name']] = array(
                'gitserver_id'  => $this->GitID,
                'gittype_id'    => $this->TypeID,
                'repos_id'      => $this->Repository->id,
                'commit_url'    => '',
                'name'          => $branch['name'],
            );
            $this->BranchStack += $_bra;
        }
        return $this;
        
    }

    /**
     * 取得したコミット一覧オブジェクトから
     * 必要な情報を抜き出す
     *
     * @return self
     */
    private function parseCommitsForGitHub(): self
    {

        foreach ($this->ResponseBody as $commit) {
            $str = str_replace(array("\r\n", "\r", "\n"), "\n", $commit['commit']['message']);
            $name = explode("\n", $str);
            $_com[] = array(
                'gitserver_id'  => $this->GitID,
                'gittype_id'    => $this->TypeID,
                'repos_id'  => $this->Repository->id,
                'branch_id' => $this->Branch->id,
                'name'      => $name[0],
                'message'   => $commit['commit']['message'],
                'hash'      => $commit['sha'],
                'date'      => $commit['commit']['author']['date'],
            );
            $this->CommitStack += $_com;
        }
        return $this;
    }
}

