<?php

require_once(__DIR__ . '/../vendor/autoload.php');
date_default_timezone_set('Europe/Oslo');
Carbon\Carbon::setLocale('nb');

require_once(Netflex\SDK::bootstrap);
