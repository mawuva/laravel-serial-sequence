<?php

declare(strict_types=1);

use Mawuva\LaravelSerialSequence\Services\SerialGenerator;

beforeEach(function () {
    // Clean up before each test
    \Mawuva\LaravelSerialSequence\Models\SerialSequence::query()->delete();

    // Reset config to defaults
    config([
        'serial-sequence.separator' => '-',
        'serial-sequence.prefix_separator' => '/',
        'serial-sequence.number_length' => 6,
        'serial-sequence.month_length' => 2,
        'serial-sequence.year_length' => 2,
        'serial-sequence.prefix_resolver' => null,
    ]);
});

it('uses custom separator configuration', function () {
    config(['serial-sequence.separator' => '_']);

    $generator = new SerialGenerator;
    $serialData = $generator->generate('TEST');

    expect($serialData->serial)->toContain('TEST_');
    expect($serialData->serial)->not->toContain('TEST-');
});

it('uses custom prefix separator configuration', function () {
    config(['serial-sequence.prefix_separator' => '|']);

    $generator = new SerialGenerator;
    $serialData = $generator->generate('TEST', 'PREFIX');

    expect($serialData->serial)->toStartWith('PREFIX|');
    expect($serialData->serial)->not->toContain('PREFIX/');
});

it('uses custom number length configuration', function () {
    config(['serial-sequence.number_length' => 4]);

    $generator = new SerialGenerator;
    $serialData = $generator->generate('TEST');

    expect($serialData->serial)->toContain('-0001');
    expect($serialData->serial)->not->toContain('-000001');
});

it('uses custom month length configuration', function () {
    config(['serial-sequence.month_length' => 1]);

    $generator = new SerialGenerator;
    $serialData = $generator->generate('TEST');

    $currentMonth = ltrim(now()->format('m'), '0');
    expect($serialData->serial)->toContain("-{$currentMonth}");
    expect($serialData->serial)->not->toContain('-0'.$currentMonth);
});

it('uses custom year length configuration', function () {
    config(['serial-sequence.year_length' => 4]);

    $generator = new SerialGenerator;
    $serialData = $generator->generate('TEST');

    $currentYear = now()->format('Y');
    expect($serialData->serial)->toContain(now()->format('m').$currentYear);
});

it('combines multiple custom configurations', function () {
    config([
        'serial-sequence.separator' => '.',
        'serial-sequence.prefix_separator' => ':',
        'serial-sequence.number_length' => 3,
        'serial-sequence.month_length' => 1,
        'serial-sequence.year_length' => 4,
    ]);

    $generator = new SerialGenerator;
    $serialData = $generator->generate('INV', 'PREFIX');

    $currentYear = now()->format('Y');
    $currentMonth = ltrim(now()->format('m'), '0');

    expect($serialData->serial)->toBe("PREFIX:INV.{$currentMonth}{$currentYear}.001");
});

it('handles edge case number length of 1', function () {
    config(['serial-sequence.number_length' => 1]);

    $generator = new SerialGenerator;
    $serialData = $generator->generate('TEST');

    expect($serialData->serial)->toContain('-1');
});

it('handles large number length', function () {
    config(['serial-sequence.number_length' => 10]);

    $generator = new SerialGenerator;
    $serialData = $generator->generate('TEST');

    expect($serialData->serial)->toContain('-0000000001');
});
