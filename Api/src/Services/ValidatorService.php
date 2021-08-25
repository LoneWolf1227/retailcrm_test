<?php

namespace App\Services;

class ValidatorService
{
    public function validateCreateOrderData($data): bool
    {
        $fields = [
            "externalId","productName","article","orderType",
            "status","site", "orderMethod","number","lastName",
            "firstName","patronymic","customerComment", "brand","prim"
        ];

        $fieldsCount = count($fields);
        $count = 0;

        foreach ($data as $key => $value) {
            if (in_array($key, $fields)) {
                if (!empty($value)) {
                    $count++;
                }
            }
        }

        if ($count === $fieldsCount) {
            return true;
        }

        return false;
    }
}
