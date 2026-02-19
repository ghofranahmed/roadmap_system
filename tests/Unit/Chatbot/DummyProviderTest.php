<?php

use App\Services\Chatbot\DummyProvider;

/*
|--------------------------------------------------------------------------
| 12) Roadmap intent keywords → roadmap-aware reply
|--------------------------------------------------------------------------
*/

it('returns roadmap-aware reply when message contains roadmap intent keyword', function (string $keyword) {
    $provider = new DummyProvider();

    $result = $provider->chat([], "I want to know $keyword my courses", [
        'enrollments' => [
            [
                'roadmap_id'        => 1,
                'roadmap_title'     => 'Laravel Mastery',
                'level'             => 'beginner',
                'completed_lessons' => 3,
                'total_lessons'     => 10,
            ],
        ],
        'next_lessons' => [
            1 => ['lesson_title' => 'Routing Basics', 'unit_title' => 'Core Concepts'],
        ],
    ]);

    expect($result['reply'])->toContain('Laravel Mastery');
    expect($result['reply'])->toContain('Routing Basics');
    expect($result['tokens_used'])->toBeNull();
})->with(['next', 'roadmap', 'learn', 'progress']);

it('returns not-enrolled message when user has no enrollments and asks about roadmap', function () {
    $provider = new DummyProvider();

    $result = $provider->chat([], 'What should I learn next?', [
        'enrollments'  => [],
        'next_lessons' => [],
    ]);

    expect($result['reply'])->toContain('not enrolled');
    expect($result['tokens_used'])->toBeNull();
});

/*
|--------------------------------------------------------------------------
| 13) Default intent → generic teacher reply
|--------------------------------------------------------------------------
*/

it('returns help-style reply for explanation keywords', function () {
    $provider = new DummyProvider();

    $result = $provider->chat([], 'Can you explain polymorphism?', []);

    expect($result['reply'])->toContain('great question');
    expect($result['tokens_used'])->toBeNull();
});

it('returns generic teacher reply for unknown messages', function () {
    $provider = new DummyProvider();

    $result = $provider->chat([], 'Good morning', []);

    expect($result['reply'])->toContain('Smart Teacher');
    expect($result['reply'])->toContain('What would you like to know?');
    expect($result['tokens_used'])->toBeNull();
});

