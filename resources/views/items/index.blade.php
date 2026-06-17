@extends('layouts.app')

@section('content')
<section class="flex flex-col gap-sm">
    <h2 class="font-headline-md text-on-surface">Master Items (Data Barang)</h2>
    <p class="font-body-sm text-on-surface-variant">Kelola daftar barang, kategori, dan batas minimum stok gudang.</p>
</section>

{{-- TABS KATEGORI + TOMBOL SEJAJAR --}}
<section class="flex flex-col md:flex-row justify-between items-start md:items-center gap-md glass-panel p-md rounded-xl">
    {{-- Kiri: Tabs Kategori --}}
    <div class="flex flex-wrap gap-sm">
        <a href="{{ url('/items') }}"
           class="px-md py-2 text-body-sm font-label-bold rounded-lg border transition-colors
           {{ !request('kategori') && !request('status') ? 'bg-primary-container text-on-primary-container border-primary/20' : 'text-on-surface-variant hover:bg-surface-container-high border-transparent' }}">
            Semua ({{ $totalBarang }})
        </a>
        @foreach($kategoris as $kategori)
            <div class="relative flex items-center group">
                <a href="{{ route('items.index', array_merge(request()->query(), ['kategori' => $kategori->id, 'page' => 1])) }}"
                    class="px-md py-2 text-body-sm font-label-bold rounded-lg border transition-all flex items-center gap-3
                    {{ request('kategori') == $kategori->id 
                        ? 'bg-primary text-on-primary border-primary shadow-md'
                        : 'bg-surface-container-high text-on-surface-variant border-outline-variant/30 hover:border-primary/50' }}">
                    <span>{{ strtoupper($kategori->nama) }}</span>
                    <form action="{{ route('kategori.destroy', $kategori->id) }}" method="POST" 
                        onsubmit="return confirm('Yakin ingin menghapus kategori ini?')" 
                        class="inline-flex items-center">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="hover:bg-error/20 hover:text-error rounded-full p-0.5 transition-colors flex items-center justify-center">
                            <span class="material-symbols-outlined text-[14px]">close</span>
                        </button>
                    </form>
                </a>
            </div>
        @endforeach
    </div>
    
    {{-- Kanan: Tombol Filter, Tambah Barang, Supplier (SEJAJAR) --}}
    <div class="flex items-center gap-sm w-full md:w-auto">
        {{-- Tombol Filter --}}
        <button onclick="toggleFilterPanel()"
                id="btnFilter"
                class="flex items-center justify-center gap-xs px-md py-2 text-body-sm font-label-bold rounded-lg transition-colors border
                {{ request()->hasAny(['search', 'status', 'sort']) && (request('sort', 'terbaru') !== 'terbaru' || request('search') || request('status'))
                    ? 'bg-primary/10 text-primary border-primary/30'
                    : 'text-on-surface bg-surface-container-highest border-outline-variant/30 hover:bg-surface-bright' }}">
            <span class="material-symbols-outlined text-[18px]">filter_list</span>
            Filter
            @if(request()->hasAny(['search', 'status']) || (request('sort') && request('sort') !== 'terbaru'))
                <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
            @endif
        </button>
        
        {{-- Tombol Tambah Barang --}}
        <button onclick="document.getElementById('modalAddItem').classList.remove('hidden')"
                class="flex items-center justify-center gap-xs px-md py-2 text-body-sm font-label-bold text-on-primary bg-primary rounded-lg hover:brightness-110 active:scale-95 transition-all shadow-[0_0_15px_rgba(207,188,255,0.2)]">
            <span class="material-symbols-outlined text-[18px]">add</span>
            Tambah Barang
        </button>
        
        {{-- Tombol Supplier (Pindah ke halaman supplier) --}}
        <a href="{{ route('supplier.index') }}"
           class="flex items-center justify-center gap-xs px-md py-2 text-body-sm font-label-bold text-on-primary bg-primary rounded-lg hover:brightness-110 active:scale-95 transition-all shadow-[0_0_15px_rgba(207,188,255,0.2)]">
            <span class="material-symbols-outlined text-[18px]">business</span>
            Supplier
        </a>
    </div>
</section>

