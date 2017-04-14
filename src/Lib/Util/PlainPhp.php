<?php
namespace My\Web\Lib\Util;

/**
 * This is a replacement of old PHP include/require way such as:
 *
 * ```
 * extract($someVariableArray);
 * require $someFilePath;
 * ```
 *
 * Example 1: Template evaluation
 *
 * ```
 * PlainPhp::runner()
 *     ->binding($viewContext)
 *     ->with([
 *         'foo' => $foo,
 *         'bar' => $bar,
 *     ])
 *     ->doRequire($templateFile);
 * ```
 *
 * Example 2: Configuration parameter loading
 *
 * ```
 * $runner = PlainPhp::runner()->with(
 *     'debug' => getenv('APP_ENV_DEBUG'),
 * );
 * $params1 = $runner->doRequire(__DIR__ . '/params1.php');
 * $params2 = $runner->doRequire(__DIR__ . '/params2.php');
 * ```
 *
 * Advantage
 *
 * - Safer against unexpected variable violation
 * - Controllable `$this` variable assignment
 * - Reusable binding context
 */
class PlainPhp
{
    protected $boundedObject = null;

    protected $vars = [];

    /**
     * Factory
     *
     * @return static
     */
    public static function runner()
    {
        return new static();
    }

    /**
     * Returns new instance binding given object as script's $this variable.
     *
     * @param object $object
     * @return static
     */
    public function binding($object)
    {
        $that = clone $this;
        $that->boundedObject = $object;
        return $that;
    }

    /**
     * Returns new instance having more script variables.
     *
     * @param array $vars
     * @return PlainPhp
     */
    public function with(array $vars)
    {
        $that = clone $this;
        $that->vars = array_merge($that->vars, $vars);
        return $that;
    }

    /**
     * Do require and returns result.
     *
     * @param string $filename
     * @return mixed
     */
    public function doRequire($filename)
    {
        return $this->run($filename,'require');
    }

    /**
     * Do include and returns result.
     *
     * @param string $filename
     * @return mixed
     */
    public function doInclude($filename)
    {
        return $this->run($filename,'@include');
    }

    /**
     * Internal implementation of require and include.
     *
     * @param string $filename
     * @param string $statement
     * @return mixed
     */
    protected function run($filename, $statement)
    {
        $runner = function ($_statement_, $_filename_, $_vars_) {
            if (!is_file($_filename_)) {
                throw new \InvalidArgumentException('File not exists: ' . $_filename_);
            }
            extract($_vars_);
            $_ = null;
            eval('$_ = ' . $_statement_ . ' $_filename_;');
            return $_;
        };

        if ($this->boundedObject) {
            $runner = $runner->bindTo($this->boundedObject);
        }

        return $runner($statement, $filename, $this->vars);
    }
}
