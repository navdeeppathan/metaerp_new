<?php

namespace App\Services;

class JsonDiffService
{
    private static array $ignoredKeys = [
        'signatureFile',
        'signature_file_name',
        'logoFile',
        'footerFile',
        'stampFile',
        'password',
        'passwordConfirmation',
        'remember_token',
        'updated_at',
        'created_at',
        'version'
    ];

    /**
     * Compute differences between old and new state.
     */
    public function compare(array|object $before, array|object $after): string
    {
        $arr1 = is_object($before) ? (array) $before : $before;
        $arr2 = is_object($after) ? (array) $after : $after;

        $differences = [];

        foreach ($arr2 as $key => $newVal) {
            if (in_array($key, self::$ignoredKeys, true)) {
                continue;
            }

            $oldVal = $arr1[$key] ?? null;

            if ($oldVal !== $newVal) {
                if (is_array($oldVal) && is_array($newVal)) {
                    $subDiff = $this->compare($oldVal, $newVal);
                    if (!empty($subDiff)) {
                        $differences[$key] = json_decode($subDiff, true);
                    }
                } else {
                    $differences[$key] = [
                        'from' => $oldVal,
                        'to' => $newVal,
                    ];
                }
            }
        }

        return !empty($differences) ? json_encode($differences, JSON_UNESCAPED_UNICODE) : '';
    }
}
