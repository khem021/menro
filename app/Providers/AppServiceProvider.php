<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Livewire 2 throws away flash data after any action that does not
     * redirect, and the toast area lives in the layout outside the component,
     * so "Entry deleted." was set but never shown. Hand the message to the page
     * as a "toast" browser event instead. A component that already prints the
     * message inline is left alone so it does not appear twice.
     */
    private function showFlashMessagesFromLivewireActions(): void
    {
        Livewire::listen('component.dehydrate', function ($component, $response) {
            if (!Livewire::isDefinitelyLivewireRequest() || !empty($component->redirectTo)) {
                return;
            }

            $html = (string) ($response->effects['html'] ?? '');

            foreach (['success', 'error'] as $type) {
                $message = session($type);

                if (!is_string($message) || $message === '' || ($html !== '' && str_contains($html, e($message)))) {
                    continue;
                }

                $response->effects['dispatches'][] = ['event' => 'toast', 'data' => ['type' => $type, 'message' => $message]];
            }
        });
    }

    public function register()
    {
        //
    }

    public function boot()
    {
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        $this->showFlashMessagesFromLivewireActions();
    }
}
