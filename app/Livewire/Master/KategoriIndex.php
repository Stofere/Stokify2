<?php

namespace App\Livewire\Master;

use Livewire\Component;
use App\Models\Kategori;
use App\Models\Atribut;

class KategoriIndex extends Component
{
    public $nama_kategori = '';
    public $lacak_rol = false;
    public $selectedAtribut = []; // Menyimpan ID atribut yang dicentang
    
    public $edit_id = null;
    public $form_open = false;

    public function simpan()
    {
        $this->validate([
            'nama_kategori' => 'required|string|max:255',
        ]);

        if ($this->edit_id) {
            $kat = Kategori::find($this->edit_id);
            $kat->update([
                'nama_kategori' => $this->nama_kategori,
                'lacak_rol' => $this->lacak_rol
            ]);
            
            // Sinkronisasi Pivot Table kategori_atribut
            $kat->atribut()->sync($this->selectedAtribut);
            
            session()->flash('sukses', 'Kategori berhasil diubah.');
        } else {
            $kat = Kategori::create([
                'nama_kategori' => $this->nama_kategori,
                'lacak_rol' => $this->lacak_rol
            ]);
            $kat->atribut()->sync($this->selectedAtribut);
            
            session()->flash('sukses', 'Kategori baru berhasil dibuat.');
        }

        $this->resetForm();
    }

    public function edit($id)
    {
        $kat = Kategori::with('atribut')->find($id);
        $this->edit_id = $kat->id_kategori;
        $this->nama_kategori = $kat->nama_kategori;
        $this->lacak_rol = (bool) $kat->lacak_rol;
        
        // Ambil ID atribut yang sudah berelasi ke dalam array untuk checkbox
        $this->selectedAtribut = $kat->atribut->pluck('id_atribut')->toArray();
        $this->form_open = true;
    }

    public function resetForm()
    {
        $this->reset(['nama_kategori', 'lacak_rol', 'selectedAtribut', 'edit_id']);
        $this->form_open = false;
    }

    public function render()
    {
        return view('livewire.master.kategori-index', [
            'daftarKategori' => Kategori::with('atribut')->latest()->get(),
            'daftarAtribut' => Atribut::orderBy('nama_atribut')->get() // Untuk Checkbox
        ]);
    }
}