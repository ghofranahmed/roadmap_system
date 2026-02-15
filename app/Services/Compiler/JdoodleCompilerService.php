<?php

namespace App\Services\Compiler;

use Illuminate\Support\Facades\Http;

class JdoodleCompilerService implements CompilerServiceInterface
{
    protected $clientId;
    protected $clientSecret;
    protected $apiUrl = 'https://api.jdoodle.com/v1/execute';

    public function __construct()
    {
        $this->clientId = config('services.jdoodle.client_id');
        $this->clientSecret = config('services.jdoodle.client_secret');
    }

    public function execute(string $code, string $language, array $testCases = []): array
    {
        // هنا يمكنك تنفيذ كل test case على حدة وجمع النتائج
        // لكن JDoodle تنفذ قطعة واحدة. سنقوم بتنفيذ الكود مع stdin فارغ أو مع test cases
        // للتبسيط سنفترض أن الكود يقرأ من stdin حسب test case.

        // إرسال الكود إلى JDoodle
        $response = Http::post($this->apiUrl, [
            'clientId' => $this->clientId,
            'clientSecret' => $this->clientSecret,
            'script' => $code,
            'language' => $this->mapLanguage($language),
            'versionIndex' => $this->getVersionIndex($language),
            'stdin' => '', // يمكن تمرير test case هنا
        ]);

        if ($response->failed()) {
            return [
                'output' => '',
                'success' => false,
                'error' => 'Compiler API error',
            ];
        }

        $data = $response->json();

        return [
            'output' => $data['output'] ?? '',
            'success' => !($data['statusCode'] ?? 0),
            'error' => $data['error'] ?? null,
        ];
    }

    protected function mapLanguage($language)
    {
        $map = [
            'php' => 'php',
            'python' => 'python3',
            'javascript' => 'nodejs',
            'java' => 'java',
            'cpp' => 'cpp17',
        ];
        return $map[$language] ?? 'php';
    }

    protected function getVersionIndex($language)
    {
        // JDoodle يحتاج versionIndex
        return 0;
    }
}