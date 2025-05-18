<?php
if (!function_exists('send_response')) {
    function send_response(bool $success, string $message, array $data = [], int $statusCode = 200)
    {
        $request = \Config\Services::request();
        $response = \Config\Services::response();

        if ($request->isAJAX() || is_api_request()) {
            return $response->setJSON([
                'success' => $success,
                'message' => $message,
                'data' => $data
            ])->setStatusCode($success ? $statusCode : ($statusCode >= 400 ? $statusCode : 400));
        }

        if ($success) {
            session()->setFlashdata('success', $message);
        } else {
            session()->setFlashdata('error', $message);
        }

        return $data['redirect'] ?? redirect()->back();
    }
}

if (!function_exists('is_api_request')) {
    function is_api_request(): bool
    {
        $request = \Config\Services::request();
        return $request->hasHeader('X-API-KEY') || strpos($request->getPath(), 'api') === 0;
    }
}