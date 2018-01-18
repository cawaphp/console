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

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Command extends \Symfony\Component\Console\Command\Command
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
        $this->differential = $this->start;

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
     * @var float
     */
    protected $differential;

    /**
     * @return float
     */
    public function getDifferentialDuration() : float
    {
        $return = round((microtime(true) - $this->differential), 3);
        $this->differential = microtime(true);

        return $return;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
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
     * @return InputInterface
     */
    public function getInput() : InputInterface
    {
        return $this->input;
    }

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
     * @return ConsoleOutput
     */
    public function getOutput() : ConsoleOutput
    {
        return $this->output;
    }

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

    /**
     * @param string $class
     * @param ConsoleOutput $output
     * @param array $inputs
     *
     * @return int
     */
    public static function executeCommand(string $class, ConsoleOutput $output, array $inputs) : int
    {
        if (!class_exists($class)) {
            throw new \RuntimeException((sprintf("Undefined class '%s'", $class)));
        }

        $command = new $class();

        if (!$command instanceof Command) {
            throw new \RuntimeException(sprintf("Invalid class '%s'", $class));
        }

        $input = new ArrayInput($inputs, $command->getDefinition());

        $command
            ->setInput($input)
            ->setOutput($output)
            ->setStart();

        return $command->execute($input, $output);
    }
}