{{-- FILTER PANEL --}}
<section id="filterPanel" class="hidden glass-panel p-lg rounded-xl border border-outline-variant/20 animate-fade-in-up mt-md">
    <form method="GET" action="{{ url('/items') }}" id="filterForm">
        @if(request('kategori'))
            <input type="hidden" name="kategori" value="{{ request('kategori') }}">
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-md">
            <div class="flex flex-col gap-1">
                <label class="text-[11px] font-label-bold text-on-surface-variant uppercase tracking-wider ml-1">
                    <span class="material-symbols-outlined text-[14px] align-middle mr-0.5">search</span> Cari Barang
                </label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Nama barang atau SKU..."
                       class="w-full bg-surface-container-lowest border border-outline-variant/50 rounded-xl py-2.5 px-4 text-on-surface text-sm focus:border-primary focus:ring-1 focus:ring-primary/50 outline-none transition-all">
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-[11px] font-label-bold text-on-surface-variant uppercase tracking-wider ml-1">
                    <span class="material-symbols-outlined text-[14px] align-middle mr-0.5">inventory_2</span> Status Stok
                </label>
                <select name="status" onchange="this.form.submit()" class="w-full bg-surface-container-lowest border border-outline-variant/50 rounded-xl py-2.5 px-4 text-on-surface text-sm focus:border-primary focus:ring-1 focus:ring-primary/50 outline-none transition-all appearance-none">
                    <option value="">Semua Status</option>
                    <option value="aman" {{ request('status') == 'aman' ? 'selected' : '' }}>✅ Aman ({{ $countAman }})</option>
                    <option value="kritis" {{ request('status') == 'kritis' ? 'selected' : '' }}>⚠️ Kritis ({{ $countKritis }})</option>
                    <option value="kosong" {{ request('status') == 'kosong' ? 'selected' : '' }}>❌ Habis ({{ $countKosong }})</option>
                </select>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-[11px] font-label-bold text-on-surface-variant uppercase tracking-wider ml-1">
                    <span class="material-symbols-outlined text-[14px] align-middle mr-0.5">sort</span> Urutkan
                </label>
                <select name="sort" class="w-full bg-surface-container-lowest border border-outline-variant/50 rounded-xl py-2.5 px-4 text-on-surface text-sm focus:border-primary focus:ring-1 focus:ring-primary/50 outline-none transition-all appearance-none">
                    <option value="terbaru" {{ request('sort', 'terbaru') == 'terbaru' ? 'selected' : '' }}>Terbaru</option>
                    <option value="terlama" {{ request('sort') == 'terlama' ? 'selected' : '' }}>Terlama</option>
                    <option value="nama_asc" {{ request('sort') == 'nama_asc' ? 'selected' : '' }}>Nama A → Z</option>
                    <option value="nama_desc" {{ request('sort') == 'nama_desc' ? 'selected' : '' }}>Nama Z → A</option>
                    <option value="stok_asc" {{ request('sort') == 'stok_asc' ? 'selected' : '' }}>Stok Terendah</option>
                    <option value="stok_desc" {{ request('sort') == 'stok_desc' ? 'selected' : '' }}>Stok Tertinggi</option>
                </select>
            </div>
        </div>

        <div class="flex items-center justify-between mt-md pt-md border-t border-outline-variant/20">
            <div class="flex flex-wrap items-center gap-2">
                @if(request('search'))
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-primary/10 text-primary text-[11px] font-label-bold">
                        <span class="material-symbols-outlined text-[14px]">search</span> "{{ request('search') }}"
                        <a href="{{ url('/items') . '?' . http_build_query(request()->except(['search', 'page'])) }}" class="hover:text-error transition-colors ml-0.5">✕</a>
                    </span>
                @endif
                @if(request('status'))
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-primary/10 text-primary text-[11px] font-label-bold">
                        <span class="material-symbols-outlined text-[14px]">inventory_2</span> {{ ucfirst(request('status')) }}
                        <a href="{{ url('/items') . '?' . http_build_query(request()->except(['status', 'page'])) }}" class="hover:text-error transition-colors ml-0.5">✕</a>
                    </span>
                @endif
                @if(request('sort') && request('sort') !== 'terbaru')
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-primary/10 text-primary text-[11px] font-label-bold">
                        <span class="material-symbols-outlined text-[14px]">sort</span> {{ request('sort') }}
                        <a href="{{ url('/items') . '?' . http_build_query(request()->except(['sort', 'page'])) }}" class="hover:text-error transition-colors ml-0.5">✕</a>
                    </span>
                @endif
            </div>

            <div class="flex items-center gap-2">
                @if(request()->hasAny(['search', 'status', 'kategori']) || (request('sort') && request('sort') !== 'terbaru'))
                    <a href="{{ url('/items') }}" class="px-md py-2 rounded-xl text-on-surface-variant font-label-bold text-sm hover:bg-error/10 hover:text-error transition-colors flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">restart_alt</span> Reset
                    </a>
                @endif
                <button type="submit" class="px-lg py-2 rounded-xl bg-primary text-on-primary font-label-bold text-sm hover:brightness-110 active:scale-95 transition-all shadow-[0_0_12px_rgba(207,188,255,0.2)] flex items-center gap-1">
                    <span class="material-symbols-outlined text-[16px]">check</span> Terapkan
                </button>
            </div>
        </div>
    </form>
