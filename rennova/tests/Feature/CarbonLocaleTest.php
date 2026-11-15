<?php

use Carbon\Carbon;

it('formatea los días de la semana en español', function () {
    expect(Carbon::parse('2026-11-02')->isoFormat('dddd'))->toBe('lunes');
});

it('formatea los meses en español', function () {
    expect(Carbon::parse('2026-01-15')->isoFormat('MMM'))->toBe('ene.');
});
