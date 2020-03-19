<?php

namespace App\_lib\GitLib;

use App\_lib\GitLib\Helper\GitHelper;
use App\_lib\GitLib\Helper\GitGetSetHelper;
use App\_lib\GitLib\Helper\GitParseHelper;
use App\_lib\GitLib\Helper\GitRequestHelper;

class BitBacketRepository
{
    use GitHelper;
    use GitGetSetHelper;
    use GitParseHelper;
    use GitRequestHelper;

    private $Builder       = 'Auth';
    private $Target        = 'BitBacket';

    private $baseURL       = 'https://api.bitbucket.org/2.0/';


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

}

