<?php

use HalilCosdu\LogWeaver\LogWeaver;

it('can be instantiated', function () {
    $logWeaver = new LogWeaver();

    expect($logWeaver)->toBeInstanceOf(LogWeaver::class);
});

it('can set description', function () {
    $logWeaver = new LogWeaver();
    $logWeaver->description('Test Description');

    expect($logWeaver->getDescription())->toBe('Test Description');
});

it('can set log resource', function () {
    $logWeaver = new LogWeaver();
    $logWeaver->logResource('event');

    expect($logWeaver->getLogResource())->toBe('event');
});

it('can set content', function () {
    $logWeaver = new LogWeaver();
    $logWeaver->content(['user_id' => 1]);

    expect($logWeaver->getContent())->toBe(['user_id' => 1]);
});

it('can set level', function () {
    $logWeaver = new LogWeaver();
    $logWeaver->level('info');

    expect($logWeaver->getLevel())->toBe('info');
});

it('can set disk', function () {
    $logWeaver = new LogWeaver();
    $logWeaver->disk('local');

    expect($logWeaver->getDisk())->toBe('local');
});

it('can set directory', function () {
    $logWeaver = new LogWeaver();
    $logWeaver->directory('custom_logs');

    expect($logWeaver->getDirectory())->toBe('custom_logs');
});
