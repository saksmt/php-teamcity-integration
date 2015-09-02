<?php
/**
 * @author Kirill Saksin <kirillsaksin@yandex.ru>
 */

(new \Smt\TeamCityIntegration\Application\Application())->run(isset($argv[1]) ? $argv[1] : '/srv');
