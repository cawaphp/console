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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

class Application extends \Symfony\Component\Console\Application
{
    /**
     * {@inheritdoc}
     */
    protected function doRunCommand(Command $command, InputInterface $input, OutputInterface $output)
    {
        self::$application = $this;

        $change = false;
        if (!in_array($command->getName(), [
            'help',
            'list',
        ])) {
            $change = true;
        }

        if ($change) {
            $output = new ConsoleOutput(
                $output->getVerbosity(),
                $output->isDecorated(),
                $output->getFormatter()
            );
      }

        if ($command instanceof \Cawa\Console\Command) {
            $command->setInput($input)
                ->setOutput($output)
                ->setStart();
        }

        return parent::doRunCommand($command, $input, $output);
    }

    /**
     * @var self
     */
    private static $application;

    /**
     * @param OutputInterface $output
     *
     * @return StreamOutput
     */
    private static function getStreamOutput(OutputInterface $output)
    {
        $memory = fopen('php://memory', 'rw');

        $errorStream = new StreamOutput(
            $memory,
            OutputInterface::VERBOSITY_VERY_VERBOSE,
            $output->isDecorated(),
            $output->getFormatter()
        );

        return $errorStream;
    }

    /**
     * {@inheritdoc}
     */
    public function renderException(\Exception $e, OutputInterface $output ) {
        if ($e instanceof UserException || !self::$application) {
            parent::renderException($e, $output);
        } else {
            self::writeException($e, $output);
        }
    }

    /**
     * @param \Throwable $e
     * @param OutputInterface $output
     */
    private function parentRenderException(\Throwable $e, OutputInterface $output)
    {
        parent::renderException($e, $output);
    }

    /**
     * @param \Throwable $e
     * @param OutputInterface $output
     * @param int $verbosity
     */
    public static function writeException(
        \Throwable $e,
        OutputInterface $output,
        int $verbosity = OutputInterface::VERBOSITY_NORMAL
    )
    {
        $errorStream = self::getStreamOutput($output);

        self::$application->parentRenderException($e, $errorStream);

        rewind($errorStream->getStream());
        $exception = stream_get_contents($errorStream->getStream());

        $console = new ConsoleOutput();

        $explode = explode("\n", rtrim($exception));
        foreach ($explode as $i => &$line) {
            if ($i !== 0) {
                $line = $console->prefixWithTimestamp($line);
            }
        }

        if (method_exists($output, 'getErrorOutput')) {
            $output->getErrorOutput()->write(implode("\n", $explode) . "\n", true, $verbosity);
        } else {
            $output->write(implode("\n", $explode) . "\n", true, $verbosity);
        }
    }
}
