<?php

namespace App\Services\Compiler;

interface CompilerServiceInterface
{
    /**
     * تنفيذ الكود وإرجاع النتيجة.
     * @param string $code
     * @param string $language
     * @param array $testCases  // قد تحتاج لاستخدامها لاحقاً
     * @return array ['output' => string, 'success' => bool, 'error' => string|null]
     */
    public function execute(string $code, string $language, array $testCases = []): array;
}