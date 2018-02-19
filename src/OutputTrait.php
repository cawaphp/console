<?php

/*
 * This file is part of the Сáша framework.
 *
 * (c) tchiotludo <http://github.com/tchiotludo>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Cawa\Console;

use Symfony\Component\Console\Output\ConsoleOutput as BaseConsoleOutput;
use Symfony\Component\Console\Output\ConsoleOutputInterface;

/**
 * @mixin BaseConsoleOutput|ConsoleOutputInterface|ConsoleOutput
 */
trait OutputTrait
{
    /**
     * @var Command
     */
    private $command;

    /**
     * @param Command $command
     *
     * @return self
     */
    public function setCommand(Command $command) : self
    {
        $this->command = $command;

        if ($this instanceof ConsoleOutput && $this->getErrorOutput() instanceof ErrorOutput) {
            $this->getErrorOutput()->setCommand($command);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function doWrite($message, $newline)
    {
        // bug with progress bar don't understand
        if ($message === "\e[2K") {
            return parent::doWrite("\033[2K", false);
        }

        if (trim($message) != '') {
            $message = $this->prefixWithTimestamp($message);
        }

        parent::doWrite($message, $newline);
    }

    /**
     * @var bool
     */
    private $prefix = ConsoleOutput::PREFIX_TIMESTAMP | ConsoleOutput::PREFIX_DURATION;

    /**
     * @param int $prefix
     *
     * @return $this|self
     */
    public function setPrefix(int $prefix) : self
    {
        $this->prefix = $prefix;

        if ($this instanceof ConsoleOutput) {
            $this->getErrorOutput()->setPrefix($prefix);
        }

        return $this;
    }

    /**
     * @var float
     */
    private $previous;

    /**
     * Add timestamp/duration to given string.
     *
     * @param string $message
     *
     * @return string
     */
    public function prefixWithTimestamp($message)
    {
        if (!(
            $this->prefix & ConsoleOutput::PREFIX_TIMESTAMP ||
            $this->prefix & ConsoleOutput::PREFIX_DURATION ||
            $this->prefix & ConsoleOutput::PREFIX_CLASSNAME
        )) {
            return $message;
        }

        $diff = $this->previous ? round((microtime(true) - $this->previous), 3) : 0;
        $this->previous = microtime(true);

        $microtime = explode(' ', (string) microtime())[0];
        $microtime = substr((string) round($microtime, 3), 2, 3);
        $microtime = str_pad(is_bool($microtime) ? '0' : $microtime, 3, '0');

        $prefixs = [];

        if ($this->prefix & ConsoleOutput::PREFIX_TIMESTAMP) {
            $prefixs[] = sprintf(
                '<fg=yellow>[%s.%s]</>',
                date('Y-m-d H:i:s'),
                $microtime
            );
        }

        if ($this->prefix & ConsoleOutput::PREFIX_DURATION) {
            $prefixs[] = sprintf(
                '<fg=cyan>[+%s s]</>',
                str_pad((string) $diff, 9, ' ', STR_PAD_LEFT)
            );
        }

        if ($this->prefix & ConsoleOutput::PREFIX_CLASSNAME && isset($this->command)) {
            $prefixs[] = sprintf(
                '<fg=magenta>[%s]</>',
                get_class($this->command)
            );
        }

        $prefix = implode(' ', $prefixs);

        $prefix = $this->getFormatter()->format($prefix);
        $message = str_pad($prefix, strlen($prefix) + 1) . $message;

        return $message;
    }
}
