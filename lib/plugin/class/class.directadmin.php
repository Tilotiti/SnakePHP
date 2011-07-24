<?php
# vim: set expandtab tabstop=4 shiftwidth=4 fdm=marker:

# +---------------------------------------------------+
# | This source file is not copyrighted nor licensed. |
# +---------------------------------------------------+
# | Author:  Eric Yao <Eric@AncientDeveloper.com>     |
# +---------------------------------------------------+

# OOP:  http://php.net/manual/en/language.oop5.php
# Data Types:  http://php.net/manual/en/language.types.php

# {{{ public class DirectAdmin
class directadmin
{
    # {{{ Properties
    var $da = array(); /* private array[] $da; */
    # }}}

    # {{{ Constructor
    # {{{ public void DirectAdmin(string $url);
    function directadmin($url)
    {
        $this->da = parse_url($url);

        if (!$this->da['port']) {
            $this->da['port'] = ($this->da['scheme'] == 'https') ? 443 : 80;
        }
    }
    # }}}
    # }}}

    # {{{ Methods
    # {{{ public string retrieve(array[] $argument);
    function retrieve($argument)
    {
        if (is_array($argument) && count($argument)) {

            if (is_string($argument['command'])) {
                $command = $argument['command'];
            } else {
                return 'command not specified';
            }

            switch (strcasecmp($argument['method'] , 'POST')) {
                case 0:
                    $post = 1;
                    $method = 'POST';
                break;
                default:
                    $post = 0;
                    $method = 'GET';
            }

            if (is_array($argument['data']) && count($argument['data'])) {

                foreach ($argument['data'] as $index=>$value) {
                    $pair .= $index.'='.urlencode($value).'&';
                }

                $data = rtrim($pair , '&');
                $content_length = ($post) ? strlen($data) : 0;

            } else {
                $content_length = 0;
            }

            $prefix = ($this->da['scheme'] == 'https') ? 'ssl://' : NULL;

            if ($fp = @fsockopen($prefix.$this->da['host'] , $this->da['port'] , $error['number'] , $error['string'] , 10)) {

                $http_header = array(
                    $method.' /'.$command.((!$post) ? '?'.$data : NULL).' HTTP/1.0',
                    'Authorization: Basic '.base64_encode($this->da['user'].':'.$this->da['pass']),
                    'Host: '.$this->da['host'],
                    'Content-Type: application/x-www-form-urlencoded',
                    'Content-Length: '.$content_length,
                    'Connection: close'
                );

                $request = implode("\r\n" , $http_header)."\r\n\r\n";
                fwrite($fp , $request.(($post) ? $data : NULL));

                while ($line = @fread($fp , 1024)) {
                    $returned .= $line;
                }

                fclose($fp);

                $h = strpos($returned , "\r\n\r\n");
                $head['all'] = substr($returned , 0 , $h);
                $head['part'] = explode("\r\n" , $head['all']);

                foreach ($head['part'] as $response) {
                    if (preg_match('/^Location:\s+/i' , $response)) {
                        header($response);
                        exit;
                    }
                }

                $body = substr($returned , $h + 4); # \r\n\r\n = 4

            }

        }

        return rtrim((string) $body);
    }
    # }}}
    # }}}
}
# }}}
?>