</section>

{{-- TABEL BARANG --}}
<section class="glass-panel rounded-xl overflow-hidden flex flex-col mt-lg">
    @if(request()->hasAny(['search', 'status', 'kategori']) || (request('sort') && request('sort') !== 'terbaru'))
    <div class="px-lg py-2.5 bg-primary/5 border-b border-primary/10 flex items-center justify-between">
        <span class="text-[12px] text-on-surface-variant font-label-bold">
            <span class="material-symbols-outlined text-[14px] align-middle mr-0.5">filter_list</span>
            Menampilkan {{ $barangs->total() }} hasil
            @if(request('search')) untuk "<span class="text-primary">{{ request('search') }}</span>" @endif
        </span>
        <a href="{{ url('/items') }}" class="text-[11px] text-primary hover:underline font-label-bold">Hapus semua filter</a>
    </div>
    @endif

    <div class="overflow-x-auto custom-scrollbar">
        <table class="w-full text-left border-collapse min-w-[800px]">
            <thead>
                <tr class="bg-surface-container-high border-b border-outline-variant/30">
                    <th class="px-lg py-md font-label-bold text-on-surface-variant text-center w-[60px]">NO</th>
                    <th class="px-lg py-md font-label-bold text-on-surface-variant">SKU & NAMA BARANG</th>
                    <th class="px-lg py-md font-label-bold text-on-surface-variant">KATEGORI</th>
                    <th class="px-lg py-md font-label-bold text-on-surface-variant">STOK</th>
                    <th class="px-lg py-md font-label-bold text-on-surface-variant">STATUS</th>
                    <th class="px-lg py-md font-label-bold text-on-surface-variant text-center w-[80px]">AKSI</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/10">
                @forelse($barangs as $item)
                <tr class="hover:bg-surface-container/50 transition-colors group">
                    <td class="px-lg py-md text-center align-middle">
                        <div class="font-body-md text-on-surface font-medium">
                            {{ $loop->iteration + (($barangs->currentPage() - 1) * $barangs->perPage()) }}
                        </div>
                    </td>
                    <td class="px-lg py-md align-middle">
                        <div class="font-body-md text-on-surface font-medium group-hover:text-primary transition-colors">{{ $item->nama_barang }}</div>
                        <div class="font-label-muted text-on-surface-variant mt-0.5">SKU: {{ $item->kode_barang }}</div>
                    </td>
                    <td class="px-lg py-md font-body-sm text-on-surface-variant align-middle">{{ $item->kategori->nama ?? '-' }}</td>
                    <td class="px-lg py-md align-middle">
                        @if($item->stok <= 0)
                            <div class="font-body-md text-error font-bold">Habis</div>
                        @elseif($item->stok <= $item->stok_minimum)
                            <div class="font-body-md text-tertiary font-bold">{{ $item->stok }} <span class="text-on-surface-variant text-[12px] font-normal">{{ $item->satuan }}</span></div>
                        @else
                            <div class="font-body-md text-on-surface">{{ $item->stok }} <span class="text-on-surface-variant text-[12px] font-normal">{{ $item->satuan }}</span></div>
                        @endif
                        <div class="font-label-muted text-on-surface-variant mt-0.5">Min: {{ $item->stok_minimum }}</div>
                    </td>
                    <td class="px-lg py-md align-middle">
                        @if($item->stok <= 0)
                            <span class="px-2 py-1 rounded-sm bg-error-container/20 text-error font-label-bold text-[10px] border border-error/20">Kosong</span>
                        @elseif($item->stok <= $item->stok_minimum)
                            <span class="px-2 py-1 rounded-sm bg-tertiary-container/20 text-tertiary font-label-bold text-[10px] border border-tertiary/20">Kritis</span>
                        @else
                            <span class="px-2 py-1 rounded-sm bg-green-500/10 text-green-400 font-label-bold text-[10px] border border-green-500/20">Aman</span>
                        @endif
                    </td>
                    <td class="px-lg py-md text-center align-middle">
                        <div class="flex justify-center gap-1">
                            <button type="button" onclick="openEditModal({{ $item->load('kategori') }})" class="p-1.5 text-on-surface-variant hover:text-primary transition-colors">
                                <span class="material-symbols-outlined text-[20px]">edit</span>
                            </button>
                            <form action="/items/{{ $item->id }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus barang ini?');" class="inline">
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
                    <td colspan="6" class="px-lg py-xl text-center">
                        <div class="flex flex-col items-center gap-2 py-6">
                            <span class="material-symbols-outlined text-[48px] text-outline/40">search_off</span>
                            <p class="text-on-surface-variant font-body-sm">
                                {{ request()->hasAny(['search', 'status', 'kategori']) ? 'Tidak ada barang yang cocok dengan filter.' : 'Belum ada data barang.' }}
                            </p>
                            @if(request()->hasAny(['search', 'status', 'kategori']))
                                <a href="{{ url('/items') }}" class="text-primary text-sm font-label-bold hover:underline mt-1">Reset Filter</a>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="p-md border-t border-outline-variant/20 bg-surface-container-low/50">
        {{ $barangs->links('pagination::tailwind') }}
    </div>
