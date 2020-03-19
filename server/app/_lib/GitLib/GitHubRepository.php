<?php

namespace App\_lib\GitLib;

use App\_lib\GitLib\Helper\GitHelper;
use App\_lib\GitLib\Helper\GitGetSetHelper;
use App\_lib\GitLib\Helper\GitParseHelper;
use App\_lib\GitLib\Helper\GitRequestHelper;

class GitHubRepository
{
    use GitHelper;
    use GitGetSetHelper;
    use GitParseHelper;
    use GitRequestHelper;

    private $Builder       = 'Token';
    private $Target        = 'GitHub';

    private $baseURL       = 'https://api.github.com';


    /**
     * GitHubからリポジトリ一覧を取得
     *
     * @return bool
     */
    public function getAllUserRepositorys(): bool
    {
        // 呼び出しクエリビルダ
        $build  = 'buildRequest' . $this->Builder;
        // 呼び出しパーサー
        $parser = 'parseRepositoryFor'.$this->Target;

        $this->ini()
            ->request($this->$build('/users/yasuko/repos'))
            ->$parser()
            ->pageNation($parser);
        return true;
    }

    /**
     * GitHubの指定リポジトリからブランチ一覧を取得
     *
     * @return bool
     */
    public function getAllUserBranches(): bool
    {
        $build  = 'buildRequest' . $this->Builder;
        $parser = 'parseBranchesFor'.$this->Target;

        $this->ini()
            ->request(
                $this->$build(
                    'repos/' . $this->User . '/'
                    . $this->Repository->name . '/branches'
                )
            )
            ->$parser()
            ->pageNation($parser);
        return true;
    }

    /**
     * GitHubの指定リポジトリからコミット一覧を取得
     * ブランチ指定がある場合はブランチも指定
     *
     * @return bool
     */
    public function getAllUserCommits(): bool
    {
        $build  = 'buildRequest' . $this->Builder;
        $parser = 'parseCommitFor'.$this->Target;

        $this->ini()
            ->request(
                $this->$build(
                    'repos/' . $this->User . '/'
                    . $this->Repository->name . '/commits' 
                    . '?sha=' . $this->Branch->name
                )
            )
            ->$parser()
            ->pageNation($parser);
        return true;
    }


}

