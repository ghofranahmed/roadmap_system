<?php

namespace App\Services\Compiler;

interface CompilerServiceInterface
{
    /**
     * Execute code and return the result.
     * @param string $code The code to execute
     * @param string $language The programming language
     * @param string $stdin Standard input for the program
     * @return array ['output' => string, 'success' => bool, 'error' => string|null]
     */
    public function execute(string $code, string $language, string $stdin = ''): array;
}