<?php
namespace Memaw\Phpcs;

class Cli
{
    /**
     * Default values for phpcs
     *
     * @var array
     */
    protected $defaultValues = array();

    /**
     * Constructor
     *
     * @param array $defaultValues Default values for phpcs
     */
    public function __construct($defaultValues = array())
    {
        if (isset($defaultValues['configurationFile'])) {
            $configurationFilename = $defaultValues['configurationFile'];
        } elseif (file_exists(getcwd() . '/memaw-phpcs.php')) {
            $configurationFilename = getcwd() . '/memaw-phpcs.php';
        } elseif (file_exists(getcwd() . '/memaw-phpcs.php.dist')) {
            $configurationFilename = getcwd() . '/memaw-phpcs.php.dist';
        }
        if (isset($configurationFilename)) {
            $this->defaultValues = json_decode(require $configurationFilename, true);
        }
        if (! empty($defaultValues)) {
            $this->defaultValues = array_merge($this->defaultValues, $defaultValues);
        }
        if (! isset($this->defaultValues['installed_paths'])) {
            $this->defaultValues['installed_paths'] = $this->getMemawStandardsPath();
        } else {
            $this->defaultValues['installed_paths'] .= ',' . $this->getMemawStandardsPath();
        }
        if (! isset($this->defaultValues['standard'])) {
            $this->defaultValues['standard'] = array('Memaw');
        }
    }

    /**
     * Run the coding style check
     *
     * @return int
     */
    public function process()
    {
        $this->setDefaultValues();
        \PHP_CodeSniffer_Reporting::startTiming();
        $phpcs = new \PHP_CodeSniffer_CLI();
        $phpcs->checkRequirements();

        $values        = $phpcs->getCommandLineValues();
        foreach ($this->defaultValues as $k => $v) {
            if (empty($values[$k])) {
                $values[$k] = $v;
            }
        }

        return $phpcs->process($values);
    }

    /**
     * Get the path of the additional standards directory
     *
     * @return string
     */
    public function getMemawStandardsPath()
    {
        return __DIR__ . '/Standards';
    }

    /**
     * Set default values in context
     *
     * @return null
     */
    protected function setDefaultValues()
    {
        $GLOBALS['PHP_CODESNIFFER_CONFIG_DATA'] = $this->defaultValues;
    }
}
