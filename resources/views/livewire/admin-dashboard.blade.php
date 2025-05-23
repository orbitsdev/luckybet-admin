
<div x-data="{ sidebarOpen: false, profileOpen: false }" class="min-h-screen bg-gray-50">
  <div class="flex min-h-screen">
    <!-- Sidebar -->
    <div :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-30 w-64 bg-white shadow-md transform transition-transform duration-300 ease-in-out md:translate-x-0 md:static md:inset-0 flex flex-col">
        <div class="flex items-center justify-between px-6 py-4">
            <span class="text-gray-700 font-extrabold text-2xl tracking-wide flex items-center gap-2">
                <!-- Lucky Bet Logo -->
                <svg class="w-8 h-8 " fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="12" cy="12" r="10" stroke="#fff" stroke-width="2" fill="#337ab7" /></svg>
                Lucky Bet
            </span>
            <button @click="sidebarOpen=false" class="text-gray-700 md:hidden focus:outline-none">
                <!-- X Icon -->
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <nav class="flex-1 px-4 space-y-2 mt-4 bg-white border-r-2 border-blue-100">
    <!-- Sidebar links with active highlight -->
    <a href="#" class="flex items-center px-3 py-2 rounded-lg bg-blue-100 text-blue-700 font-bold shadow-sm sidebar-link">
        <!-- Home Icon -->
        <svg class="w-6 h-6 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7m-9 2v6m0 0h4m-4 0v6h4v-6m0 0V9m0 0l2 2m-2-2l-2 2" /></svg>
        Dashboard
    </a>
            <a href="#" class="flex items-center px-3 py-2 rounded-lg text-gray-500 hover:bg-blue-50 hover:text-blue-700 transition-colors duration-200 font-medium sidebar-link">
                <!-- Table Icon -->
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2" stroke-width="2" /><path d="M3 10h18" stroke-width="2" /><path d="M9 4v16" stroke-width="2" /></svg>
                Draws
            </a>
            <a href="#" class="flex items-center px-3 py-2 rounded-lg text-gray-500 hover:bg-blue-50 hover:text-blue-700 transition-colors duration-200 font-medium sidebar-link">
                <!-- Chart Bar Icon -->
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 17v-6a2 2 0 012-2h2a2 2 0 012 2v6M13 17v-2a2 2 0 012-2h2a2 2 0 012 2v2" /></svg>
                Bet Ratios
            </a>
            <a href="#" class="flex items-center px-3 py-2 rounded-lg text-gray-500 hover:bg-blue-50 hover:text-blue-700 transition-colors duration-200 font-medium sidebar-link">
                <!-- Fire Icon -->
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v2m0 0C7.03 7.61 4 12.13 4 16a8 8 0 0016 0c0-3.87-3.03-8.39-8-11z" /></svg>
                Low Win Numbers
            </a>
            <a href="#" class="flex items-center px-3 py-2 rounded-lg text-gray-500 hover:bg-blue-50 hover:text-blue-700 transition-colors duration-200 font-medium sidebar-link">
                <!-- Currency Dollar Icon -->
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zm0 0V4m0 16v-4" /></svg>
                Winning Amounts
            </a>
            <a href="#" class="flex items-center px-3 py-2 rounded-lg text-gray-500 hover:bg-blue-50 hover:text-blue-700 transition-colors duration-200 font-medium sidebar-link">
                <!-- Users Icon -->
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20h6M4 20h5v-2a4 4 0 013-3.87M12 4a4 4 0 110 8 4 4 0 010-8z" /></svg>
                Users & Branches
            </a>
            <a href="#" class="flex items-center px-3 py-2 rounded-lg text-gray-500 hover:bg-blue-50 hover:text-blue-700 transition-colors duration-200 font-medium sidebar-link">
                <!-- Clipboard List Icon -->
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a2 2 0 012-2h2a2 2 0 012 2v2M9 13h6" /></svg>
                Reports
            </a>
            <a href="#" class="flex items-center px-3 py-2 rounded-lg text-gray-500 hover:bg-blue-50 hover:text-blue-700 transition-colors duration-200 font-medium sidebar-link">
                <!-- Clock Icon -->
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3" /><circle cx="12" cy="12" r="10" stroke-width="2" /></svg>
                Audit Logs
            </a>
        </nav>
        <div class="mt-auto px-6 py-4">
            <button class="w-full flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white font-bold shadow-lg hover:bg-blue-700 transition-colors duration-200">
                <!-- Logout Icon -->
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12h4" /></svg>
                Logout
            </button>
        </div>
    </div>
    <!-- Main content wrapper -->
    <div class="flex-1 flex flex-col md:ml-64">
      <!-- Header -->
      <header class="flex items-center justify-between bg-white shadow border-b border-gray-200 px-6 py-4 sticky top-0 z-20">
        <div class="flex items-center gap-2">
          <button @click="sidebarOpen = !sidebarOpen" class="md:hidden text-gray-700 hover:bg-gray-100 p-2 rounded-lg transition">
            <!-- Menu Icon -->
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
          </button>
          <span class="font-bold text-lg text-gray-700 tracking-wide">Dashboard</span>
        </div>
        <div class="flex items-center gap-4">
          <button class="relative hover:bg-gray-100 transition">
            <!-- Bell Icon -->
            <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full px-1.5 py-0.5 ">3</span>
          </button>
          <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" class="flex items-center gap-2 focus:outline-none hover:bg-gray-100 transition">
              <img src="https://i.pravatar.cc/40?img=3" alt="Avatar" class="w-9 h-9 rounded-full border-2 border-blue-500 shadow" />
              <span class="hidden md:block font-semibold text-gray-700">Admin</span>
              <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
            </button>
            <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl py-2 z-50">
              <a href="#" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 transition">
                <!-- Cog Icon -->
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-1.14 1.603-1.14 1.902 0a1.724 1.724 0 002.573 1.02c.969-.56 2.12.311 1.81 1.367a1.724 1.724 0 001.1 2.23c1.062.322 1.062 1.776 0 2.098a1.724 1.724 0 00-1.1 2.23c.31 1.056-.841 1.927-1.81 1.367a1.724 1.724 0 00-2.573 1.02c-.299 1.14-1.603 1.14-1.902 0a1.724 1.724 0 00-2.573-1.02c-.969.56-2.12-.311-1.81-1.367a1.724 1.724 0 00-1.1-2.23c-1.062-.322-1.062-1.776 0-2.098a1.724 1.724 0 001.1-2.23c.31-1.056.841-1.927 1.81-1.367.97.56 2.12-.311 2.573-1.02z" /></svg>
                Settings
              </a>
              <a href="#" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 transition">
                <!-- Logout Icon -->
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12h4" /></svg>
                Logout
              </a>
            </div>
          </div>
        </div>
      </header>
      <!-- Dashboard Main Content -->
      <main class="flex-1 bg-gray-50 p-8 md:p-10 lg:p-12 space-y-8 min-h-screen">
        <!-- Cards Row -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Sales Card -->
                <div class="bg-white rounded-xl shadow p-6 flex items-center gap-4 border-t-4 border-blue-500">
    <!-- Card: blue accent, flat, modern -->
                    <div class="flex-shrink-0 bg-blue-500 rounded-full p-3">
                        <!-- Currency Dollar Icon -->
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zm0 0V4m0 16v-4" /></svg>
                    </div>
                    <div>
                        <div class="text-gray-400 text-xs font-bold uppercase tracking-wide">Total Sales</div>
                        <div class="text-3xl font-extrabold text-gray-900">₱1,250,000</div>
                    </div>
                </div>
                <!-- Winners Card -->
                <div class="bg-white rounded-xl shadow p-6 flex items-center gap-4 border-t-4 border-green-500">
    <!-- Card: green accent, flat, modern -->
                    <div class="flex-shrink-0 bg-green-500 rounded-full p-3">
                        <!-- Trophy Icon -->
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 21h8m-4-4v4m0 0a7 7 0 007-7V5H5v9a7 7 0 007 7z" /></svg>
                    </div>
                    <div>
                        <div class="text-gray-400 text-xs font-bold uppercase tracking-wide">Winners</div>
                        <div class="text-3xl font-extrabold text-gray-900">128</div>
                    </div>
                </div>
                <!-- Profit Card -->
                <div class="bg-white rounded-xl shadow p-6 flex items-center gap-4 border-t-4 border-blue-400">
    <!-- Card: blue accent, flat, modern -->
                    <div class="flex-shrink-0 bg-blue-400 rounded-full p-3">
                        <!-- Trending Up Icon -->
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 17v-6a2 2 0 012-2h2a2 2 0 012 2v6M13 17v-2a2 2 0 012-2h2a2 2 0 012 2v2" /></svg>
                    </div>
                    <div>
                        <div class="text-gray-400 text-xs font-bold uppercase tracking-wide">Profit</div>
                        <div class="text-3xl font-extrabold text-gray-900">₱320,000</div>
                    </div>
                </div>
            </div>
            <!-- Fake Chart Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Bar Chart -->
                <div class="bg-white rounded-xl shadow p-6 flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-gray-800 font-extrabold text-xl">Sales by Draw</div>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">Live</span>
                    </div>
                    <!-- Fake Bars -->
                    <div class="flex items-end h-32 gap-2">
                        <div class="w-1/6 bg-blue-100 rounded-lg" style="height: 35%"></div>
                        <div class="w-1/6 bg-blue-100 rounded-lg" style="height: 65%"></div>
                        <div class="w-1/6 bg-blue-100 rounded-lg" style="height: 80%"></div>
                        <div class="w-1/6 bg-blue-100 rounded-lg" style="height: 50%"></div>
                        <div class="w-1/6 bg-blue-100 rounded-lg" style="height: 90%"></div>
                        <div class="w-1/6 bg-blue-100 rounded-lg" style="height: 60%"></div>
                    </div>
                    <div class="flex justify-between text-xs text-gray-400 mt-2">
                        <span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span>
                    </div>
                </div>
                <!-- Pie Chart (Fake) -->
                <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center justify-center">
                    <div class="text-gray-700 font-bold text-lg mb-2">Bet Ratio</div>
                    <div class="relative w-32 h-32 flex items-center justify-center">
                        <!-- Fake Pie Chart -->
                        <svg viewBox="0 0 36 36" class="w-32 h-32">
                            <path class="text-blue-500" stroke-width="3.8" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831" />
                            <path class="text-blue-400" stroke-width="3.8" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 0 0 31.831" />
                            <circle class="text-gray-200" stroke-width="3.8" stroke="currentColor" fill="none" cx="18" cy="18" r="15.9155" />
                        </svg>
                        <span class="absolute inset-0 flex items-center justify-center text-2xl font-bold text-blue-600">68%</span>
                    </div>
                    <div class="flex gap-4 mt-4">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">D4</span>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">3D</span>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-gray-200 text-gray-700">2D</span>
                    </div>
                </div>
            </div>
            <!-- Data Table -->
            <div class="bg-white rounded-xl shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-gray-800 font-extrabold text-xl">Recent Draws</div>
                    <button class="px-4 py-2 rounded-lg bg-blue-600 text-white font-bold shadow hover:bg-blue-700 transition-colors duration-200">
                        View All
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-blue-600 text-white sticky top-0 z-10 rounded-t-xl">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Draw #</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Game Type</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Draw Date</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <tr class="hover:bg-blue-50 transition-colors duration-200">
                                <td class="px-6 py-4 whitespace-nowrap font-bold">#20250523</td>
                                <td class="px-6 py-4 whitespace-nowrap">D4</td>
                                <td class="px-6 py-4 whitespace-nowrap">2025-05-23 21:00</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700 text-white  ">Open</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap flex gap-2">
                                    <button class="p-2 rounded-full bg-blue-100 text-blue-700 hover:bg-blue-200 transition-colors duration-200" title="View">
                                        <!-- Eye Icon -->
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542 7z" /></svg>
                                    </button>
                                    <button class="p-2 rounded-full bg-yellow-100 text-yellow-700 hover:bg-yellow-200 transition-colors duration-200" title="Edit">
                                        <!-- Pencil Icon -->
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13h6m2 2a2 2 0 01-2 2H9a2 2 0 01-2-2V9a2 2 0 012-2h6a2 2 0 012 2v6z" /></svg>
                                    </button>
                                    <button class="p-2 rounded-full bg-red-100 text-red-600 hover:bg-red-200 transition-colors duration-200" title="Delete">
                                        <!-- Trash Icon -->
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </td>
                            </tr>
                            <tr class="hover:bg-blue-50 transition-colors duration-200">
                                <td class="px-6 py-4 whitespace-nowrap font-bold">#20250522</td>
                                <td class="px-6 py-4 whitespace-nowrap">3D</td>
                                <td class="px-6 py-4 whitespace-nowrap">2025-05-22 14:00</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700 text-white  ">Closed</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap flex gap-2">
                                    <button class="p-2 rounded-full bg-blue-100 text-blue-700 hover:bg-blue-200 transition-colors duration-200" title="View">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542 7z" /></svg>
                                    </button>
                                    <button class="p-2 rounded-full bg-yellow-100 text-yellow-700 hover:bg-yellow-200 transition-colors duration-200" title="Edit">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13h6m2 2a2 2 0 01-2 2H9a2 2 0 01-2-2V9a2 2 0 012-2h6a2 2 0 012 2v6z" /></svg>
                                    </button>
                                    <button class="p-2 rounded-full bg-red-100 text-red-600 hover:bg-red-200 transition-colors duration-200" title="Delete">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </td>
                            </tr>
                            <tr class="hover:bg-blue-50 transition-colors duration-200">
                                <td class="px-6 py-4 whitespace-nowrap font-bold">#20250521</td>
                                <td class="px-6 py-4 whitespace-nowrap">2D</td>
                                <td class="px-6 py-4 whitespace-nowrap">2025-05-21 09:00</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-gray-200 text-gray-700 text-white  ">Cancelled</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap flex gap-2">
                                    <button class="p-2 rounded-full bg-blue-100 text-blue-700 hover:bg-blue-200 transition-colors duration-200" title="View">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542 7z" /></svg>
                                    </button>
                                    <button class="p-2 rounded-full bg-yellow-100 text-yellow-700 hover:bg-yellow-200 transition-colors duration-200" title="Edit">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13h6m2 2a2 2 0 01-2 2H9a2 2 0 01-2-2V9a2 2 0 012-2h6a2 2 0 012 2v6z" /></svg>
                                    </button>
                                    <button class="p-2 rounded-full bg-red-100 text-red-600 hover:bg-red-200 transition-colors duration-200" title="Delete">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>


