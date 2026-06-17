@extends('layouts.app')

@section('content')
<section class="flex flex-col gap-sm">
    <h2 class="font-headline-md text-on-surface">Master Supplier</h2>
    <p class="font-body-sm text-on-surface-variant">Kelola data supplier, kontak, dan alamat.</p>
</section>

<section class="flex flex-col md:flex-row justify-between items-start md:items-center gap-md glass-panel p-md rounded-xl">
    <div class="flex flex-wrap gap-sm">
        <span class="px-md py-2 text-body-sm font-label-bold rounded-lg bg-primary-container text-on-primary-container border-primary/20">
            Semua Supplier ({{ $suppliers->total() }})
        </span>
    </div>
    
    <div class="flex items-center gap-sm w-full md:w-auto">
        <button onclick="document.getElementById('modalAddSupplier').classList.remove('hidden')"
                class="flex-1 md:flex-none flex items-center justify-center gap-xs px-md py-2 text-body-sm font-label-bold text-on-primary bg-primary rounded-lg hover:brightness-110 active:scale-95 transition-all shadow-[0_0_15px_rgba(207,188,255,0.2)]">
            <span class="material-symbols-outlined text-[18px]">add</span> Tambah Supplier
        </button>
    </div>
</section>

<section class="glass-panel rounded-xl overflow-hidden flex flex-col mt-lg">
    <div class="overflow-x-auto custom-scrollbar">
        <table class="w-full text-left border-collapse min-w-[600px]">
            <thead>
                <tr class="bg-surface-container-high border-b border-outline-variant/30">
                    <th class="px-lg py-md font-label-bold text-on-surface-variant text-center w-[60px]">NO</th>
                    <th class="px-lg py-md font-label-bold text-on-surface-variant">NAMA SUPPLIER</th>
                    <th class="px-lg py-md font-label-bold text-on-surface-variant">KONTAK</th>
                    <th class="px-lg py-md font-label-bold text-on-surface-variant">ALAMAT</th>
                    <th class="px-lg py-md font-label-bold text-on-surface-variant text-center w-[100px]">AKSI</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/10">
                @forelse($suppliers as $index => $supplier)
                <tr class="hover:bg-surface-container/50 transition-colors">
                    <td class="px-lg py-md text-center align-middle">
                        {{ $index + 1 + (($suppliers->currentPage() - 1) * $suppliers->perPage()) }}
                    </td>
                    <td class="px-lg py-md align-middle font-body-md text-on-surface">{{ $supplier->nama_supplier }}</td>
                    <td class="px-lg py-md align-middle text-on-surface-variant">{{ $supplier->kontak ?? '-' }}</td>
                    <td class="px-lg py-md align-middle text-on-surface-variant">{{ $supplier->alamat ?? '-' }}</td>
                    <td class="px-lg py-md text-center align-middle">
                        <div class="flex justify-center gap-1">
                            <button onclick="openEditModal({{ $supplier }})" class="p-1.5 text-on-surface-variant hover:text-primary transition-colors">
                                <span class="material-symbols-outlined text-[20px]">edit</span>
                            </button>
                            <form action="{{ route('supplier.destroy', $supplier->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus supplier ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-on-surface-variant hover:text-error transition-colors">
                                    <span class="material-symbols-outlined text-[20px]">delete</span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-lg py-xl text-center">
                        <div class="flex flex-col items-center gap-2 py-6">
                            <span class="material-symbols-outlined text-[48px] text-outline/40">business</span>
                            <p class="text-on-surface-variant">Belum ada data supplier.</p>
                            <button onclick="document.getElementById('modalAddSupplier').classList.remove('hidden')" class="mt-2 px-4 py-2 bg-primary text-on-primary rounded-lg text-sm">Tambah Supplier</button>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="p-md border-t border-outline-variant/20 bg-surface-container-low/50">
        {{ $suppliers->links('pagination::tailwind') }}
    </div>
</section>

