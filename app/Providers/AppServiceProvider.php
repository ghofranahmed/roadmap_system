<?php

namespace App\Providers;
use Illuminate\View\ViewServiceProvider;
use Illuminate\Session\SessionServiceProvider;
use Illuminate\Cookie\CookieServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
// Models
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Challenge;
use App\Models\ChallengeAttempt;
use App\Models\ChatMessage;
use App\Models\User;
use App\Models\Announcement;
use App\Models\Roadmap;
use App\Models\LearningUnit;
use App\Models\Lesson;
use App\Models\SubLesson;
use App\Models\Resource;
use App\Models\QuizQuestion;

// Policies
use App\Policies\QuizPolicy;
use App\Policies\QuizAttemptPolicy;
use App\Policies\ChallengePolicy;
use App\Policies\ChallengeAttemptPolicy;
use App\Policies\ChatMessagePolicy;
use App\Policies\UserPolicy;
use App\Policies\AnnouncementPolicy;
use App\Policies\RoadmapPolicy;
use App\Policies\LearningUnitPolicy;
use App\Policies\LessonPolicy;
use App\Policies\SubLessonPolicy;
use App\Policies\ResourcePolicy;
use App\Policies\QuizQuestionPolicy;
use App\Models\Notification;
use App\Policies\NotificationPolicy;

use App\Services\Compiler\CompilerServiceInterface;
use App\Services\Compiler\JdoodleCompilerService;

use App\Services\Chatbot\LLMProviderInterface;
use App\Services\Chatbot\GeminiProvider;
use App\Services\Chatbot\GroqProvider;
use App\Services\Chatbot\OpenAIProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            CompilerServiceInterface::class,
            JdoodleCompilerService::class
        );

        // Chatbot LLM provider (DB settings take precedence over config)
        // STRICT: No fallback to DummyProvider. Must explicitly configure a valid provider.
        $this->app->singleton(LLMProviderInterface::class, function () {
            // Try to get provider from DB settings first
            try {
                $settings = \App\Models\ChatbotSetting::getSettings();
                $provider = $settings->provider;
            } catch (\Exception $e) {
                // If DB table doesn't exist yet or error, fallback to config
                $provider = config('services.chatbot.provider');
            }
            
            if (empty($provider)) {
                throw new \RuntimeException(
                    'CHATBOT_PROVIDER is not set. Please set it to one of: openai, gemini, groq, dummy'
                );
            }
            
            return match ($provider) {
                'openai' => new OpenAIProvider(),
                'gemini' => new GeminiProvider(),
                'groq'   => new GroqProvider(),
                'dummy'  => new \App\Services\Chatbot\DummyProvider(),
                default  => throw new \RuntimeException(
                    "Invalid CHATBOT_PROVIDER value: '{$provider}'. Must be one of: openai, gemini, groq, dummy"
                ),
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
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Announcement::class, AnnouncementPolicy::class);
        Gate::policy(Roadmap::class, RoadmapPolicy::class);
        Gate::policy(LearningUnit::class, LearningUnitPolicy::class);
        Gate::policy(Lesson::class, LessonPolicy::class);
        Gate::policy(SubLesson::class, SubLessonPolicy::class);
        Gate::policy(Resource::class, ResourcePolicy::class);
        Gate::policy(QuizQuestion::class, QuizQuestionPolicy::class);
        Gate::policy(Notification::class, NotificationPolicy::class);

        // Apply system settings to admin panel config
        try {
            $appName = \App\Models\SystemSetting::get('app_name');
            if ($appName) {
                config(['adminlte.title' => $appName]);
            }

            $appLogo = \App\Models\SystemSetting::get('app_logo');
            if ($appLogo && \Storage::disk('public')->exists($appLogo)) {
                config(['adminlte.logo_img' => \Storage::disk('public')->url($appLogo)]);
            }
        } catch (\Exception $e) {
            // Ignore if tables don't exist yet
        }
    }
}
