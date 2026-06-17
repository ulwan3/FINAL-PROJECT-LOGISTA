@extends('layouts.app')

@section('title', 'Settings - Logista')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Settings</h1>
        <p class="text-gray-400 text-sm mt-1">Pengaturan sistem & akun pengguna</p>
    </div>

    <!-- Alert Messages dari Laravel -->
    @if(session('success'))
        <div class="mb-4 p-3 bg-green-500/20 border border-green-500/50 rounded-lg text-green-400 text-sm">
            {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="mb-4 p-3 bg-red-500/20 border border-red-500/50 rounded-lg text-red-400 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <!-- Container untuk AJAX Messages -->
    <div id="ajax-message-container" class="mb-4"></div>

    <!-- HANYA KEAMANAN & AKSES - TANPA TAB PROFIL -->
    
    <!-- Operator Dionic -->
    <div class="bg-[#1c1515] rounded-xl border border-[#4d3535]/40 p-6 mt-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold text-white">Operator</h2>
            <span class="text-xs text-gray-400 bg-[#2b2020] px-2 py-1 rounded-full">
                Total: <span id="total-operators">{{ $operators->count() }}</span>
            </span>
        </div>
        
        <div class="text-sm text-gray-400 mb-4 bg-[#2b2020]/50 p-2 rounded-lg">
            ℹ️ Status Online/Offline otomatis berdasarkan aktivitas terakhir operator di aplikasi.
        </div>
        
        @if($operators->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-[#4d3535]/40">
                            <th class="text-left py-3 px-3 text-gray-400 font-medium">NAMA</th>
                            <th class="text-left py-3 px-3 text-gray-400 font-medium">USERNAME</th>
                            <th class="text-left py-3 px-3 text-gray-400 font-medium">STATUS</th>
                            <th class="text-left py-3 px-3 text-gray-400 font-medium">AKSES</th>
                            <th class="text-left py-3 px-3 text-gray-400 font-medium">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($operators as $operator)
                        <tr id="operator-row-{{ $operator->id }}" class="border-b border-[#4d3535]/30 hover:bg-[#2b2020]/30 transition-colors">
                            <td class="py-3 px-3 text-white font-medium">{{ $operator->name }}</td>
                            <td class="py-3 px-3 text-gray-400">{{ $operator->username ?? '-' }}</td>
                            <td class="py-3 px-3 status-cell">
                                @if($operator->isOnline())
                                    <span class="inline-flex items-center gap-1.5">
                                        <span class="relative flex h-2 w-2">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                                        </span>
                                        <span class="text-green-400 text-xs">Online</span>
                                        @if($operator->last_seen_at)
                                            <span class="text-gray-500 text-xs">({{ \Carbon\Carbon::parse($operator->last_seen_at)->diffForHumans() }})</span>
                                        @endif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5">
                                        <span class="w-2 h-2 bg-gray-500 rounded-full"></span>
                                        <span class="text-gray-400 text-xs">Offline</span>
                                        @if($operator->last_seen_at)
                                            <span class="text-gray-500 text-xs">({{ \Carbon\Carbon::parse($operator->last_seen_at)->diffForHumans() }})</span>
                                        @endif
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3">
                                @if($operator->is_active)
                                    <span class="inline-flex items-center gap-1.5">
                                        <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                                        <span class="text-blue-400 text-xs">Akses Diizinkan</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5">
                                        <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                                        <span class="text-red-400 text-xs">Akses Diblokir</span>
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3 action-cell">
                                <div class="flex items-center gap-3">
                                    @if($operator->is_active)
                                        <button onclick="toggleOperatorActive({{ $operator->id }}, 'nonaktifkan')" 
                                            class="text-red-400 hover:text-red-300 text-xs font-medium transition-colors">
                                            blokir akses
                                        </button>
                                    @else
                                        <button onclick="toggleOperatorActive({{ $operator->id }}, 'aktifkan')" 
                                            class="text-blue-400 hover:text-blue-300 text-xs font-medium transition-colors">
                                            izinkan akses
                                        </button>
                                    @endif
                                    
                                    <button onclick="deleteOperator({{ $operator->id }}, '{{ $operator->name }}')" 
                                        class="text-red-400 hover:text-red-300 text-xs transition-colors">
                                        🗑️ hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="mt-6 pt-4 border-t border-[#4d3535]/30">
                <button onclick="deactivateAllOperators()" class="px-4 py-2 bg-red-500/20 hover:bg-red-500/30 text-red-400 rounded-lg text-sm transition-all duration-200 flex items-center gap-2">
                    <span class="text-sm">🔒</span>
                    Blokir akses semua operator
                </button>
            </div>
        @else
            <div class="text-center py-12">
                <span class="material-symbols-outlined text-5xl text-gray-500 mb-3">people</span>
                <p class="text-gray-400">Belum ada operator terdaftar.</p>
                <p class="text-gray-500 text-sm mt-1">Tambahkan user dengan role "operator" untuk mulai mengelola.</p>
            </div>
        @endif
    </div>
</div>

<script>
    // Tampilkan pesan di container
    function showAjaxMessage(message, type) {
        const container = document.getElementById('ajax-message-container');
        if (!container) return;
        
        container.innerHTML = '';
        
        const alertDiv = document.createElement('div');
        alertDiv.className = `p-3 rounded-lg text-sm ${
            type === 'success' 
                ? 'bg-green-500/20 border border-green-500/50 text-green-400' 
                : 'bg-red-500/20 border border-red-500/50 text-red-400'
        }`;
        alertDiv.innerHTML = message;
        
        container.appendChild(alertDiv);
        
        setTimeout(() => {
            alertDiv.style.opacity = '0';
            setTimeout(() => {
                if (alertDiv.parentNode) alertDiv.remove();
            }, 300);
        }, 3000);
    }
    
    // Toggle operator active status
    function toggleOperatorActive(operatorId, action) {
        if (!confirm('Yakin ingin ' + action + ' akses operator ini?')) return;
        
        fetch('/settings/operator/' + operatorId + '/toggle-active', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const row = document.getElementById('operator-row-' + operatorId);
                const aksesCell = row.querySelector('td:nth-child(4)');
                const actionCell = row.querySelector('.action-cell');
                const operatorName = row.querySelector('td:first-child')?.innerText || 'Operator';
                
                if (data.is_active) {
                    aksesCell.innerHTML = `
                        <span class="inline-flex items-center gap-1.5">
                            <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                            <span class="text-blue-400 text-xs">Akses Diizinkan</span>
                        </span>
                    `;
                    actionCell.innerHTML = `
                        <div class="flex items-center gap-3">
                            <button onclick="toggleOperatorActive(${operatorId}, 'nonaktifkan')" class="text-red-400 hover:text-red-300 text-xs font-medium">blokir akses</button>
                            <button onclick="deleteOperator(${operatorId}, '${operatorName}')" class="text-red-400 hover:text-red-300 text-xs">🗑️ hapus</button>
                        </div>
                    `;
                    showAjaxMessage('Operator "' + operatorName + '" berhasil diizinkan aksesnya.', 'success');
                } else {
                    aksesCell.innerHTML = `
                        <span class="inline-flex items-center gap-1.5">
                            <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                            <span class="text-red-400 text-xs">Akses Diblokir</span>
                        </span>
                    `;
                    actionCell.innerHTML = `
                        <div class="flex items-center gap-3">
                            <button onclick="toggleOperatorActive(${operatorId}, 'aktifkan')" class="text-blue-400 hover:text-blue-300 text-xs font-medium">izinkan akses</button>
                            <button onclick="deleteOperator(${operatorId}, '${operatorName}')" class="text-red-400 hover:text-red-300 text-xs">🗑️ hapus</button>
                        </div>
                    `;
                    showAjaxMessage('Operator "' + operatorName + '" berhasil diblokir aksesnya.', 'success');
                }
            } else {
                showAjaxMessage(data.message || 'Terjadi kesalahan.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAjaxMessage('Terjadi kesalahan. Periksa koneksi atau coba lagi.', 'error');
        });
    }
    
    // Delete operator
    function deleteOperator(operatorId, operatorName) {
        if (!confirm(`Hapus operator "${operatorName}"?\n\nData transaksinya tetap tersimpan.`)) {
            return;
        }
        
        fetch('/settings/operator/' + operatorId + '/delete', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const row = document.getElementById('operator-row-' + operatorId);
                if (row) row.remove();
                
                const totalSpan = document.getElementById('total-operators');
                if (totalSpan) {
                    totalSpan.innerText = parseInt(totalSpan.innerText) - 1;
                }
                
                showAjaxMessage(data.message, 'success');
                
                if (document.querySelectorAll('#operators-table tbody tr').length === 0) {
                    location.reload();
                }
            } else {
                showAjaxMessage(data.message || 'Gagal menghapus operator.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAjaxMessage('Terjadi kesalahan saat menghapus.', 'error');
        });
    }
    
    // Blokir akses semua operator
    function deactivateAllOperators() {
        if (!confirm('Yakin ingin memblokir akses SEMUA operator?')) return;
        
        fetch('{{ route("settings.operator.deactivateAll") }}', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAjaxMessage(data.message, 'success');
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                showAjaxMessage(data.message || 'Gagal memblokir operator.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAjaxMessage('Terjadi kesalahan.', 'error');
        });
    }
</script>
@endsection