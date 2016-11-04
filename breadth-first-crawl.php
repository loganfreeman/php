<?php    
    /**
     * Function that crawls each provided URI
     * It applies all processors and listeners set on the Spider
     *
     * This is a either depth first algorithm as explained here:
     *  https://en.wikipedia.org/wiki/Depth-first_search#Example
     * Note that because we don't do it recursive, but iteratively,
     * results will be in a different order from the example, because
     * we always take the right-most child first, whereas a recursive
     * variant would always take the left-most child first
     *
     * or
     *
     * a breadth first algorithm
     *
     * @return void
     */
    private function doCrawl()
    {
        while ($currentUri = $this->getQueueManager()->next()) {
            if ($this->getDownloader()->isDownLoadLimitExceeded()) {
                break;
            }
            if (!$resource = $this->getDownloader()->download($currentUri)) {
                continue;
            }
            $this->dispatch(
                SpiderEvents::SPIDER_CRAWL_RESOURCE_PERSISTED,
                new GenericEvent($this, array('uri' => $currentUri))
            );
            // Once the document is enqueued, apply the discoverers to look for more links to follow
            $discoveredUris = $this->getDiscovererSet()->discover($resource);
            foreach ($discoveredUris as $uri) {
                try {
                    $this->getQueueManager()->addUri($uri);
                } catch (QueueException $e) {
                    // when the queue size is exceeded, we stop discovering
                    break;
                }
            }
        }
    }
