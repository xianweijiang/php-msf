<?php
/**
 * CTask
 *
 * @author camera360_server@camera360.com
 * @copyright Chengdu pinguo Technology Co.,Ltd.
 */

namespace PG\MSF\Coroutine;

class CTask extends Base
{
    /**
     * 任务ID
     *
     * @var int
     */
    public $id;

    /**
     * 任务执行参数
     *
     * @var array
     */
    public $taskProxyData;

    /**
     * 初始化Task协程对象
     *
     * @param array $taskProxyData
     * @param int $id
     * @param int $timeout
     * @return $this
     */
    public function initialization($taskProxyData, $id, $timeout)
    {
        parent::init($timeout);
        $this->taskProxyData = $taskProxyData;
        $this->id            = $id;
        $profileName         = $taskProxyData['message']['task_name'] . '::' . $taskProxyData['message']['task_fuc_name'];
        $logId               = $this->getContext()->getLogId();

        $this->getContext()->getLog()->profileStart($profileName);
        getInstance()->coroutine->IOCallBack[$logId][] = $this;
        $this->send(function ($serv, $taskId, $data) use ($profileName, $logId) {
            if (empty(getInstance()->coroutine->taskMap[$logId])) {
                return;
            }

            $this->getContext()->getLog()->profileEnd($profileName);
            $this->result = $data;
            $this->ioBack = true;
            $this->nextRun($logId);
        });
        
        return $this;
    }

    /**
     * 投递异步任务给Tasker进程
     * @param callable $callback
     */
    public function send($callback)
    {
        getInstance()->server->task($this->taskProxyData, $this->id, $callback);
    }

    /**
     * 销毁
     */
    public function destroy()
    {
        parent::destroy();
    }
}
