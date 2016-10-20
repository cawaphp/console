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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

class Command extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var int
     */
    protected $exitCode = 0;

    /**
     * @var float
     */
    protected $start;

    /**
     * @param float $start
     *
     * @return $this|self
     */
    public function setStart(float $start = null) : self
    {
        $this->start = $start ?? microtime(true);

        return $this;
    }

    /**
     * @return float
     */
    public function getDuration() : float
    {
        return $this->start ? round((microtime(true) - $this->start), 3) : 0;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @throws UserException
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
       return !is_null($this->exitCode) ? $this->exitCode : 0;
    }

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @param InputInterface $input
     *
     * @return $this|self
     */
    public function setInput(InputInterface $input) : self
    {
        $this->input = $input;

        return $this;
    }

    /**
     * @var ConsoleOutput
     */
    protected $output;

    /**
     * @param ConsoleOutput $output
     *
     * @return $this|self
     */
    public function setOutput(ConsoleOutput $output)
    {
        $this->output = $output;

        return $this;
    }
}
