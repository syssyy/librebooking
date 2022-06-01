<?php

define('LOG4PHP_ROOT', ROOT_DIR . 'lib/external/log4php/Logger.php');
require_once(LOG4PHP_ROOT);

class Log
{
    /**
     * @var Log
     */
    private static $_instance;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var Logger
     */
    private $sqlLogger;

    private function __construct()
    {
        $this->logger = new NullLog4php();
        $this->sqlLogger = new NullLog4php();

        if (file_exists($f = ROOT_DIR . 'config/log4php.config.xml')) {
            Logger::configure($f);
            $this->logger = Logger::getLogger('default');
            $this->sqlLogger = Logger::getLogger('sql');
        }
    }

    /**
     * @return Log
     */
    private static function &GetInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new Log();
        }

        return self::$_instance;
    }

    /**
     * @param string $message
     * @param mixed $args
     */
    public static function Debug($message, $args = [])
    {
        if (!self::GetInstance()->logger->isDebugEnabled()) {
            return;
        }

        try {
            $debug = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            if (is_array($debug)) {
                $debugInfo = $debug[0];
            } else {
                $debugInfo = ['file' => null, 'line' => null];
            }

            $args = func_get_args();
            $log = vsprintf(array_shift($args), array_values($args));
            $log .= sprintf(' [File=%s,Line=%s]', $debugInfo['file'], $debugInfo['line']);

            $log = '[User=' . ServiceLocator::GetServer()->GetUserSession() . '] ' . $log;

            self::GetInstance()->logger->debug($log);
        } catch (Exception $ex) {
            echo $ex;
        }
    }

    /**
     * @param string $message
     * @param mixed $args
     */
    public static function Error($message, $args = [])
    {
        if (!self::GetInstance()->logger->isEnabledFor(LoggerLevel::getLevelError())) {
            return;
        }

        try {
            $debug = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            if (is_array($debug)) {
                $debugInfo = $debug[0];
            } else {
                $debugInfo = ['file' => null, 'line' => null];
            }

            $args = func_get_args();
            $log = vsprintf(array_shift($args), array_values($args));
            $log .= sprintf(' [File=%s,Line=%s]', $debugInfo['file'], $debugInfo['line']);

            $log = '[User=' . ServiceLocator::GetServer()->GetUserSession() . '] ' . $log;

            self::GetInstance()->logger->error($log);
        } catch (Exception $ex) {
        }
    }

    /**
     * @static
     * @param string $message
     * @param mixed $args
     * @return void
     */
    public static function Sql($message, $args = [])
    {
        try {
            if (!self::GetInstance()->sqlLogger->isDebugEnabled()) {
                return;
            }
            $args = func_get_args();
            $log = vsprintf(array_shift($args), array_values($args));
            $log = '[User=' . ServiceLocator::GetServer()->GetUserSession() . '] ' . $log;
            self::GetInstance()->sqlLogger->debug($log);
        } catch (Exception $ex) {
        }
    }

    /**
     * @return bool
     */
    public static function DebugEnabled()
    {
        return self::GetInstance()->logger->isDebugEnabled();
    }
}

class NullLog4php
{
    public function error($log)
    {
    }

    public function debug($log)
    {
    }

    public function isDebugEnabled()
    {
        return false;
    }

    public function isEnabledFor($anything)
    {
        return false;
    }
}
