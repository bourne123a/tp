<?php
/**
 * Created by PhpStorm.
 * User: baidu
 * Date: 18/3/27
 * Time: 上午12:50
 */
class Http {
    CONST HOST = "0.0.0.0";
    CONST PORT = 8811;

    public $http = null;
    public function __construct() {
        $this->http = new swoole_http_server(self::HOST, self::PORT);

        $this->http->set(
            [
//                'enable_static_handler' => true,
//                'document_root' => "",
                'worker_num' => 4,
                'task_worker_num' => 4,
            ]
        );
        $this->http->on("start", [$this, 'onStart']);
        $this->http->on("workerstart", [$this, 'onWorkerStart']);
        $this->http->on("request", [$this, 'onRequest']);
        $this->http->on("task", [$this, 'onTask']);
        $this->http->on("finish", [$this, 'onFinish']);
        $this->http->on("close", [$this, 'onClose']);

        $this->http->start();
    }
    /**定义主进程名
     * @param $server
     */
    public function onStart($server) {
        cli_set_process_title("tp_master");
    }

    /**
     * @param $server
     * @param $worker_id
     */
    public function onWorkerStart($server,  $worker_id) {
        // 定义应用目录
        define('APP_PATH', __DIR__ . '/../../application/');
        // 加载框架里面的文件
        require __DIR__ . '/../../thinkphp/base.php';
    }

    /**
     * request回调
     * @param $request
     * @param $response
     */
    public function onRequest($request, $response) {
        $_SERVER  =  [];
        if(isset($request->server)) {
            foreach($request->server as $k => $v) {
                $_SERVER[strtoupper($k)] = $v;
            }
        }
        if(isset($request->header)) {
            foreach($request->header as $k => $v) {
                $_SERVER[strtoupper($k)] = $v;
            }
        }

        $_GET = [];
        if(isset($request->get)) {
            foreach($request->get as $k => $v) {
                $_GET[$k] = $v;
            }
        }
        $_POST = [];
        if(isset($request->post)) {
            foreach($request->post as $k => $v) {
                $_POST[$k] = $v;
            }
        }

        $_POST['http_server'] = $this->http;

        ob_start();
        // 执行应用并响应
        try {
            \think\App::run()->send();
        }catch (\Exception $e) {
            // todo
        }

        $res = ob_get_contents();
        ob_end_clean();
        $response->end($res);
    }

    /**
     * @param $serv
     * @param $taskId
     * @param $workerId
     * @param $data
     */
    public function onTask($serv, $taskId, $workerId, $data) {
    }

    /**
     * @param $serv
     * @param $taskId
     * @param $data
     */
    public function onFinish($serv, $taskId, $data) {
    }

    /**
     * close
     * @param $ws
     * @param $fd
     */
    public function onClose($ws, $fd) {
        echo "clientid:{$fd}\n";
    }
}

new Http();