</section>

{{-- MODAL TAMBAH BARANG --}}
<div id="modalAddItem" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="document.getElementById('modalAddItem').classList.add('hidden')"></div>
    <div class="glass-panel p-lg rounded-2xl w-full max-w-lg relative z-10 animate-fade-in-up border border-outline-variant/30">
        <div class="flex justify-between items-center mb-md border-b border-outline-variant/20 pb-3">
            <h3 class="font-headline-sm text-on-surface font-bold">Tambah Barang Baru</h3>
            <button onclick="document.getElementById('modalAddItem').classList.add('hidden')" class="text-on-surface-variant hover:text-error transition-colors"><span class="material-symbols-outlined">close</span></button>
        </div>
        <form action="{{ url('/items') }}" method="POST" class="flex flex-col gap-md">
            @csrf
            <div>
                <label class="text-[11px] font-label-bold text-on-surface-variant uppercase tracking-wider ml-1">Kode / SKU</label>
                <input type="text" name="kode_barang" value="{{ $nextKodeBarang }}" readonly class="w-full bg-surface-container-low border border-outline-variant/30 rounded-xl py-3 px-4 text-primary font-bold cursor-not-allowed outline-none mt-1 select-none tracking-wider">
            </div>
            <div>
                <label class="text-[11px] font-label-bold text-on-surface-variant uppercase tracking-wider ml-1">Nama Barang</label>
                <input type="text" name="nama_barang" required class="w-full bg-surface-container-lowest border border-outline-variant/50 rounded-xl py-3 px-4 text-on-surface focus:border-primary focus:ring-1 focus:ring-primary/50 outline-none transition-all mt-1" placeholder="Masukkan nama barang">
            </div>
            <div class="grid grid-cols-2 gap-md">
                <div>
                    <label class="text-[11px] font-label-bold text-on-surface-variant uppercase tracking-wider ml-1">Kategori</label>
                    <input type="text" name="kategori_input" required class="w-full bg-surface-container-lowest border border-outline-variant/50 rounded-xl py-3 px-4 text-on-surface focus:border-primary focus:ring-1 focus:ring-primary/50 outline-none transition-all mt-1" placeholder="Contoh: Elektronik, Pakaian">
                </div>
                <div>
                    <label class="text-[11px] font-label-bold text-on-surface-variant uppercase tracking-wider ml-1">Satuan</label>
                    <input type="text" name="satuan" required class="w-full bg-surface-container-lowest border border-outline-variant/50 rounded-xl py-3 px-4 text-on-surface focus:border-primary focus:ring-1 focus:ring-primary/50 outline-none transition-all mt-1" placeholder="Pcs, Kg, Box">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-md">
                <div>
                    <label class="text-[11px] font-label-bold text-on-surface-variant uppercase tracking-wider ml-1">Stok Awal</label>
                    <input type="number" name="stok" required value="0" class="w-full bg-surface-container-lowest border border-outline-variant/50 rounded-xl py-3 px-4 text-on-surface focus:border-primary focus:ring-1 focus:ring-primary/50 outline-none transition-all mt-1">
                </div>
                <div>
                    <label class="text-[11px] font-label-bold text-on-surface-variant uppercase tracking-wider ml-1">Min. Stok (Alert)</label>
                    <input type="number" name="stok_minimum" required value="5" class="w-full bg-surface-container-lowest border border-outline-variant/50 rounded-xl py-3 px-4 text-on-surface focus:border-primary focus:ring-1 focus:ring-primary/50 outline-none transition-all mt-1">
                </div>
            </div>
            <div class="mt-sm pt-sm flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('modalAddItem').classList.add('hidden')" class="px-lg py-2.5 rounded-xl text-on-surface-variant font-label-bold hover:bg-surface-container-high transition-colors">Batal</button>
                <button type="submit" class="px-lg py-2.5 rounded-xl bg-primary text-on-primary font-bold hover:brightness-110 active:scale-95 transition-all shadow-[0_0_15px_rgba(207,188,255,0.3)]">Simpan Barang</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT BARANG --}}
