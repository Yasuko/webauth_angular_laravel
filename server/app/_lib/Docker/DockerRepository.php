<?php

namespace App\_lib\Docker;

use App\_lib\Helper\CodeCheck;

class DockerRepository
{
    private $logfile = 'c:\xampp\htdocs\LineBot\log.txt';
    var $scheem = "";
    var $layout = array();

    private $docker_url = '192.168.2.183';
    private $docker_port = 2376;
    private $docker_com = '/usr/bin/docker';

    private $CONTAINERS = array();
    private $PORT = array();

    /**
     * 全てのコンテナを取得
     * 
     * @return array
     */
    public function getAllContainer(): array
    {
        $cmd = 'ps -a --format "{{.Names}}::{{.ID}}::{{.Ports}}::{{.Image}}::{{.Status}}"';
        return $this->separateResultFormat($this->execDocker($cmd));
    }
    /**
     * コミット名指定でコンテナ詳細取得
     * 
     * @param string $name
     * @return array
     */
    public function getContainerByCommit(string $name): array
    {
        $cmd = 'ps -inspect ' . $name;
        return $this->separateResult($this->execDocker($cmd));
    }
    /**
     * Dockerイメージを全て取得
     *
     * @return array
     */
    public function getAllImages(): array
    {
        $cmd = 'images';
        return $this->separateResult($this->execDocker($cmd));
    }

    /**
     * コンテナのインフォメーション情報を取得
     * 未整形情報を返却する
     *
     * @param string $cid
     * @return array
     */
    public function getInfoContainer(string $cid): array
    {
        $cmd = 'inspect '.$cid;
        return $this->execDocker($cmd);
    }

    /**
     * コンテナを新規作成
     *
     * @param array $param
     * @return string
     */
    public function setNewContainer(array $param): string
    {
        // コンテナ情報取得
        $this->getAllContainer();

        //既に該当コミットで作成していないか確認
        if ($this->checkDuplication($param['commit']) === false) {
            $param['port'] = $this->portSelector();
            $cmd = 'run -it -d'
            . ' -p ' . $param['port'] . ':80'
            . ' --name ' . $param['commit']
            . ' -e GITURL=' . $param['url']
            . ' -e GITREPOSITORY=' . $param['repos']
            . ' -e GITBRANCH=' . $param['branch']['id']
            . ' -e GITCOMMIT=' . $param['commit']
            . ' yasukosan/php56:1.1';
            if ($this->execDocker($cmd)) {
                $url = $this->buildAddress($param);
                if (CodeCheck::checkURL($url)) {
                    return $url;
                } else {
                    return 'Build Error';
                };
            }
        }
        return 'this commit already runnnig';
    }

    /**
     * コンテナを止める
     *
     * @param string $name
     * @return self
     */
    public function setStopContainer(string $name): self
    {
        $cmd = 'stop ' . $name;
        $result = $this->execDocker($cmd);
        return $this;
    }

    /**
     * コンテナを削除する
     *
     * @param string $name
     * @return bool
     */
    public function setDeleteContainer(string $name): bool
    {
        $this->getAllContainer();
        if (in_array($name, $this->CONTAINERS)) {
            $cmd = ' rm --force ' . $name;
            $result = $this->execDocker($cmd);
            if ($result == $name) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }

    }

    /**
     * 作成されたコンテナのURLを返す
     *
     * @param array $param
     * @return string
     */
    public function buildAddress(array $param): string
    {
        // return 'http://' . $this->docker_url . ':' . $param['port'] . '/' . $param['repos'];
        return 'http://' . $this->docker_url . ':' . $param['port'];
    }

    /**
     * コマンド実行
     * @param string $cmd
     * @return array|null
     * docketコマンドを実行し結果を受け取る
     * エラー発生時、コマンドが戻らない場合はfalseを返す
     */
    private function execDocker($cmd): ?array
    {
        $cmd = $this->docker_com 
                . ' -H tcp://' . $this->docker_url
                . ':' . $this->docker_port
                . ' ' . $cmd;
        // コマンド実行、コマンドの成否にかかわらず
        // 成功として処理されるので戻り値から別途判断が必要
        exec($cmd, $opt, $result);
        if (!$result) {
            // execの戻り値を返す
            return $opt;
        } else {
            return $result;
        }
    }

    /**
     * コンテナが既に登録済みで無いか確認
     * 登録済み：TRUE
     * 未登録：FALSE
     * 
     * @param string $name
     * @return bool
     */
    private function checkDuplication(string $name): bool
    {
        return in_array($name, $this->CONTAINERS, TRUE);
    }

    /**
     * コマンドの出力結果をパース
     * @$result array 出力結果の１行ごとの配列
     * @return array　出力結果のパースされた配列群
     * コマンド出力結果のformatオプションが無いコマンド用パース関数
     */
    private function separateResult($result)
    {

        foreach ($result as $key => $cmdline) {
            $loop = true;
            while($loop)
            {
                if (strpos($cmdline, '   ')) {
                    $cmdline = str_replace('   ', '  ', $cmdline);
                } else {
                    $result[$key] = null;
                    $result[$key] = explode('  ', $cmdline);
                    $loop = false;
                }
                $resutl[$key] = $cmdline;
            }
        }

        // 取得したコンテナ情報を保持
        $this->setContainerPropatie($result);

        return $result;
    }
    /**
     * フォーマット指定で取得した結果をパース
     * @param array $result
     * @return array
     * フォーマット指定可能なコマンドのパース処理
     *　必ず「：」で区切られていること
     */
    private function separateResultFormat($result)
    {
        foreach ($result as $key => $cmdline) {
            $_result = explode('::', $cmdline);
            $delimita = '[->/:]';
            if (empty($_result[2])) {
                $port = array('', 'none');
            } else {
                $port = mb_split($delimita, $_result[2]);
            }

            $result[$key] = array(
                'name'  => $_result[0],
                'id'    => $_result[1],
                'ports' => $port[1],
                'image' => $_result[3],
                'status'=> $_result[4],
            );
        }

        // 取得したコンテナ情報を保持
        $this->setContainerPropatie($result);

        return $result;
    }

    /**
     * コンテナ情報を一時保存
     * @param array $containers
     * 
     */
    private function setContainerPropatie($containers)
    {
        $this->CONTAINERS = array();
        $this->PORT = array();
        foreach ($containers as $key => $container) {
            // 使用中ポート番号取得
            $this->PORT[] = (int)$container['ports'];

            //　使用中のコンテナ名取得
            $this->CONTAINERS[] = $container['name'];
        }
    }

    /**
     * ポート番号の自動セレクタ
     * 32000から32500までのポート番号で未使用のものを返す
     * ポート範囲の根拠は適当
     */
    private function portSelector()
    {
        for ($i = 32000; $i <= 32500; ++$i ) {
            if (!in_array((int)$i, $this->PORT, TRUE)) {
                return $i;
            }
        }
    }
}