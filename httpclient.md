[stream_context_create](http://php.net/manual/en/function.stream-context-create.php)
---
```php
try {
            $context = stream_context_create($contextOptions);
            $stream = fopen($url, 'rb', false, $context);
            $responseContent = stream_get_contents($stream);
            // see http://php.net/manual/en/reserved.variables.httpresponseheader.php
            $responseHeaders = $http_response_header;
            fclose($stream);
        } catch (\Exception $e) {
            Yii::endProfile($token, __METHOD__);
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
```

[Curl](http://php.net/manual/en/book.curl.php)
---
CurlTransport sends HTTP messages using [Client URL Library (cURL)](http://php.net/manual/en/book.curl.php)
