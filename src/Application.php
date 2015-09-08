<?php

namespace Smt\TeamCityIntegration\Application;

/**
 * Base application class
 * @package Smt\TeamCityIntegration\Application
 * @author Kirill Saksin <kirillsaksin@yandex.ru>
 */
class Application
{
    /**
     * @var string MessDetector command template
     */
    private $messDetectorCommand = '%s %s %s %s --reportfile "%s"';

    /**
     * @var string CodeSniffer command template
     */
    private $codeSnifferCommand = '%s --report-%s="%s" %s';

    /**
     * @var array Configuration
     */
    private $config = [
        'inspections' => [
            'phpmd' => [
                'enabled' => true,
                'path' => 'phpmd',
                'format' => 'xml',
                'files' => 'src/',
                'resultPath' => 'res/mess.xml',
                'inspections' => [
                    'cleancode',
                    'codesize',
                    'controversial',
                    'design',
                    'naming',
                    'unusedcode',
                ],
            ],
            'phpcs' => [
                'files' => 'src/',
                'enabled' => true,
                'path' => 'phpcs',
                'format' => 'checkstyle',
                'resultPath' => 'res/checkstyle.xml',
            ],
            'phpunit' => [
                'enabled' => true,
                'path' => 'phpunit',
                'bootstrap' => 'vendor/autoload.php',
                'files' => 'src/',
                'configurationFile' => null,
                'coverage' => [
                    'format' => 'clover',
                    'resultPath' => 'res/coverage.xml',
                ],
                'format' => 'junit',
                'resultPath' => 'res/unit.xml',
            ],
        ],
        'hooks' => [
            'inspections' => [],
            'cleanup' => [],
        ],
    ];

    /**
     * Run integration
     * @param string $path Path to project
     */
    public function run($path = '/srv')
    {
        chdir($path);
        $this->loadConfig();
        $this->installDependencies();
        $this->runBeforeInspectionsHook();
        $this->runInspections();
        $this->runBeforeCleanupHook();
    }

    /**
     * Merges configurations
     * @param array $original Default configuration
     * @param array $redefined User configuration
     * @return array Resulting configuration
     */
    private static function merge(array $original, array $redefined)
    {
        $result = [];
        foreach ($original as $key => $value) {
            if (isset($redefined[$key])) {
                if (is_array($redefined[$key]) && is_array($value)) {
                    $result[$key] = self::merge($value, $redefined[$key]);
                } else {
                    $result[$key] = $redefined[$key];
                }
                unset($redefined[$key]);
            } else {
                $result[$key] = $value;
            }
        }

        return array_merge_recursive($result, $redefined);
    }

    /**
     * Load configuration
     */
    private function loadConfig()
    {
        if (file_exists('ci.json')) {
            $this->config = self::merge($this->config, json_decode(file_get_contents('ci.json'), true));
        }
    }

    /**
     * Install dependencies
     */
    private function installDependencies()
    {
        system('composer update');
    }

    /**
     * Run before-inspections hooks
     */
    private function runBeforeInspectionsHook()
    {
        foreach ($this->config['hooks']['inspections'] as $hook) {
            system($hook);
        }
    }

    /**
     * Run inspections
     */
    private function runInspections()
    {
        foreach ($this->config['inspections'] as $inspectionName => $inspection) {
            if ($inspection['enabled']) {
                switch ($inspectionName) {
                    case 'phpmd':
                        $this->runMessDetector($inspection);
                        break;
                    case 'phpcs':
                        $this->runCodeSniffer($inspection);
                        break;
                    case 'phpunit':
                        $this->runUnitTests($inspection);
                        break;
                    default:
                        break;
                }
            }
        }
    }

    /**
     * Run inspections of MessDetector
     * @param array $inspection Inspection configuration
     */
    private function runMessDetector(array $inspection)
    {
        $messDetectorCommand = sprintf(
            $this->messDetectorCommand,
            $inspection['path'],
            $inspection['files'],
            $inspection['format'],
            implode(',', $inspection['inspections']),
            $inspection['resultPath']
        );
        system($messDetectorCommand);
    }

    /**
     * Run inspections of CodeSniffer
     * @param array $inspection Inspection configuration
     */
    private function runCodeSniffer(array $inspection)
    {
        system(sprintf(
            $this->codeSnifferCommand,
            $inspection['path'],
            $inspection['format'],
            $inspection['resultPath'],
            $inspection['files']
        ));
    }

    /**
     * Run unit tests
     * @param array $inspection Unit testing configuration
     */
    private function runUnitTests($inspection)
    {
        $baseString = sprintf(
            '%s --log-%s "%s" --coverage-%s "%s"',
            $inspection['path'],
            $inspection['format'],
            $inspection['resultPath'],
            $inspection['coverage']['format'],
            $inspection['coverage']['resultPath']
        );
        if (isset($inspection['configurationFile'])) {
            system(sprintf('%s -c %s', $baseString, $inspection['configurationFile']));
        } else {
            system(sprintf(
                '%s --bootstrap "%s" %s',
                $baseString,
                $inspection['bootstrap'],
                $inspection['files']
            ));
        }
    }

    /**
     * Run before-cleanup hooks
     */
    private function runBeforeCleanupHook()
    {
        foreach ($this->config['hooks']['cleanup'] as $hook) {
            system($hook);
        }
    }
}
