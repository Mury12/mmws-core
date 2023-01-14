<?php

namespace MMWS\Middleware;

use MMWS\Abstracts\Model;
use MMWS\Factory\RequestExceptionFactory;
use MMWS\Handler\Request;
use MMWS\Handler\SESSION;
use MMWS\Interfaces\Middleware;

/**
 * Implements request caching with timeout
 * @param String $name the request name
 * @param Int $timeout time in seconds to REDO the request. Default is 10 seconds 
 * @param Int $interval interval between requests. Default is 1 second
 */
class Cache implements Middleware
{


    /**
     * @var Int $timeout timeout to clean cache
     */
    public static $timeout = 30;

    /**
     * @var Int $interval interval between requests
     */
    public static $interval = 1;

    /**
     * @var Array $cache is the cached content
     */
    public static $cache;

    private $session = [];

    function init(Request $request)
    {
        return $this->action();
    }

    function action()
    {
    }


    /**
     * @param String $name the request name
     */
    static function check($name)
    {
        $pathname = $_SERVER['REQUEST_URI'];
        $cachedName = $name . $pathname . '_cache';
        if (SESSION::get($cachedName)) {
            $cached = json_decode(SESSION::get($cachedName), true);
            $cacheTime = new \DateTime($cached['time']['date']);
            $now = new \DateTime();

            $diff = date_diff($now, $cacheTime)->format('%s');
            $ttl = self::$timeout - +$diff;

            if ($diff < self::$timeout) {
                header("X-Result-Cached-At: " . $cacheTime->format('D, d M Y H:i:s') . " GMT");
                header("X-Result-Will-Renew: " .  gmdate('D, d M Y H:i:s', strtotime("+ $ttl seconds")) . " GMT");
                return array_merge($cached['result']);
            }
        }
        return false;
    }

    /**
     * @param String $name the request name
     */
    static function put($result, $name)
    {
        $pathname = $_SERVER['REQUEST_URI'];
        $cachedName = $name . $pathname . '_cache';
        $now = new \DateTime();
        $request = array(
            'time' => $now,
            'result' => self::getArrayOf($result),
        );
        SESSION::add($cachedName, json_encode($request));
    }

    static function getArrayOf($result)
    {
        if (!($result instanceof Model || $result[0] instanceof Model))
            throw RequestExceptionFactory::create("Cannot cache request", 500);
        /**
         * @var MMWS\Abstract\Model[] $toParse
         */
        return !is_array($result)
            ? $result->toArray()
            : array_map(function ($item) {
                return $item->toArray();
            }, $result);
    }

    private function updateTimeout()
    {
    }

    private function updateInterval()
    {
    }
}
