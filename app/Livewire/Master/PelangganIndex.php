<?php

namespace App\Livewire\Master;

use Livewire\Component;
use App\Models\Pelanggan;

class PelangganIndex extends Component
{
    public $keyword = '';
    public $nama, $telepon, $alamat;
    public $edit_id = null;
    public $form_open = false;

    public function simpan()
    {
        $this->validate([
            'nama' => 'required|string|max:255|unique:pelanggan,nama',
            'telepon' => 'nullable|string|max:50',
            'alamat' => 'nullable|string',
        ],[
            'nama.unique' => 'Nama pelanggan sudah terdaftar.',
            'nama.required' => 'Nama pelanggan wajib diisi.',
            'nama.max' => 'Nama pelanggan maksimal 255 karakter.',
            'telepon.max' => 'No. telepon maksimal 50 karakter.',
            'alamat.max' => 'Alamat maksimal 255 karakter.',
        ]);

        if ($this->edit_id) {
            Pelanggan::find($this->edit_id)->update([
                'nama' => $this->nama,
                'telepon' => $this->telepon,
                'alamat' => $this->alamat,
            ]);
            session()->flash('sukses', 'Data pelanggan berhasil diubah.');
        } else {
            Pelanggan::create([
                'nama' => $this->nama,
                'telepon' => $this->telepon,
                'alamat' => $this->alamat,
            ]);
            session()->flash('sukses', 'Pelanggan baru berhasil ditambahkan.');
        }

        $this->resetForm();
    }

    public function edit($id)
    {
        $plg = Pelanggan::find($id);
        $this->edit_id = $plg->id_pelanggan;
        $this->nama = $plg->nama;
        $this->telepon = $plg->telepon;
        $this->alamat = $plg->alamat;
        $this->form_open = true;
    }

    public function toggleAktif($id)
    {
        // ATURAN PRD: Soft Delete dengan mengubah status aktif (Bukan Delete Permanen)
        $plg = Pelanggan::find($id);
        $plg->update(['aktif' => !$plg->aktif]);
    }

    public function resetForm()
    {
        $this->reset(['nama', 'telepon', 'alamat', 'edit_id']);
        $this->form_open = false;
    }

    public function render()
    {
        $data = Pelanggan::where('nama', 'like', "%{$this->keyword}%")
            ->orderBy('aktif', 'desc')
            ->latest()
            ->get();

        return view('livewire.master.pelanggan-index', ['daftarPelanggan' => $data]);
    }
}