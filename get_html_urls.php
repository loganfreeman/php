    /**
     * 分析提取HTML页面中的URL
     * 
     * @param mixed $html           HTML内容
     * @param mixed $collect_url    抓取的URL，用来拼凑完整页面的URL
     * @return void
     * @author seatle <seatle@foxmail.com> 
     * @created time :2016-09-18 10:17
     */
    public function get_html_urls($html, $collect_url, $depth = 0) 
    { 
        //--------------------------------------------------------------------------------
        // 正则匹配出页面中的URL
        //--------------------------------------------------------------------------------
        // 同样进行1000次提取，xpath要6秒，正则一秒不到，所以没有什么特殊还是用正则吧
        //$urls = selector::select($html, '//a/@href');
        preg_match_all("/<a.*href=[\"']{0,1}(.*)[\"']{0,1}[> \r\n\t]{1,}/isU", $html, $matchs); 
        $urls = array();
        if (!empty($matchs[1])) 
        {
            foreach ($matchs[1] as $url) 
            {
                $urls[] = str_replace(array("\"", "'"), "", $url);
            }
        }
        if (empty($urls)) 
        {
            return false;
        }
        //--------------------------------------------------------------------------------
        // 过滤和拼凑URL
        //--------------------------------------------------------------------------------
        // 去除重复的RUL
        $urls = array_unique($urls);
        foreach ($urls as $k=>$url) 
        {
            $url = trim($url);
            if (empty($url)) 
            {
                continue;
            }
            $val = $this->fill_url($url, $collect_url);
            if ($val) 
            {
                $urls[$k] = $val;
            }
            else 
            {
                unset($urls[$k]);
            }
        }
        if (empty($urls)) 
        {
            return false;
        }
        //--------------------------------------------------------------------------------
        // 把抓取到的URL放入队列
        //--------------------------------------------------------------------------------
        foreach ($urls as $url) 
        {
            // 把当前页当做找到的url的Referer页
            $options = array(
                'headers' => array(
                    'Referer' => $collect_url,
                )
            );
            $this->add_url($url, $options, $depth);
        }
    }
    /**
     * 获得完整的连接地址
     * 
     * @param mixed $url            要检查的URL
     * @param mixed $collect_url    从那个URL页面得到上面的URL
     * @return void
     * @author seatle <seatle@foxmail.com> 
     * @created time :2016-09-23 17:13
     */
    public function fill_url($url, $collect_url)
    {
        $url = trim($url);
        $collect_url = trim($collect_url);
        // 排除JavaScript的连接
        //if (strpos($url, "javascript:") !== false) 
        if( preg_match("@^(javascript:|#|'|\")@i", $url) || $url == '')
        {
            return false;
        }
        $parse_url = @parse_url($collect_url);
        if (empty($parse_url['scheme']) || empty($parse_url['host'])) 
        {
            return false;
        }
        $scheme = $parse_url['scheme'];
        $domain = $parse_url['host'];
        $path = empty($parse_url['path']) ? '' : $parse_url['path'];
        $base_url_path = $domain.$path;
        $base_url_path = preg_replace("/\/([^\/]*)\.(.*)$/","/",$base_url_path);
        $base_url_path = preg_replace("/\/$/",'',$base_url_path);
        $i = $path_step = 0;
        $dstr = $pstr = '';
        $pos = strpos($url,'#');
        if($pos > 0)
        {
            // 去掉#和后面的字符串
            $url = substr($url, 0, $pos);
        }
        // 京东变态的都是 //www.jd.com/111.html
        if(substr($url, 0, 2) == '//')
        {
            $url = str_replace("//", "", $url);
        }
        // /1234.html
        elseif($url[0] == '/')
        {
            $url = $domain.$url;
        }
        // ./1234.html、../1234.html 这种类型的
        elseif($url[0] == '.')
        {
            if(!isset($url[2]))
            {
                return false;
            }
            else
            {
                $urls = explode('/',$url);
                foreach($urls as $u)
                {
                    if( $u == '..' )
                    {
                        $path_step++;
                    }
                    // 遇到 .，不知道为什么不直接写$u == '.'，貌似一样的
                    else if( $i < count($urls)-1 )
                    {
                        //$dstr .= $urls[$i].'/';
                    }
                    else
                    {
                        $dstr .= $urls[$i];
                    }
                    $i++;
                }
                $urls = explode('/',$base_url_path);
                if(count($urls) <= $path_step)
                {
                    return false;
                }
                else
                {
                    $pstr = '';
                    for($i=0;$i<count($urls)-$path_step;$i++){ $pstr .= $urls[$i].'/'; }
                    $url = $pstr.$dstr;
                }
            }
        }
        else 
        {
            if( strtolower(substr($url, 0, 7))=='http://' )
            {
                $url = preg_replace('#^http://#i','',$url);
                $scheme = "http";
            }
            else if( strtolower(substr($url, 0, 8))=='https://' )
            {
                $url = preg_replace('#^https://#i','',$url);
                $scheme = "https";
            }
            else
            {
                $url = $base_url_path.'/'.$url;
            }
        }
        // 两个 / 或以上的替换成一个 /
		$url = preg_replace('@/{1,}@i', '/', $url);
        $url = $scheme.'://'.$url;
        $parse_url = @parse_url($url);
        $domain = empty($parse_url['host']) ? $domain : $parse_url['host'];
        // 如果host不为空，判断是不是要爬取的域名
        if (!empty($parse_url['host'])) 
        {
            //排除非域名下的url以提高爬取速度
            if (!in_array($parse_url['host'], self::$configs['domains'])) 
            {
                return false;
            }
        }
        return $url;
    }
