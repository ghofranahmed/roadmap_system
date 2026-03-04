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

        // Validate that credentials are set
        if (empty($this->clientId) || empty($this->clientSecret)) {
            throw new \RuntimeException(
                'JDoodle credentials are not configured. Please set JDoodle_CLIENT_ID and JDoodle_CLIENT_SECRET in your .env file.'
            );
        }
    }

    public function execute(string $code, string $language, string $stdin = ''): array
    {
        // Send code to JDoodle API with the provided stdin
        $response = Http::post($this->apiUrl, [
            'clientId' => $this->clientId,
            'clientSecret' => $this->clientSecret,
            'script' => $code,
            'language' => $this->mapLanguage($language),
            'versionIndex' => $this->getVersionIndex($language),
            'stdin' => $stdin, // Pass the actual stdin from test case
        ]);

        if ($response->failed()) {
            $statusCode = $response->status();
            $errorBody = $response->body();
            
            return [
                'output' => '',
                'success' => false,
                'error' => "Compiler API error (HTTP {$statusCode}): " . ($errorBody ?: 'Unknown error'),
            ];
        }

        $data = $response->json();

        // JDoodle returns statusCode: 0 for success, non-zero for errors
        $statusCode = $data['statusCode'] ?? 0;
        $isSuccess = $statusCode === 0;

        return [
            'output' => $data['output'] ?? '',
            'success' => $isSuccess,
            'error' => $isSuccess ? null : ($data['error'] ?? "Execution failed with status code: {$statusCode}"),
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