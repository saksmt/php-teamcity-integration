smt/teamcity-integration
========================

Simple script to integrate (generate reports) with teamcity

Installation
------------

    composer global require smt/teamcity-integration

Now you can use it


Usage
-----

    run-inspections PATH_TO_YOUR_PROJECT


Configuration
=============

Configuration file is optional, however if you decided to change something you must place it in project root and name **ci.json**

Reference
---------

    {
        "inspections": { // Configuration for inspections (default below)
            "phpmd": { // Configuration for PHPMD
                "enabled": true, // Whether this inspection should be run
                "path": "phpmd", // How mess detector should be executed
                "format": "xml", // Output format
                "files": "src/", // Folder or files to process
                "resultPath": "res/mess.xml" // Path to file with report
                "inspections": [ // List of inspections of mess detector to enable
                    "cleancode",
                    "codesize",
                    "controversial",
                    "design",
                    "naming",
                    "unusedcode"
                ]
            },
            "phpcs": { // Configuration for CodeSniffer
                "enabled": true, // Whether this inspection should be run
                "path": "phpcs", // How CodeSniffer should be executed
                "files": "src/", // Folder or files to process
                "format": "checkstyle", // Output format
                "resultPath": "res/checkstyle.xml" // Path to file with report
            },
            "phpunit": { // Configuration for PHPUnit
                "enabled": true, // Whether this inspection should be run
                "path": "phpunit", // How PHPUnit should be executed
                "files": "src/", // Folder or files to process
                "bootstrap": "vendor/autoload.php", // Bootstrap file
                "configurationFile": null, // Configuration file for PHPUnit
                "format": "junit", // Test results output format
                "resultPath": "res/unit.xml", // Path to file with report about tests
                "coverage": { // Coverage configuration
                    "format": "clover", // Output format
                    "resultPath": "res/coverage.xml" // Path to file with report about coverage
                }
            }
        },
        "hooks": {
            "inspections": [ // Commands placed here would ran just before inspections starts
                "echo 'Some command here'"
            ],
            "cleanup": [ // Commands placed here would ran just before exit
                "echo 'Good Bye!'"
            ]
        }
    }