<?php


namespace App\Services\Web;


use App\Models\Setting;

class SettingService
{
    private $setting;

    public function __construct(
        Setting $setting
    )
    {
        $this->setting = $setting;
    }

    public function getContact()
    {
        $contact = $this->setting->where('key', 'like', Setting::$contact_us)
            ->select('value')->first();

        return $contact;
    }

}
