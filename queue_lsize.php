   /**
     * 队列长度
     * 
     * @return void
     * @author seatle <seatle@foxmail.com> 
     * @created time :2016-09-23 17:13
     */
    public function queue_lsize()
    {
        if (self::$tasknum > 1 || self::$save_running_state)
        {
            $lsize = cls_redis::lsize("collect_queue"); 
        }
        else 
        {
            $lsize = count(self::$collect_queue);
        }
        return $lsize;
    }
