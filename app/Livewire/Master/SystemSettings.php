<?php

namespace App\Livewire\Master;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Setting;

class SystemSettings extends Component
{
    use WithFileUploads;

    public $pos_background_image;
    public $pos_background_preview;

    public function mount()
    {
        $this->pos_background_preview = Setting::getValue('pos_background_image');
    }

    public function updatedPosBackgroundImage()
    {
        $this->validate([
            'pos_background_image' => 'image|max:10240', // Max 10MB
        ]);
    }

    public function simpanBackground()
    {
        if ($this->pos_background_image) {
            $this->validate([
                'pos_background_image' => 'image|max:10240',
            ]);

            $path = $this->pos_background_image->store('pos-backgrounds', 'public');
            Setting::setValue('pos_background_image', '/storage/' . $path);
            $this->pos_background_preview = '/storage/' . $path;
            $this->pos_background_image = null;

            session()->flash('sukses', 'Background POS berhasil diperbarui!');
        }
    }

    public function hapusBackground()
    {
        Setting::setValue('pos_background_image', null);
        $this->pos_background_preview = null;
        $this->pos_background_image = null;

        session()->flash('sukses', 'Background POS dikembalikan ke default.');
    }

    public function render()
    {
        return view('livewire.master.system-settings');
    }
}
