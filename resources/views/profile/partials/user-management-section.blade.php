<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            <i class="fas fa-users mr-2 text-blue-600"></i>Manajemen User
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            Kelola user dan akses sistem sebagai direktur perusahaan.
        </p>
    </header>

    <div class="mt-6">
        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            @php
                $totalUsers = \App\Models\User::count();
                $direkturCount = \App\Models\User::whereHas('roles', function($q) { $q->where('name', 'direktur'); })->count();
                $projectManagerCount = \App\Models\User::whereHas('roles', function($q) { $q->where('name', 'project_manager'); })->count();
                $financeManagerCount = \App\Models\User::whereHas('roles', function($q) { $q->where('name', 'finance_manager'); })->count();
                $stafCount = \App\Models\User::whereHas('roles', function($q) { $q->where('name', 'staf'); })->count();
            @endphp

            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-users text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-blue-900">Total User</p>
                        <p class="text-2xl font-bold text-blue-600">{{ $totalUsers }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-crown text-purple-600 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-purple-900">Direktur</p>
                        <p class="text-2xl font-bold text-purple-600">{{ $direkturCount }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-user-tie text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-900">Manager</p>
                        <p class="text-2xl font-bold text-green-600">{{ $projectManagerCount + $financeManagerCount }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-user text-gray-600 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">Staf</p>
                        <p class="text-2xl font-bold text-gray-600">{{ $stafCount }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap gap-3 mb-6">
            <a href="{{ route('users.index') }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center">
                <i class="fas fa-list mr-2"></i>Lihat Semua User
            </a>
            <a href="{{ route('users.create') }}" 
               class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center">
                <i class="fas fa-plus mr-2"></i>Tambah User Baru
            </a>
        </div>

        <!-- Recent Users -->
        <div class="bg-gray-50 rounded-lg p-4">
            <h4 class="text-md font-medium text-gray-900 mb-4">User Terbaru</h4>
            
            @php
                $recentUsers = \App\Models\User::with('roles')->latest()->take(5)->get();
            @endphp

            @if($recentUsers->count() > 0)
                <div class="space-y-3">
                    @foreach($recentUsers as $recentUser)
                        <div class="flex items-center justify-between bg-white p-3 rounded-lg border">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-8 w-8">
                                    <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                        <span class="text-blue-600 font-medium text-xs">
                                            {{ strtoupper(substr($recentUser->name, 0, 2)) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">{{ $recentUser->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $recentUser->email }}</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                @foreach($recentUser->roles as $role)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                        @if($role->name == 'direktur') bg-purple-100 text-purple-800
                                        @elseif($role->name == 'project_manager') bg-blue-100 text-blue-800
                                        @elseif($role->name == 'finance_manager') bg-green-100 text-green-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                                    </span>
                                @endforeach
                                <a href="{{ route('users.show', $recentUser) }}" 
                                   class="text-blue-600 hover:text-blue-900 text-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-sm">Belum ada user yang terdaftar.</p>
            @endif
        </div>

        <!-- Quick Actions -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                <h5 class="font-medium text-blue-900 mb-2">
                    <i class="fas fa-shield-alt mr-2"></i>Keamanan Sistem
                </h5>
                <p class="text-sm text-blue-700 mb-3">
                    Kelola akses dan keamanan user di sistem.
                </p>
                <div class="space-y-2">
                    <a href="{{ route('users.index') }}?role=direktur" 
                       class="block text-sm text-blue-600 hover:text-blue-800">
                        • Lihat semua direktur
                    </a>
                    <a href="{{ route('users.index') }}?role=project_manager" 
                       class="block text-sm text-blue-600 hover:text-blue-800">
                        • Lihat project manager
                    </a>
                    <a href="{{ route('users.index') }}?role=finance_manager" 
                       class="block text-sm text-blue-600 hover:text-blue-800">
                        • Lihat finance manager
                    </a>
                </div>
            </div>

            <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                <h5 class="font-medium text-green-900 mb-2">
                    <i class="fas fa-chart-line mr-2"></i>Aktivitas User
                </h5>
                <p class="text-sm text-green-700 mb-3">
                    Monitor aktivitas dan performa user.
                </p>
                <div class="space-y-2">
                    <p class="text-sm text-green-600">
                        • User aktif hari ini: <strong>{{ \App\Models\User::whereDate('updated_at', today())->count() }}</strong>
                    </p>
                    <p class="text-sm text-green-600">
                        • User baru minggu ini: <strong>{{ \App\Models\User::where('created_at', '>=', now()->startOfWeek())->count() }}</strong>
                    </p>
                    <p class="text-sm text-green-600">
                        • Total login bulan ini: <strong>-</strong>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
