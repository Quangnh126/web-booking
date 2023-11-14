<?php


namespace App\Services\Admin;


use App\Models\Setting;

class SettingV2Service
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

    public function updateOrCreate(array $request)
    {
        return $this->setting->updateOrCreate([
            'key' => Setting::$contact_us
        ],[
            'value' => json_encode($request)
        ]);
    }

}
