<?php


if (! function_exists('getPackageStatus')) {
    function getPackageStatus()
    {
        $status = config('constants.PACKAGE_STATUS');
        $packageStatus = '';
        foreach ($status as $index => $item) {
            $packageStatus .= $index . '|' . $item;
            if ($index < count($status))
                $packageStatus .= ';';
        }
        return $packageStatus;
    }
}

