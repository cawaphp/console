<?php

/*
 * This file is part of the Сáша framework.
 *
 * (c) tchiotludo <http://github.com/tchiotludo>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare (strict_types = 1);

namespace Cawa\Console;

use Symfony\Component\Console\Output\ConsoleOutput as BaseConsoleOutput;
use Symfony\Component\Console\Output\ConsoleOutputInterface;

/**
 * @mixin BaseConsoleOutput|ConsoleOutputInterface|ConsoleOutput
 */
trait OutputTrait
{
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
     * Add timestamp/duration to given string
     *
     * @param string $message
     *
     * @return string
     */
    public function prefixWithTimestamp($message)
    {
        if (!($this->prefix & ConsoleOutput::PREFIX_TIMESTAMP || $this->prefix & ConsoleOutput::PREFIX_DURATION)) {
            return $message;
        }

        $diff = $this->previous ? round((microtime(true) - $this->previous), 3) : 0;
        $this->previous = microtime(true);

        $microtime = explode(' ', (string) microtime())[0];
        $microtime = substr((string) round($microtime, 3), 2, 3);
        $microtime = str_pad(is_bool($microtime) ? '0' : $microtime, 3, '0');

        if ($this->prefix & ConsoleOutput::PREFIX_TIMESTAMP && $this->prefix & ConsoleOutput::PREFIX_DURATION) {
            $prefix = sprintf(
                '<fg=cyan>[%s.%s]</> <fg=yellow>[+%s s]</>',
                date('Y-m-d H:i:s'),
                $microtime,
                str_pad((string) $diff, 9, ' ', STR_PAD_LEFT)
            );
        } elseif ($this->prefix & ConsoleOutput::PREFIX_DURATION) {
            $prefix = sprintf(
                '<fg=cyan>[+%s s]</>',
                str_pad((string) $diff, 9, ' ', STR_PAD_LEFT)
            );
        } else {
            $prefix = sprintf(
                '<fg=cyan>[%s.%s]</>',
                date('Y-m-d H:i:s'),
                $microtime
            );
        }

        $prefix = $this->getFormatter()->format($prefix);
        $message = str_pad($prefix, strlen($prefix) + 1) . $message;

        return $message;
    }
}
