<?php

require_once __DIR__ . '/../../../../vendor/autoload.php';

use Kit\Terminal\Output\Colorizer\Colorizer;
use Kit\Terminal\Output\Colorizer\Facade;

echo (new Colorizer())->fg(255, 00, 0)->text("橙色文字\n");

echo Facade::fg("#00ff00")->text("绿色文字\n");
