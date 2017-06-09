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

use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\ConsoleOutput as BaseConsoleOutput;
use Symfony\Component\Console\Output\ConsoleOutputInterface;

class ConsoleOutput extends BaseConsoleOutput implements ConsoleOutputInterface
{
    use OutputTrait;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        $verbosity = self::VERBOSITY_NORMAL,
        $decorated = null,
        OutputFormatterInterface $formatter = null
    ) {
        parent::__construct($verbosity, $decorated, $formatter);

        $error = fopen($this->hasStderrSupport() ? 'php://stderr' : 'php://output', 'w');
        $this->setErrorOutput(new ErrorOutput($error,
            $this->getVerbosity(),
            $this->isDecorated(),
            $this->getFormatter()
        ));
    }

    const PREFIX_TIMESTAMP = 1;
    const PREFIX_DURATION = 2;

    const ERROR_ERROR = '<bg=red;fg=white>%s</>';
    const ERROR_WARNING = '<fg=red>%s</>';

    /**
     * @param string $text
     * @param string $type
     *
     * @return $this|self
     */
    public function writeError(string $text, string $type = self::ERROR_ERROR) : self
    {
        $this->getErrorOutput()->writeln(sprintf($type, $text));

        return $this;
    }
}
