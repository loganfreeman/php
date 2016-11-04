<?php   
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
        
        
    /**
     * 从队列右边取出
     * 
     * @return void
     * @author seatle <seatle@foxmail.com> 
     * @created time :2016-09-23 17:13
     */
    public function queue_rpop()
    {
        if (self::$tasknum > 1 || self::$save_running_state)
        {
            $link = cls_redis::rpop("collect_queue"); 
            $link = json_decode($link, true);
        }
        else 
        {
            $link = array_shift(self::$collect_queue); 
        }
        return $link;
    }
        
        
    /**
     * 从队列左边取出
     * 后进先出
     * 可以避免采集内容页有分页的时候采集失败数据拼凑不全
     * 还可以按顺序采集列表页
     * 
     * @return void
     * @author seatle <seatle@foxmail.com> 
     * @created time :2016-09-23 17:13
     */
    public function queue_lpop()
    {
        if (self::$tasknum > 1 || self::$save_running_state)
        {
            $link = cls_redis::lpop("collect_queue"); 
            $link = json_decode($link, true);
        }
        else 
        {
            $link = array_pop(self::$collect_queue); 
        }
        return $link;
    }
        
    /**
     * 从队列左边插入
     * 
     * @return void
     * @author seatle <seatle@foxmail.com> 
     * @created time :2016-09-23 17:13
     */
    public function queue_lpush($link = array(), $allowed_repeat = false)
    {
        if (empty($link) || empty($link['url'])) 
        {
            return false;
        }
        $url = $link['url'];
        $status = false;
        if (self::$tasknum > 1 || self::$save_running_state)
        {
            $key = "collect_urls-".md5($url);
            $lock = "lock-".$key;
            // 加锁：一个进程一个进程轮流处理
            if (cls_redis::lock($lock))
            {
                $exists = cls_redis::exists($key); 
                //$exists = cls_redis::get($key); 
                // 不存在或者当然URL可重复入
                if (!$exists || $allowed_repeat) 
                {
                    // 待爬取网页记录数加一
                    cls_redis::incr("collect_urls_num"); 
                    // 先标记为待爬取网页
                    cls_redis::set($key, time()); 
                    // 入队列
                    $link = json_encode($link);
                    cls_redis::lpush("collect_queue", $link); 
                    $status = true;
                }
                // 解锁
                cls_redis::unlock($lock);
            }
        }
        else 
        {
            $key = md5($url);
            if (!array_key_exists($key, self::$collect_urls))
            {
                self::$collect_urls_num++;
                self::$collect_urls[$key] = time();
                array_push(self::$collect_queue, $link);
                $status = true;
            }
        }
        return $status;
    }
    /**
     * 从队列右边插入
     * 
     * @return void
     * @author seatle <seatle@foxmail.com> 
     * @created time :2016-09-23 17:13
     */
    public function queue_rpush($link = array(), $allowed_repeat = false)
    {
        if (empty($link) || empty($link['url'])) 
        {
            return false;
        }
        $url = $link['url'];
        $status = false;
        if (self::$tasknum > 1 || self::$save_running_state)
        {
            $key = "collect_urls-".md5($url);
            $lock = "lock-".$key;
            // 加锁：一个进程一个进程轮流处理
            if (cls_redis::lock($lock))
            {
                $exists = cls_redis::exists($key); 
                // 不存在或者当然URL可重复入
                if (!$exists || $allowed_repeat) 
                {
                    // 待爬取网页记录数加一
                    cls_redis::incr("collect_urls_num"); 
                    // 先标记为待爬取网页
                    cls_redis::set($key, time()); 
                    // 入队列
                    $link = json_encode($link);
                    cls_redis::rpush("collect_queue", $link); 
                    $status = true;
                }
                // 解锁
                cls_redis::unlock($lock);
            }
        }
        else 
        {
            $key = md5($url);
            if (!array_key_exists($key, self::$collect_urls))
            {
                self::$collect_urls_num++;
                self::$collect_urls[$key] = time();
                array_unshift(self::$collect_queue, $link);
                $status = true;
            }
        }
        return $status;
    }
