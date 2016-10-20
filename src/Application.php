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
        if (!in_array($command->getName(), [
            'help',
            'list',
        ])) {
            $output = new ConsoleOutput(
                $output->getVerbosity(),
                $output->isDecorated(),
                $output->getFormatter()
            );
        }

        if ($command instanceof \Cawa\Console\Command) {
            $command->setInput($input);
            $command->setOutput($output);
        }

        return parent::doRunCommand($command, $input, $output);
    }

    /**
     * @param OutputInterface $output
     *
     * @return StreamOutput
     */
    public static function getStreamOutput(OutputInterface $output)
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
    public function renderException(\Exception $e, OutputInterface $output)
    {
        if ($e instanceof UserException) {
            parent::renderException($e, $output);
        } else {
            $errorStream = self::getStreamOutput($output);

            parent::renderException($e, $errorStream);

            rewind($errorStream->getStream());
            $exception = stream_get_contents($errorStream->getStream());

            $console = new ConsoleOutput();

            $explode = explode("\n", rtrim($exception));
            foreach ($explode as &$line) {
                $line = $console->prefixWithTimestamp($line);
            }

            $output->write(implode("\n", $explode) . "\n");
        }
    }
}
