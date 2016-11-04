<?php
    /**
     * 创建一个子进程
     * @param Worker $worker
     * @throws Exception
     */
    public function fork_one_task($taskid)
    {
        $pid = pcntl_fork();
        // 主进程记录子进程pid
        if($pid > 0)
        {
            // 暂时没用
            //self::$taskpids[$taskid] = $pid;
        }
        // 子进程运行
        elseif(0 === $pid)
        {
            log::warn("Fork children task({$taskid}) successful...");
            self::$time_start = microtime(true);
            self::$taskid = $taskid;
            self::$taskpid = posix_getpid();
            self::$collect_succ = 0;
            self::$collect_fail = 0;
            while( $this->queue_lsize() )
            { 
                // 如果队列中的网页比任务数2倍多，子任务可以采集，否则等待...
                if ($this->queue_lsize() > self::$tasknum*2) 
                {
                    // 抓取页面
                    $this->collect_page();
                }
                else 
                {
                    log::warn("Task(".self::$taskid.") waiting...");
                    sleep(1);
                }
                $this->set_task_status();
            } 
            // 这里用0表示正常退出
            exit(0);
        }
        else
        {
            log::error("Fork children task({$taskid}) fail...");
            exit;
        }
    }
