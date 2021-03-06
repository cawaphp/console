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

use Cawa\App\AbstractApp;
use Cawa\Error\ErrorEvent;
use Cawa\Events\DispatcherFactory;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Debug\Exception\FatalThrowableError;

class App extends AbstractApp
{
    use DispatcherFactory;

    /**
     * @var int
     */
    private static $exitCode;

    /**
     * @var Application
     */
    private static $application;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        set_error_handler(['Cawa\Error\Handler', 'errorHandler']);
        set_exception_handler([__CLASS__, 'exceptionHandler']);

        self::$application = new Application();
        self::$application->setAutoExit(false);
        self::$application->setCatchExceptions(false);
    }

    /**
     * @param \Throwable $exception
     */
    public static function exceptionHandler(\Throwable $exception)
    {
        // This error code is not included in error_reporting
        if (!error_reporting() || $exception->getLine() == 0) {
            return;
        }

        self::emit(new ErrorEvent($exception));

        $output = new ConsoleOutput(
            OutputInterface::VERBOSITY_VERY_VERBOSE
        );

        if (!$exception instanceof \Exception) {
            $exception = new FatalThrowableError($exception);
        }

        self::$application->renderException($exception, $output);
    }

    /**
     * @param Command $command
     *
     * @return $this|App
     */
    public function addCommand(Command $command) : self
    {
        self::$application->add($command);

        return $this;
    }

    /**
     * @param string $path
     *
     * @return $this|self
     */
    public function addCommandDir(string $path) : self
    {
        $declared = get_declared_classes();

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(rtrim($path)));
        $files = new RegexIterator($iterator, '`^.*\.php$`', RegexIterator::GET_MATCH);

        foreach ($files as $file) {
            if (array_search($file[0], get_included_files()) === false) {
                require $file[0];
            }
        }

        $currentClasses = get_declared_classes();

        foreach (array_diff($currentClasses, $declared) as $class) {
            $reflection = new \ReflectionClass($class);
            if ($reflection->isInstantiable() && is_subclass_of($class, Command::class)) {
                self::$application->add(new $class());
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        self::$exitCode = self::$application->run();
    }

    /**
     * {@inheritdoc}
     */
    public function end()
    {
        parent::end();

        if (self::$exitCode > 255) {
            self::$exitCode = 255;
        }

        exit(self::$exitCode);
    }
}
