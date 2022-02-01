<?php

/**
 * Port from symfony/process 3.x
 */
namespace SSHClient\ClientBuilder;

use Symfony\Component\Process\Exception\InvalidArgumentException;
use Symfony\Component\Process\Exception\LogicException;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessUtils;
use Traversable;
use function count;
use function is_array;

/**
 * Ported from symfony/process 3.x
 *
 * @author Kris Wallsmith <kris@symfony.com>
 */
class ProcessBuilder
{
    private $arguments;
    private $cwd;
    private $env = [];
    private $input;
    private $timeout = 60;
    private $options = [];
    private $prefix = [];
    private $outputDisabled = false;

    /**
     * @param string[] $arguments An array of arguments
     */
    public function __construct(array $arguments = [])
    {
        $this->arguments = $arguments;
    }

    /**
     * Creates a process builder instance.
     *
     * @param string[] $arguments An array of arguments
     *
     * @return static
     */
    public static function create(array $arguments = []): ProcessBuilder
    {
        return new static($arguments);
    }

    /**
     * Adds an unescaped argument to the command string.
     *
     * @param string $argument A command argument
     *
     * @return $this
     */
    public function add(string $argument): ProcessBuilder
    {
        $this->arguments[] = $argument;

        return $this;
    }

    /**
     * Adds a prefix to the command string.
     *
     * The prefix is preserved when resetting arguments.
     *
     * @param string|array $prefix A command prefix or an array of command prefixes
     *
     * @return $this
     */
    public function setPrefix($prefix): ProcessBuilder
    {
        $this->prefix = is_array($prefix) ? $prefix : [$prefix];

        return $this;
    }

    /**
     * Sets the arguments of the process.
     *
     * Arguments must not be escaped.
     * Previous arguments are removed.
     *
     * @param string[] $arguments
     *
     * @return $this
     */
    public function setArguments(array $arguments): ProcessBuilder
    {
        $this->arguments = $arguments;

        return $this;
    }

    /**
     * Sets the working directory.
     *
     * @param string|null $cwd The working directory
     *
     * @return $this
     */
    public function setWorkingDirectory(?string $cwd): ProcessBuilder
    {
        $this->cwd = $cwd;

        return $this;
    }

    /**
     * Sets an environment variable.
     *
     * Setting a variable overrides its previous value. Use `null` to unset a
     * defined environment variable.
     *
     * @param string $name  The variable name
     * @param string|null $value The variable value
     *
     * @return $this
     */
    public function setEnv(string $name, ?string $value): ProcessBuilder
    {
        $this->env[$name] = $value;

        return $this;
    }

    /**
     * Adds a set of environment variables.
     *
     * Already existing environment variables with the same name will be
     * overridden by the new values passed to this method. Pass `null` to unset
     * a variable.
     *
     * @param array $variables The variables
     *
     * @return $this
     */
    public function addEnvironmentVariables(array $variables): ProcessBuilder
    {
        $this->env = array_replace($this->env, $variables);

        return $this;
    }

    /**
     * Sets the input of the process.
     *
     * @param resource|string|int|float|bool|Traversable|null $input The input content
     *
     * @return $this
     *
     * @throws InvalidArgumentException In case the argument is invalid
     */
    public function setInput($input): ProcessBuilder
    {
        $this->input = ProcessUtils::validateInput(__METHOD__, $input);

        return $this;
    }

    /**
     * Sets the process timeout.
     *
     * To disable the timeout, set this value to null.
     *
     * @param float|null $timeout
     *
     * @return $this
     *
     * @throws InvalidArgumentException
     */
    public function setTimeout(?float $timeout): ProcessBuilder
    {
        if (null === $timeout) {
            $this->timeout = null;

            return $this;
        }

        if ($timeout < 0) {
            throw new InvalidArgumentException('The timeout value must be a valid positive integer or float number.');
        }

        $this->timeout = $timeout;

        return $this;
    }

    /**
     * Adds a proc_open option.
     *
     * @param string $name  The option name
     * @param string $value The option value
     *
     * @return $this
     */
    public function setOption(string $name, string $value): ProcessBuilder
    {
        $this->options[$name] = $value;

        return $this;
    }

    /**
     * Disables fetching output and error output from the underlying process.
     *
     * @return $this
     */
    public function disableOutput(): ProcessBuilder
    {
        $this->outputDisabled = true;

        return $this;
    }

    /**
     * Enables fetching output and error output from the underlying process.
     *
     * @return $this
     */
    public function enableOutput(): ProcessBuilder
    {
        $this->outputDisabled = false;

        return $this;
    }

    /**
     * Creates a Process instance and returns it.
     *
     * @return Process
     *
     * @throws LogicException In case no arguments have been provided
     */
    public function getProcess(): Process
    {
        if (0 === count($this->prefix) && 0 === count($this->arguments)) {
            throw new LogicException('You must add() command arguments before calling getProcess().');
        }

        $arguments = array_merge($this->prefix, $this->options, $this->arguments);
        $process = new Process($arguments, $this->cwd, $this->env, $this->input, $this->timeout);

        if ($this->outputDisabled) {
            $process->disableOutput();
        }

        return $process;
    }
}
