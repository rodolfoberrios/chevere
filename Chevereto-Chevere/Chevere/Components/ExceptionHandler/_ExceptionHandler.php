<?php

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevere.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Chevere\Components\ExceptionHandler;

use DateTime;
use DateTimeZone;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LogLevel;
use Chevere\Components\App\Instances\RequestInstance;
use Chevere\Components\App\Instances\RuntimeInstance;
use Chevere\Components\Data\Data;
use Chevere\Components\Data\Traits\DataMethodTrait;
use Chevere\Components\ExceptionHandler\src\Formatter;
use Chevere\Components\ExceptionHandler\src\Output;
use Chevere\Components\ExceptionHandler\src\Style;
use Chevere\Components\ExceptionHandler\src\Template;
use Chevere\Components\ExceptionHandler\src\Wrap;
use Chevere\Components\Path\PathApp;
use Chevere\Components\Runtime\Runtime;
use Chevere\Components\Http\Interfaces\RequestInterface;

/**
 * The Chevere exception handler.
 */
final class _ExceptionHandler
{
    use DataMethodTrait;

    /** @var string Relative folder where logs will be stored */
    const LOG_DATE_FOLDER_FORMAT = 'Y/m/d/';

    /** @var ?bool Null will read app/config.php. Any boolean value will override that */
    const DEBUG = null;

    /** @var string */
    // FIXME:
    const PATH_LOGS = 'var/logs/';

    private RequestInterface $request;

    private bool $isDebugEnabled;

    private string $loggerLevel;

    private Wrap $wrap;

    /** @var string */
    private string $logDateFolderFormat;

    private Logger $logger;

    private Runtime $runtime;

    private Output $output;

    /**
     * @param mixed $args Arguments passed to the error exception (severity, message, file, line; Exception)
     */
    public function __construct(...$args)
    {
        $this->data = new Data([]);
        $this->setTimeProperties();
        $this->data = $this->data
            ->withAddedKey('id', uniqid('', true));
        $this->request = RequestInstance::get();
        $this->runtime = RuntimeInstance::get();
        $this->isDebugEnabled = (bool) $this->runtime->data()->key('debug');
        $this->logDateFolderFormat = static::LOG_DATE_FOLDER_FORMAT;
        $this->wrap = new Wrap($args[0]);
        $this->loggerLevel = $this->wrap->data()->key('loggerLevel');
        $this->setLogFilePathProperties();
        $this->setLogger();

        $formatter = new Formatter($this);
        $formatter = $formatter
            ->withLineBreak(Template::BOX_BREAK_HTML)
            ->withCss(Style::CSS);

        $this->output = new Output($this, $formatter);
        $this->loggerWrite();
        $this->output->out();
    }

    public function isDebugEnabled(): bool
    {
        return $this->isDebugEnabled;
    }

    public static function exception($exception): void
    {
        new static($exception);
    }

    private function setLogFilePathProperties(): void
    {
        $absolute = (new PathApp('var/logs/'))->absolute();
        $date = gmdate($this->logDateFolderFormat, $this->data->key('timestamp'));
        $id = $this->data->key('id');
        $timestamp = $this->data->key('timestamp');
        $logFilename = $absolute . $this->loggerLevel . '/' . $date . $timestamp . '_' . $id . '.log';
        $this->data = $this->data
            ->withAddedKey('logFilename', $logFilename);
    }

    private function setLogger(): void
    {
        $lineFormatter = new LineFormatter(null, null, true, true);
        $logFilename = $this->data->key('logFilename');
        $streamHandler = new StreamHandler($logFilename);
        $streamHandler->setFormatter($lineFormatter);
        $this->logger = new Logger(__NAMESPACE__);
        $this->logger->setTimezone(new DateTimeZone('UTC'));
        $this->logger->pushHandler($streamHandler);
        $this->logger->pushHandler(new FirePHPHandler());
    }

    private function loggerWrite(): void
    {
        $log = strip_tags($this->output->textPlain());
        $log .= "\n\n" . str_repeat('=', Formatter::COLUMNS);
        $this->logger->log($this->loggerLevel, $log);
    }
}