{{-- MODAL TAMBAH SUPPLIER --}}
<div id="modalAddSupplier" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeAddModal()"></div>
    <div class="glass-panel p-lg rounded-2xl w-full max-w-md relative z-10 border border-outline-variant/30">
        <div class="flex justify-between items-center mb-md border-b border-outline-variant/20 pb-3">
            <h3 class="font-headline-sm text-on-surface font-bold">Tambah Supplier</h3>
            <button onclick="closeAddModal()" class="text-on-surface-variant hover:text-error"><span class="material-symbols-outlined">close</span></button>
        </div>
        <form action="{{ route('supplier.store') }}" method="POST" class="flex flex-col gap-md">
            @csrf
            <div>
                <label class="text-[11px] font-label-bold text-on-surface-variant uppercase ml-1">Nama Supplier</label>
                <input type="text" name="nama_supplier" required class="w-full bg-surface-container-lowest border border-outline-variant/50 rounded-xl py-3 px-4 text-on-surface mt-1">
            </div>
            <div>
                <label class="text-[11px] font-label-bold text-on-surface-variant uppercase ml-1">Kontak</label>
                <input type="text" name="kontak" class="w-full bg-surface-container-lowest border border-outline-variant/50 rounded-xl py-3 px-4 text-on-surface mt-1">
            </div>
            <div>
                <label class="text-[11px] font-label-bold text-on-surface-variant uppercase ml-1">Alamat</label>
                <textarea name="alamat" rows="2" class="w-full bg-surface-container-lowest border border-outline-variant/50 rounded-xl py-3 px-4 text-on-surface mt-1"></textarea>
            </div>
            <div class="mt-sm pt-sm flex justify-end gap-3">
                <button type="button" onclick="closeAddModal()" class="px-lg py-2.5 rounded-xl text-on-surface-variant font-label-bold">Batal</button>
                <button type="submit" class="px-lg py-2.5 rounded-xl bg-primary text-on-primary font-bold">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT SUPPLIER --}}
<div id="modalEditSupplier" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeEditModal()"></div>
    <div class="glass-panel p-lg rounded-2xl w-full max-w-md relative z-10 border border-outline-variant/30">
        <div class="flex justify-between items-center mb-md border-b border-outline-variant/20 pb-3">
            <h3 class="font-headline-sm text-on-surface font-bold">Edit Supplier</h3>
            <button onclick="closeEditModal()" class="text-on-surface-variant hover:text-error"><span class="material-symbols-outlined">close</span></button>
        </div>
        <form id="editForm" method="POST" class="flex flex-col gap-md">
            @csrf
            @method('PUT')
            <div>
                <label class="text-[11px] font-label-bold text-on-surface-variant uppercase ml-1">Nama Supplier</label>
                <input type="text" name="nama_supplier" id="edit_nama" required class="w-full bg-surface-container-lowest border border-outline-variant/50 rounded-xl py-3 px-4 text-on-surface mt-1">
            </div>
            <div>
                <label class="text-[11px] font-label-bold text-on-surface-variant uppercase ml-1">Kontak</label>
                <input type="text" name="kontak" id="edit_kontak" class="w-full bg-surface-container-lowest border border-outline-variant/50 rounded-xl py-3 px-4 text-on-surface mt-1">
            </div>
            <div>
                <label class="text-[11px] font-label-bold text-on-surface-variant uppercase ml-1">Alamat</label>
                <textarea name="alamat" id="edit_alamat" rows="2" class="w-full bg-surface-container-lowest border border-outline-variant/50 rounded-xl py-3 px-4 text-on-surface mt-1"></textarea>
            </div>
            <div class="mt-sm pt-sm flex justify-end gap-3">
                <button type="button" onclick="closeEditModal()" class="px-lg py-2.5 rounded-xl text-on-surface-variant font-label-bold">Batal</button>
                <button type="submit" class="px-lg py-2.5 rounded-xl bg-primary text-on-primary font-bold">Simpan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openEditModal(supplier) {
        const form = document.getElementById('editForm');
        form.action = `/supplier/${supplier.id}`;
        document.getElementById('edit_nama').value = supplier.nama_supplier;
        document.getElementById('edit_kontak').value = supplier.kontak || '';
        document.getElementById('edit_alamat').value = supplier.alamat || '';
        document.getElementById('modalEditSupplier').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('modalEditSupplier').classList.add('hidden');
    }

    function closeAddModal() {
        document.getElementById('modalAddSupplier').classList.add('hidden');
    }
</script>
@endpush
@endsection