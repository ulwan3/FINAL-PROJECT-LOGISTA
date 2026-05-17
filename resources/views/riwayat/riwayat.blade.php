@extends('layouts.app')

@section('content')
<section class="flex flex-col md:flex-row justify-between items-start md:items-center gap-md mb-lg">
    <div>
        <h2 class="font-headline-lg text-on-surface tracking-tight">Riwayat Aktivitas Pesanan</h2>
        <p class="font-body-md text-on-surface-variant mt-1">Daftar lengkap transaksi pemesanan barang ke supplier</p>
    </div>
    <div class="flex gap-sm">
        <a href="{{ url('/') }}" class="glass border border-outline-variant/30 text-on-surface px-md py-2 rounded-lg font-label-bold hover:bg-surface-container-high transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px]">arrow_back</span>
            Kembali
        </a>
    </div>
</section>

<div class="glass-panel rounded-2xl overflow-hidden border border-outline-variant/20">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-surface-container-low">
                <tr class="border-b border-outline-variant/30 text-on-surface-variant font-label-bold uppercase text-[11px] tracking-widest">
                    <th class="py-lg px-lg">Waktu & Tanggal</th>
                    <th class="py-lg px-lg">Nama Barang</th>
                    <th class="py-lg px-lg">Supplier</th>
                    <th class="py-lg px-lg text-center">Jumlah</th>
                    <th class="py-lg px-lg">Status Pesanan</th>
                    <th class="py-lg px-lg">Admin / Pemesan</th>
                    {{-- Tambah Header Baru --}}
                    <th class="py-lg px-lg text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-on-surface font-body-md">
                @forelse($semuaAktivitas as $item)
                <tr class="border-b border-outline-variant/10 hover:bg-white/5 transition-colors group">
                    <td class="py-md px-lg">
                        <div class="text-on-surface font-bold">{{ $item->created_at->isoFormat('D MMMM YYYY') }}</div>
                        <div class="text-[11px] text-on-surface-variant opacity-60">{{ $item->created_at->format('H:i') }} WIB</div>
                    </td>
                    <td class="py-md px-lg">
                        <span class="text-primary font-bold group-hover:text-primary-light transition-colors">
                            {{ $item->barang->nama_barang ?? 'Barang Terhapus' }}
                        </span>
                    </td>
                    <td class="py-md px-lg">
                        <span class="text-secondary font-medium">{{ $item->supplier->nama_supplier ?? 'Tanpa Supplier' }}</span>
                    </td>
                    <td class="py-md px-lg text-center font-mono font-bold">
                        {{ number_format($item->jumlah_pesan ?? 0) }}
                    </td>
                    <td class="py-md px-lg">
                        @php
                            $statusConfig = [
                                'pending' => 'border-yellow-500/50 text-yellow-400 bg-yellow-500/10',
                                'diproses' => 'border-blue-500/50 text-blue-400 bg-blue-500/10',
                                'sampai' => 'border-green-500/50 text-green-400 bg-green-500/10',
                                'tidak_sesuai' => 'border-error/50 text-error bg-error/10',
                            ][$item->status ?? 'pending'] ?? 'border-outline text-on-surface-variant bg-surface';
                        @endphp
                        <span class="px-3 py-1 rounded-md text-[10px] font-bold uppercase border {{ $statusConfig }}">
                            {{ str_replace('_', ' ', $item->status ?? 'pending') }}
                        </span>
                    </td>
                    <td class="py-md px-lg">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary/30 to-tertiary/30 flex items-center justify-center text-[12px] font-bold border border-outline-variant/20">
                                {{ strtoupper(substr($item->user->name ?? 'A', 0, 1)) }}
                            </div>
                            <span class="text-sm font-medium">{{ $item->user->name ?? 'Admin' }}</span>
                        </div>
                    </td>
                    {{-- Tambah Kolom Tombol Konfirmasi --}}
                    <td class="py-md px-lg text-center">
                        @if(in_array($item->status, ['pending', 'diproses']))
                            <button 
                                onclick="bukaModalKonfirmasi({{ json_encode([
                                    'id' => $item->id,
                                    'nama' => $item->barang->nama_barang ?? 'Barang',
                                    'qty' => $item->jumlah_pesan
                                ]) }})"
                                class="flex items-center gap-1 bg-primary/10 hover:bg-primary text-primary hover:text-on-primary border border-primary/20 px-3 py-1 rounded-lg transition-all text-[11px] font-bold uppercase mx-auto"
                            >
                                <span class="material-symbols-outlined text-[16px]">check_circle</span>
                                Konfirmasi
                            </button>
                        @else
                            <span class="text-on-surface-variant opacity-40 text-[10px] font-bold uppercase italic">Selesai</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-20 text-center text-on-surface-variant italic">Belum ada riwayat pemesanan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($semuaAktivitas->hasPages())
    <div class="p-lg bg-surface-container-low border-t border-outline-variant/30">
        {{ $semuaAktivitas->links() }}
    </div>
    @endif
</div>

{{-- MODAL KONFIRMASI --}}
<div id="modalKonfirmasi" class="fixed inset-0 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" onclick="tutupModal()"></div>
        <div class="glass-panel relative w-full max-w-md rounded-2xl border border-outline-variant/30 overflow-hidden shadow-2xl transition-all transform">
            <form id="formKonfirmasi" method="POST">
                @csrf
                <div class="p-lg bg-surface-container-high">
                    <h3 class="font-headline-sm text-on-surface">Konfirmasi Barang</h3>
                    <p id="infoBarang" class="text-on-surface-variant text-sm mt-1 mb-6"></p>

                    <div class="space-y-4 text-left">
                        <div>
                            <label class="font-label-medium text-on-surface mb-2 block">Status Penerimaan</label>
                            <select name="status" class="w-full bg-surface-container border border-outline rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-primary text-on-surface transition-all">
                                <option value="sampai">Sesuai & Masuk Gudang</option>
                                <option value="tidak_sesuai">Tidak Sesuai / Reject</option>
                            </select>
                        </div>
                        <div>
                            <label class="font-label-medium text-on-surface mb-2 block">Jumlah yang Diterima (Pcs)</label>
                            <input type="number" name="qty_diterima" id="inputQty" required class="w-full bg-surface-container border border-outline rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-primary text-on-surface transition-all">
                        </div>
                        <div>
                            <label class="font-label-medium text-on-surface mb-2 block">Catatan Tambahan</label>
                            <textarea name="catatan" rows="2" class="w-full bg-surface-container border border-outline rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-primary text-on-surface transition-all" placeholder="Opsional..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="bg-surface-container-low p-md flex justify-end gap-3">
                    <button type="button" onclick="tutupModal()" class="px-4 py-2 font-label-bold text-on-surface-variant hover:bg-white/5 rounded-lg transition-all">Batal</button>
                    <button type="submit" class="bg-primary text-on-primary px-6 py-2 rounded-xl font-label-bold shadow-lg shadow-primary/20 hover:scale-[1.02] transition-all">Update Stok</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function bukaModalKonfirmasi(data) {
    document.getElementById('formKonfirmasi').action = `/riwayat/konfirmasi/${data.id}`;
    document.getElementById('infoBarang').innerText = `Barang: ${data.nama} (Pesan: ${data.qty} pcs)`;
    document.getElementById('inputQty').value = data.qty;
    document.getElementById('modalKonfirmasi').classList.remove('hidden');
}

function tutupModal() {
    document.getElementById('modalKonfirmasi').classList.add('hidden');
}
</script>
@endsection