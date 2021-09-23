<?php

namespace Twoavy\EvaluationTool\Helpers;

use App\Models\User;

class TestHelper
{
    public static function getAuthHeader($userId = 1): array
    {
        $user = User::find($userId);
        $headers = ['Accept' => 'application/json'];

        if (!is_null($user)) {
            $token                    = $user->createToken('php test token')->accessToken;
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        return $headers;
    }


}
