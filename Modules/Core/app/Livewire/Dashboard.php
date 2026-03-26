<?php

namespace Modules\Core\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('core::components.layouts.master')]
class Dashboard extends Component
{
    public function render()
    {
        return view('core::livewire.dashboard');
    }
}