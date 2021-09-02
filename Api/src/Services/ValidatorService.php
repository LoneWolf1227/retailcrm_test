<?php

namespace App\Services;

class ValidatorService
{
    public function validateRequiredFields($data, $requiredFields)
    {
        if (is_object($data))
            $data = (array) $data;

        $fieldsCount = count($requiredFields);
        $count = 0;
        $emptyFields = '';

        foreach ($data as $key => $value) {
            if (in_array($key, $requiredFields)) {
                if (!empty($value)) {
                    $count++;
                } else {
                    $emptyFields .= $key. ' ';
                }
                $reqKey = array_search($key, $requiredFields);
                unset($requiredFields[$reqKey]);
            }
        }

        if ($count === $fieldsCount) {
            return ['result' => true];
        }

        if (!empty($emptyFields)) {
            return ['result' => false, 'message' => 'Required fields \'' . $emptyFields . '\' empty'];
        }

        if (!empty($requiredFields)) {
            $requiredFields = implode(', ', $requiredFields );
            return ['result' => false, 'message' => 'Required fields \'' . $requiredFields . '\' not sent'];
        }

    }
}