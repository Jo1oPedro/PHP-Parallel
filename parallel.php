<?php

use parallel\RunTime;

$runTime = new Runtime();

$runTime->run(function () {
    var_dump('Tarefa 2: ', debug_backtrace());
    echo 'Executando tarefa demorada 2' . PHP_EOL;
    sleep(8);
    echo 'Terminando tarefa demorada 2' . PHP_EOL;
});

$runTime2 = new Runtime();

$runTime2->run(function () {
    echo 'Tarefa 3';
    sleep(5);
});

var_dump('Tarefa 1: ', debug_backtrace());
echo 'Executando tarefa demora 1' . PHP_EOL;
sleep(10);
echo 'Finalizando tarefa demorada 1' . PHP_EOL;