<div id="modalEditItem" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeEditModal()"></div>
    <div class="glass-panel p-lg rounded-2xl w-full max-w-lg relative z-10 border border-outline-variant/30">
        <div class="flex justify-between items-center mb-md border-b border-outline-variant/20 pb-3">
            <h3 class="font-headline-sm text-on-surface font-bold">Edit Barang</h3>
            <button onclick="closeEditModal()" class="text-on-surface-variant hover:text-error transition-colors"><span class="material-symbols-outlined">close</span></button>
        </div>
        <form id="editForm" method="POST" class="flex flex-col gap-md">
            @csrf
            @method('PUT')
            
            <div>
                <label class="text-[11px] font-label-bold text-on-surface-variant uppercase ml-1">Nama Barang</label>
                <input type="text" name="nama_barang" id="edit_nama" required class="w-full bg-surface-container-lowest border border-outline-variant/50 rounded-xl py-3 px-4 text-on-surface outline-none mt-1">
            </div>

            <div class="grid grid-cols-2 gap-md">
                <div>
                    <label class="text-[11px] font-label-bold text-on-surface-variant uppercase ml-1">Kategori</label>
                    <input type="text" name="kategori_input" id="edit_kategori_nama" required class="w-full bg-surface-container-lowest border border-outline-variant/50 rounded-xl py-3 px-4 text-on-surface focus:border-primary focus:ring-1 focus:ring-primary/50 outline-none transition-all mt-1" placeholder="Contoh: Elektronik, Pakaian">
                </div>
                <div>
                    <label class="text-[11px] font-label-bold text-on-surface-variant uppercase ml-1">Satuan</label>
                    <input type="text" name="satuan" id="edit_satuan" required class="w-full bg-surface-container-lowest border border-outline-variant/50 rounded-xl py-3 px-4 text-on-surface mt-1">
                </div>
            </div>

            <div>
                <label class="text-[11px] font-label-bold text-on-surface-variant uppercase ml-1">Min. Stok (Alert)</label>
                <input type="number" name="stok_minimum" id="edit_stok_min" required class="w-full bg-surface-container-lowest border border-outline-variant/50 rounded-xl py-3 px-4 text-on-surface mt-1">
            </div>

            <div class="mt-sm pt-sm flex justify-end gap-3">
                <button type="button" onclick="closeEditModal()" class="px-lg py-2.5 rounded-xl text-on-surface-variant font-label-bold">Batal</button>
                <button type="submit" class="px-lg py-2.5 rounded-xl bg-primary text-on-primary font-bold shadow-[0_0_15px_rgba(207,188,255,0.3)]">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function toggleFilterPanel() {
        const panel = document.getElementById('filterPanel');
        panel.classList.toggle('hidden');
    }

    function openEditModal(barang) {
        const form = document.getElementById('editForm');
        form.action = `/items/${barang.id}`;
        document.getElementById('edit_nama').value = barang.nama_barang;
        document.getElementById('edit_satuan').value = barang.satuan;
        document.getElementById('edit_stok_min').value = barang.stok_minimum;
        document.getElementById('edit_kategori_nama').value = barang.kategori ? barang.kategori.nama : '';
        document.getElementById('modalEditItem').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('modalEditItem').classList.add('hidden');
    }

    document.addEventListener('DOMContentLoaded', function() {
        const hasActiveFilter = {{ (request()->hasAny(['search', 'status']) || (request('sort') && request('sort') !== 'terbaru')) ? 'true' : 'false' }};
        if (hasActiveFilter) {
            document.getElementById('filterPanel').classList.remove('hidden');
        }
    });
</script>
@endpush
@endsection