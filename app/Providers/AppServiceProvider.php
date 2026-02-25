<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

// Models
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Challenge;
use App\Models\ChallengeAttempt;
use App\Models\ChatMessage;

// Policies
use App\Policies\QuizPolicy;
use App\Policies\QuizAttemptPolicy;
use App\Policies\ChallengePolicy;
use App\Policies\ChallengeAttemptPolicy;
use App\Policies\ChatMessagePolicy;

use App\Services\Compiler\CompilerServiceInterface;
use App\Services\Compiler\JdoodleCompilerService;

use App\Services\Chatbot\LLMProviderInterface;
use App\Services\Chatbot\DummyProvider;
use App\Services\Chatbot\GeminiProvider;
use App\Services\Chatbot\GroqProvider;
use App\Services\Chatbot\OpenAIProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        $this->app->bind(
            CompilerServiceInterface::class,
            JdoodleCompilerService::class
        );

        // Chatbot LLM provider (config-driven via CHATBOT_PROVIDER env)
        $this->app->singleton(LLMProviderInterface::class, function () {
            return match (config('services.chatbot.provider')) {
                'openai' => new OpenAIProvider(),
                'gemini' => new GeminiProvider(),
                'groq'   => new GroqProvider(),
                default  => new DummyProvider(),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ربط كل Model بالـ Policy المناسب
        Gate::policy(Quiz::class, QuizPolicy::class);
        Gate::policy(QuizAttempt::class, QuizAttemptPolicy::class);
        Gate::policy(Challenge::class, ChallengePolicy::class);
        Gate::policy(ChallengeAttempt::class, ChallengeAttemptPolicy::class);
        Gate::policy(ChatMessage::class, ChatMessagePolicy::class);
    }
}
