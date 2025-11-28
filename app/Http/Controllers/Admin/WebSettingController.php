<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WebSettingController extends Controller
{
    public function show()
    {
        $brokerIp = env('BROKER_IP');
        return view('admin.settings', compact('brokerIp'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'broker_ip' => 'nullable|ip',
        ]);

        $value = $request->input('broker_ip');
        $this->setEnvValue('BROKER_IP', $value);

        // Update runtime env too
        if ($value !== null) {
            putenv('BROKER_IP=' . $value);
            $_ENV['BROKER_IP'] = $value;
            $_SERVER['BROKER_IP'] = $value;
        } else {
            putenv('BROKER_IP');
            unset($_ENV['BROKER_IP']);
            unset($_SERVER['BROKER_IP']);
        }

        return redirect()->route('admin.settings.show')->with('success', 'Settings updated');
    }

    protected function setEnvValue(string $key, $value)
    {
        $envPath = base_path('.env');

        if (! file_exists($envPath)) {
            // create file
            file_put_contents($envPath, "");
        }

        $escaped = str_replace('\n', "\\n", $value);

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $found = false;
        foreach ($lines as &$line) {
            if (strpos($line, $key . '=') === 0) {
                if ($value === null || $value === '') {
                    $line = ""; // remove
                } else {
                    $line = $key . '=' . $escaped;
                }
                $found = true;
                break;
            }
        }

        if (! $found && ($value !== null && $value !== '')) {
            $lines[] = $key . '=' . $escaped;
        }

        // Filter empty lines
        $lines = array_filter($lines, function ($l) { return trim($l) !== ''; });

        file_put_contents($envPath, implode("\n", $lines) . "\n");
    }
}
