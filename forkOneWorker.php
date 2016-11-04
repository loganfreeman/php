<?php    
    /**
     * Fork one worker process.
     *
     * @param Worker $worker
     * @throws Exception
     */
    protected static function forkOneWorker($worker)
    {
        $pid = pcntl_fork();
        // Get available worker id.
        $id = self::getId($worker->workerId, 0);
        // For master process.
        if ($pid > 0) {
            self::$_pidMap[$worker->workerId][$pid] = $pid;
            self::$_idMap[$worker->workerId][$id]   = $pid;
        } // For child processes.
        elseif (0 === $pid) {
            if ($worker->reusePort) {
                $worker->listen();
            }
            if (self::$_status === self::STATUS_STARTING) {
                self::resetStd();
            }
            self::$_pidMap  = array();
            self::$_workers = array($worker->workerId => $worker);
            Timer::delAll();
            self::setProcessTitle('WorkerMan: worker process  ' . $worker->name . ' ' . $worker->getSocketName());
            $worker->setUserAndGroup();
            $worker->id = $id;
            $worker->run();
            exit(250);
        } else {
            throw new Exception("forkOneWorker fail");
        }
    }
